#!/bin/sh -e
#
# Part of XigmaNAS (https://www.xigmanas.com).
# Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
# All rights reserved.
#

# Set environment.
PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin
export PATH

# Set global variables.
PLATFORM=$(uname -m)
PRDNAME=$(cat /etc/prd.name)
CDPATH="/tmp/cdrom"
INCLUDE="/etc/install/include/boot"
APPNAME="RootOnZFS"
ALTROOT="/mnt"
DATASET="/ROOT"
BOOTENV="/default-install"
ZROOT="zroot"
BOOTPOOL="bootpool"
GMIRRORBAL="load"

# GELI options(bsdinstall).
GELIALGO="AES-XTS"
GELIINTER="256"
GELISECTOR="4096"
GELIBACKUP="/var/backups"

tmpfile=/tmp/zfsinstall.$$
trap "rm -f ${tmpfile}" 0 1 2 3 5 6 9 15

# Mount CD/USB drive.
mount_cdrom()
{
	# Unmount for stale/interrupted previous attempts.
	umount_cdrom

	# Search for LiveMedia label information.
	if glabel status | grep -q iso9660/${PRDNAME}; then
		LIVECD=0
	elif glabel status | grep -q ufs/liveboot; then
		LIVEUSB=0
	fi

	if [ "${LIVECD}" = 0 ]; then
		# Check if cd-rom is mounted else auto mount cd-rom.
		if [ ! -f "${CDPATH}/version" ]; then
			# Try to auto mount cd-rom.
			mkdir -p ${CDPATH}
			echo "Mounting CD-ROM Drive"
			mount_cd9660 /dev/cd[0-9] ${CDPATH} > /dev/null 2>&1
		fi
	elif [ "${LIVEUSB}" = 0 ]; then
		# Check if liveusb is mounted else auto mount liveusb.
		if [ ! -f "${CDPATH}/version" ]; then
			# Try to auto mount liveusb.
			mkdir -p ${CDPATH}
			echo "Mounting LiveUSB Drive"
			mount /dev/ufs/liveboot ${CDPATH} > /dev/null 2>&1
		fi
	fi
	# If no cd/usb is mounted ask for manual mount.
	if [ ! -f "${CDPATH}/version" ]; then
		manual_cdmount
	fi
}

umount_cdrom()
{
	if [ -d "${CDPATH}" ]; then
		if [ -f "${CDPATH}/version" ]; then
			echo "Unmount CD/USB Drive"
			umount -f ${CDPATH} > /dev/null 2>&1
			rm -R ${CDPATH}
		fi
	fi
}

manual_cdmount()
{
	DRIVES=$(camcontrol devlist)
	cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Select the Install Media Source" \
	--form "${DRIVES}" 0 0 0 \
	"Select CD/USB Drive e.g: cd0:" 1 1 "" 1 30 30 30 \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	# Try to mount from specified device.
	mkdir -p ${CDPATH}
	echo "Mounting CD/USB Drive"
	DEVICE=$(cat ${tmpfile} | tr -d' ')
	mount /dev/ufs/liveboot ${CDPATH} > /dev/null 2>&1 || mount /dev/${DEVICE}s1a ${CDPATH} > /dev/null 2>&1 || \
	mount /dev/${DEVICE}p3 ${CDPATH} > /dev/null 2>&1 || mount_cd9660 /dev/${DEVICE} ${CDPATH} > /dev/null 2>&1
	# Check if mounted cd/usb is accessible.
	if [ ! -f "${CDPATH}/version" ]; then
		# Re-try loop.
		mount_cdrom
	fi
}

# Clean any existing metadata on selected disks.
cleandisk_init()
{
	sysctl kern.geom.debugflags=0x10
	sleep 1

	# Ignore errors here since they may be expected.
	# We need this section to try to deal with problematic disk metadata.
	set +e

	# Destroy existing geom mirror/swap.
	if kldstat | grep -q geom_mirror; then
		if gmirror status | grep -q "mirror/swap"; then
			gmirror forget swap
			gmirror destroy -f swap
		fi
	fi

	DISKS=${DEVICE_LIST}
	NUM="0"
	WIPECOUNT="16384"
	for DISK in ${DISKS}
	do
		echo "Cleaning disk ${DISK}"

		# Destroy previous geli providers.
		# Detach pervious geli providers for glabel.
		if geli status | grep -qw gpt/sysdisk${NUM}; then
			geli detach -f /dev/gpt/sysdisk${NUM} > /dev/null 2>&1
		fi

		# Detach pervious geli providers for diskid.
		if geli status | grep -q ${DISK}; then
			GELIDEV=$(geli status | grep ${DISK} | awk '{print $3}')
			geli detach -f /dev/${GELIDEV} > /dev/null 2>&1
		fi

		gmirror clear ${DISK} > /dev/null 2>&1
		zpool labelclear -f /dev/gpt/sysdisk${NUM} > /dev/null 2>&1
		zpool labelclear -f /dev/${DISK} > /dev/null 2>&1
		gpart destroy -F ${DISK} > /dev/null 2>&1

		# Gather some disk information and proceed with cleanup.
		diskinfo ${DISK} | while read DISK sectorsize size sectors other
			do
				if [ "${WIPECOUNT}" -gt "${sectors}" ]; then
					WIPESECTORS=${sectors}
				else
					WIPESECTORS=${WIPECOUNT}
				fi
				# Delete MBR, GPT Primary, ZFS(L0L1)/other partition table.
				dd if="/dev/zero" of="/dev/${DISK}" bs=${sectorsize} count=${WIPESECTORS} > /dev/null 2>&1
				# Delete GEOM metadata, GPT Secondary, ZFS(L2L3)/other partition table.
				dd if="/dev/zero" of="/dev/${DISK}" bs=${sectorsize} seek=$(expr ${sectors} - ${WIPESECTORS}) count=${WIPESECTORS} > /dev/null 2>&1
			done
			NUM=$(expr $NUM + 1)
	done
	set -e
}

load_kmods()
{
	# Required kernel modules.
	# Load geom_mirror kernel module.
	if ! kldstat | grep -q geom_mirror; then
		kldload /boot/kernel/geom_mirror.ko
	fi

	# Load aesni kernel module.
	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		if ! kldstat | grep -qw aesni; then
			kldload /boot/kernel/aesni.ko
		fi
	fi
}

# Create GPT/Partition on disk.
gptpart_init()
{
	DISKS=${DEVICE_LIST}
	swapdevlist=/tmp/swapdevlist.$$
	gelidevlist=/tmp/gelidevlist.$$
	cat /dev/null > ${tmpfile}
	NUM="0"
	for DISK in ${DISKS}
	do
		echo "Creating GPT/Partition on ${DISK}"
		gpart create -s gpt ${DISK} > /dev/null

		# Create boot partition.
		if [ "${BOOT_MODE}" = 2 ]; then
			gpart add -a 4k -s 200M -t efi -l efiboot${NUM} ${DISK} > /dev/null
		fi
		gpart add -a 4k -s 512K -t freebsd-boot -l sysboot${NUM} ${DISK} > /dev/null

		if [ ! -z "${SWAP_SIZE}" ]; then
			# Add swap partition to selected drives.
			gpart add -a 4m -s ${SWAP_SIZE} -t freebsd-swap -l swap${NUM} ${DISK} > /dev/null
			# Generate swap device list.
			echo "/dev/gpt/swap${NUM}" >> ${swapdevlist}
		fi
		gpart add -a 4m ${ZROOT_SIZE} -t freebsd-zfs -l sysdisk${NUM} ${DISK} > /dev/null
		# Generate the glabel device list.
		echo "/dev/gpt/sysdisk${NUM}" >> ${tmpfile}

		if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
			# Generate the geli device list.
			echo "/dev/gpt/sysdisk${NUM}.eli" >> ${gelidevlist}
		fi

		NUM=$(expr $NUM + 1)
	done

	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		GELI_DEVLIST=$(cat ${tmpfile})
		ZROOT_DEVLIST=$(cat ${gelidevlist})
		rm -f ${gelidevlist}
	else
		ZROOT_DEVLIST=$(cat ${tmpfile})
	fi

	if [ ! -z "${SWAP_SIZE}" ]; then
		SWAP_DEVLIST=$(cat ${swapdevlist})
		rm -f ${swapdevlist}
	fi

	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		# Initialize encryption.
		geom_geli_init
	fi
}

# Create MBR/Partition on disk.
mbrpart_init()
{
	DISKS=${DEVICE_LIST}
	swapdevlist=/tmp/swapdevlist.$$
	gelidevlist=/tmp/gelidevlist.$$
	bootdevlist=/tmp/bootdevlist.$$
	cat /dev/null > ${tmpfile}
	#NUM="0"
	for DISK in ${DISKS}
	do
		echo "Creating MBR/Partition on ${DISK}"
		gpart create -s mbr ${DISK} > /dev/null

		# Create active partition partition.
		if [ "${BOOT_MODE}" = 3 ]; then
			gpart add -a 4k -t freebsd ${DISK} > /dev/null
			gpart set -a active -i 1 ${DISK} > /dev/null
		fi

		# Create zfs boot partition.
		gpart create -s BSD "${DISK}s1" > /dev/null
		gpart add  -i 1 -t freebsd-zfs -s 2147483648b "${DISK}s1" > /dev/null

		if [ ! -z "${SWAP_SIZE}" ]; then
			# Add swap partition to selected drives.
			gpart add -a 4k -s ${SWAP_SIZE} -i 2 -t freebsd-swap "${DISK}s1" > /dev/null
			# Generate swap device list.
			echo "/dev/${DISK}s1b" >> ${swapdevlist}
		fi

		# Create zfs os partition.
		gpart add -a 4k ${ZROOT_SIZE} -i 4 -t freebsd-zfs "${DISK}s1" > /dev/null

		# Write bootcode for ZFS on BIOS
		dd if="/boot/zfsboot" of="/dev/${DISK}s1" count=1 > /dev/null 2>&1

		# Generate the bootpool device list.
		echo "/dev/${DISK}s1a" >> ${bootdevlist}

		# Generate the mbr device list.
		echo "${DISK}s1d" >> ${tmpfile}

		if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
			# Generate the geli device list.
			echo "/dev/${DISK}s1d.eli" >> ${gelidevlist}
		fi

		#NUM=$(expr $NUM + 1)
	done

	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		GELI_DEVLIST=$(cat ${tmpfile})
		ZROOT_DEVLIST=$(cat ${gelidevlist})
		rm -f ${gelidevlist}
	else
		ZROOT_DEVLIST=$(cat ${tmpfile})
	fi

	BOOTPOOL_DEVLIST=$(cat ${bootdevlist})

	if [ ! -z "${SWAP_SIZE}" ]; then
		SWAP_DEVLIST=$(cat ${swapdevlist})
		rm -f ${swapdevlist}
	fi
}

# Initialize encryption.
geom_geli_init()
{
	load_kmods

	DISKS=${GELI_DEVLIST}
	for DISK in ${DISKS}
	do
		echo "Encryption in progress for ${DISK}..."
		RETURN=1
		#cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Encryption in progress!" --infobox \
		#"Initializing encryption on selected disks, this will take several seconds per disk." 4 46

		# Encrypt selected drives.
		if [ "${BOOT_MODE}" = 3 ]; then
			geli init -b -B ${ALTROOT}/${BOOTPOOL}/boot/${DISK}.eli -e ${GELIALGO} -J "${GELIPASS1}" -K ${ALTROOT}/${BOOTPOOL}/boot/encryption.key -l ${GELIINTER} -s ${GELISECTOR} ${DISK} > /dev/null
			geli attach -j "${GELIPASS1}" -k ${ALTROOT}/${BOOTPOOL}/boot/encryption.key ${DISK}
		else
			geli init -bg -e ${GELIALGO} -J "${GELIPASS1}" -l ${GELIINTER} -s ${GELISECTOR} ${DISK} > /dev/null
			geli attach -j "${GELIPASS1}" ${DISK}
		fi

		RETURN=$?
		if [ "${RETURN}" -eq 0 ]; then
			echo "Encryption success!"
		else
			echo "Encryption failed, exiting!"
			exit 1
		fi
	done
}

geli_meta_backup()
{
	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		if [ ! -d "${ALTROOT}${GELIBACKUP}" ]; then
			mkdir -p -m 750 ${ALTROOT}${GELIBACKUP}
		fi
		cd ${GELIBACKUP}
		for ITEM in *.eli
		do
			if [ -f "${ITEM}" ]; then
				RETURN=1
				mv -f ${ITEM} ${ALTROOT}${GELIBACKUP}/${ITEM}
				RETURN=$?
				if [ "${RETURN}" -ne 0 ]; then
					echo "Failed to backup geli providers metadata, exiting!"
					exit 1
				fi
			fi
		done
		cd
	fi
}

# Install RootOnZFS.
zroot_init()
{
	# Begin installation.
	printf '\033[1;37;44m RootOnZFS Working... \033[0m\033[1;37m\033[0m\n'

	# Mount cd-rom.
	mount_cdrom

	# Check for existing zroot pool.
	zpool_check

	# Check for existing bootpool pool.
	if [ "${BOOT_MODE}" = 3 ]; then
		bootpool_check
	fi

	# Get rid of any metadata on selected disk.
	cleandisk_init

	# Create MBR/GPT/Partition on disk.
	if [ "${BOOT_MODE}" = 3 ]; then
		mbrpart_init
	else
		gptpart_init
	fi

	# Set the raid type.
	if [ "${STRIPE}" = 0 ]; then
		RAID=""
	elif [ "${MIRROR}" = 0 ]; then
		RAID="mirror"
	elif [ "${RAID10}" = 0 ]; then
		RAID="mirror"
	elif [ "${RAIDZ1}" = 0 ]; then
		RAID="raidz1"
	elif [ "${RAIDZ2}" = 0 ]; then
		RAID="raidz2"
	elif [ "${RAIDZ3}" = 0 ]; then
		RAID="raidz3"
	fi

	if [ "${BOOT_MODE}" = 3 ]; then
		# Create bootable bootpool pool for mbr.
		echo "Setting up boot pool..."
		mount -t tmpfs "none" ${ALTROOT}

		if [ "${RAID10}" = 0 ]; then
			# Generate raid10 list in groups of two(fixed to four disk).
			echo ${BOOTPOOL_DEVLIST} | xargs -n 2 | split -d -l 1 - ${tmpfile}.disks
			BOOTPOOL_DEVLIST1=$(cat ${tmpfile}.disks00)
			BOOTPOOL_DEVLIST2=$(cat ${tmpfile}.disks01)
			zpool create -o altroot=${ALTROOT}  -m /${BOOTPOOL} -f ${BOOTPOOL} ${RAID} ${BOOTPOOL_DEVLIST1} ${RAID} ${BOOTPOOL_DEVLIST2}
			rm -rf ${tmpfile}.disks*
		else
			zpool create -o altroot=${ALTROOT}  -m /${BOOTPOOL} -f ${BOOTPOOL} ${RAID} ${BOOTPOOL_DEVLIST}
		fi

		mkdir -p ${ALTROOT}/${BOOTPOOL}/boot
		if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
			dd if="/dev/random" of="${ALTROOT}/${BOOTPOOL}/boot/encryption.key" bs=4096 count=1 > /dev/null 2>&1
			chmod go-wrx ${ALTROOT}/${BOOTPOOL}/boot/encryption.key
			geom_geli_init
		fi
		zfs unmount ${BOOTPOOL}
		umount ${ALTROOT}
	fi

	# Create bootable zroot pool with boot environments support.
	echo "Creating bootable ${ZROOT} pool"

	if [ "${RAID10}" = 0 ]; then
		# Generate raid10 list in groups of two(fixed to four disk).
		echo ${ZROOT_DEVLIST} | xargs -n 2 | split -d -l 1 - ${tmpfile}.disks
		ZROOT_DEVLIST1=$(cat ${tmpfile}.disks00)
		ZROOT_DEVLIST2=$(cat ${tmpfile}.disks01)
		zpool create -o altroot=${ALTROOT} -O compress=lz4 -O atime=off -m none -f ${ZROOT} ${RAID} ${ZROOT_DEVLIST1} ${RAID} ${ZROOT_DEVLIST2}
		rm -rf ${tmpfile}.disks*
	else
		zpool create -o altroot=${ALTROOT} -O compress=lz4 -O atime=off -m none -f ${ZROOT} ${RAID} ${ZROOT_DEVLIST}
	fi

	# Create ZFS filesystem hierarchy.
	zfs create -o mountpoint=none ${ZROOT}${DATASET}
	zfs create -o mountpoint=/ ${ZROOT}${DATASET}${BOOTENV}
	zfs create -o mountpoint=/tmp -o exec=on -o setuid=off ${ZROOT}/tmp
	#zfs create -o mountpoint=/var ${ZROOT}/var
	zfs create -o mountpoint=/var -o canmount=off ${ZROOT}/var
	zfs create -o exec=off -o setuid=off ${ZROOT}/var/log
	zfs create -o setuid=off ${ZROOT}/var/tmp
	zfs set mountpoint=/${ZROOT} ${ZROOT}
	zpool set bootfs=${ZROOT}${DATASET}${BOOTENV} ${ZROOT}
	#zfs set canmount=noauto ${ZROOT}${DATASET}${BOOTENV}
	if [ $? -eq 1 ]; then
		echo "Error: A problem has occurred while creating ${ZROOT} pool."
		exit 1
	fi

	# Install system files.
	install_sys_files

	if [ "${BOOT_MODE}" = 3 ]; then
		# Temporarily exporting ZFS pool(s)...
		zpool export ${ZROOT}
		zpool export ${BOOTPOOL}
	fi

	# Write bootcode.
	echo "Writing bootcode..."
	DISKS=${DEVICE_LIST}
	for DISK in ${DISKS}
	do
		if [ "${BOOT_MODE}" = 3 ]; then
			gpart bootcode -b /boot/mbr ${DISK}
			echo "Updating MBR boot loader on ${DISK}"
			dd if="/boot/zfsboot" of="/dev/${DISK}s1a" skip=1 seek=1024 > /dev/null 2>&1
		elif [ "${BOOT_MODE}" = 2 ]; then
			gpart bootcode -p /boot/boot1.efifat -i 1 ${DISK}
			gpart bootcode -b /boot/pmbr -p /boot/gptzfsboot -i 2 ${DISK}
		else
			gpart bootcode -b /boot/pmbr -p /boot/gptzfsboot -i 1 ${DISK}
		fi
	done

	if [ "${BOOT_MODE}" = 3 ]; then
		# Re-importing ZFS pool(s)...
		zpool import -o altroot=${ALTROOT} ${ZROOT}
		zpool import -o altroot=${ALTROOT} -N ${BOOTPOOL}
		zfs mount ${BOOTPOOL}

		echo "Copying ${BOOTPOOL} content..."
		rsync -a ${ALTROOT}/boot/ ${ALTROOT}/${BOOTPOOL}/boot/
		rm -rf ${ALTROOT}/boot

		echo "Creating /boot symlink for ${BOOTPOOL}..."
		ln -sf ${BOOTPOOL}/boot ${ALTROOT}/boot
		mkdir -p ${ALTROOT}/boot/zfs
	fi

	# Set the zpool.cache file.
	zpool set cachefile=${ALTROOT}/boot/zfs/zpool.cache ${ZROOT}

	# Set canmount=noauto for the boot environment.
	zfs set canmount=noauto ${ZROOT}${DATASET}${BOOTENV}

	sysctl kern.geom.debugflags=0
	sleep 1

	# Add required ZFS mount points to fstab(legacy mountpoints only).
	#echo "${ZROOT}/tmp /tmp zfs rw,noatime 0 0" >> ${ALTROOT}/etc/fstab
	#echo "${ZROOT}/var /var zfs rw,noatime 0 0" >> ${ALTROOT}/etc/fstab

	# Creating/adding the fixed swap devices.
	if [ ! -z "${SWAP_SIZE}" ]; then
		if [ "${SWAPMODE}" = 1 ]; then
			echo "Creating/adding swap mirror..."
			if ! kldstat | grep -q geom_mirror; then
				kldload /boot/kernel/geom_mirror.ko
			fi
			gmirror label -b ${GMIRRORBAL} swap ${SWAP_DEVLIST}
			# Add swap mirror to fstab.
			if echo ${GELI_MODE} | grep -qw "SWAP+GELI"; then
				echo "/dev/mirror/swap.eli none swap sw 0 0" >> ${ALTROOT}/etc/fstab
			else
				echo "/dev/mirror/swap none swap sw 0 0" >> ${ALTROOT}/etc/fstab
			fi
		else
			# Add swap device to fstab.
			echo "Adding swap devices to fstab..."
			for swapdev in ${SWAP_DEVLIST}; do
				if echo ${GELI_MODE} | grep -qw "SWAP+GELI"; then
					echo "${swapdev}.eli none swap sw 0 0" >> ${ALTROOT}/etc/fstab
				else
					echo "${swapdev} none swap sw 0 0" >> ${ALTROOT}/etc/fstab
				fi
			done
		fi
	fi

	# Backup geli providers metadata.
	geli_meta_backup

	# Unmount cd-rom.
	umount_cdrom

	# Create user Dataset on request.
	create_user_dataset

	# Creates system default snapshot after install.
	create_default_snapshot

	# Flush disk cache and wait 1 second.
	sync; sleep 1
	if zpool status | grep -q ${ZROOT}; then
		if [ "${BOOT_MODE}" = 3 ]; then
			if zpool status | grep -q ${BOOTPOOL}; then
				zpool export ${BOOTPOOL}
			fi
		fi

		zpool export ${ZROOT}
	fi

	# Final message.
	if [ $? -eq 0 ]; then
		cdialog --msgbox "${PRDNAME} ${APPNAME} Successfully Installed!" 6 60
		exit 0
	else
		echo "Error: A problem has occurred during the installation."
		exit 1
	fi
}

install_sys_files()
{
	echo "Installing system files on ${ZROOT}..."

	# Install system files and discard unwanted folders.
	EXCLUDEDIRS="--exclude .snap/ --exclude resources/ --exclude mnt/ --exclude dev/ --exclude var/ --exclude tmp/ --exclude cf/"
	tar ${EXCLUDEDIRS} -c -f - -C / . | tar -xpf - -C ${ALTROOT}
	if [ ! -f "${ALTROOT}/etc/rc.d/var" ]; then
		cp -r /etc/rc.d/var ${ALTROOT}/etc/rc.d/var
	fi

	# Copy files from live media source.
	copy_media_files

	# Install configuration file.
	mkdir -p ${ALTROOT}/cf/conf
	cp /conf.default/config.xml ${ALTROOT}/cf/conf

	# Generate new loader.conf file.
	cat << EOF > ${ALTROOT}/boot/loader.conf
kernel="kernel"
bootfile="kernel"
kernel_options=""
hw.est.msr_info="0"
hw.hptrr.attach_generic="0"
hw.msk.msi_disable="1"
kern.maxfiles="6289573"
kern.cam.boot_delay="12000"
kern.cam.ada.legacy_aliases="0"
kern.geom.label.disk_ident.enable="0"
kern.geom.label.gptid.enable="0"
hint.acpi_throttle.0.disabled="0"
hint.p4tcc.0.disabled="0"
loader_brand="${PRDNAME}"
autoboot_delay="3"
#isboot_load="YES"
zfs_load="YES"
EOF
	if [ "${BOOT_MODE}" = 3 ]; then
		echo "vfs.root.mountfrom=\"zfs:${ZROOT}${DATASET}${BOOTENV}\"" >> ${ALTROOT}/boot/loader.conf
	fi

	if [ "${PLATFORM}" = "amd64" ]; then
		echo 'mlx4en_load="YES"' >> ${ALTROOT}/boot/loader.conf
	fi

	if [ "${SWAPMODE}" = 1 ]; then
		if [ ! -z "${SWAP_SIZE}" ]; then
			echo 'geom_mirror_load="YES"' >> ${ALTROOT}/boot/loader.conf
		fi
	fi

	# Update loader.conf file if geli encryption.
	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		if [ "${BOOT_MODE}" = 3 ]; then
			DISKS=${DEVICE_LIST}
			for DISK in ${DISKS}
			do
				echo "geli_${DISK}s1d_keyfile0_load=\"YES\"" >> ${ALTROOT}/boot/loader.conf
				echo "geli_${DISK}s1d_keyfile0_type=\"${DISK}s1d:geli_keyfile0\"" >> ${ALTROOT}/boot/loader.conf
				echo "geli_${DISK}s1d_keyfile0_name=\"/boot/encryption.key\"" >> ${ALTROOT}/boot/loader.conf
			done
				echo 'zpool_cache_load="YES"' >> ${ALTROOT}/boot/loader.conf
				echo 'zpool_cache_type="/boot/zfs/zpool.cache"' >> ${ALTROOT}/boot/loader.conf
				echo 'zpool_cache_name="/boot/zfs/zpool.cache"' >> ${ALTROOT}/boot/loader.conf
				echo 'geom_eli_passphrase_prompt="YES"' >> ${ALTROOT}/boot/loader.conf
		fi
		echo 'aesni_load="YES"' >> ${ALTROOT}/boot/loader.conf
		echo 'geom_eli_load="YES"' >> ${ALTROOT}/boot/loader.conf
	fi

	# Generate new rc.conf file.
	#cat << EOF > ${ALTROOT}/etc/rc.conf

#EOF

	# Set the release type.
	if [ "${PLATFORM}" = "amd64" ]; then
		echo "x64-full" > ${ALTROOT}/etc/platform
	elif [ "${PLATFORM}" = "i386" ]; then
		echo "x86-full" > ${ALTROOT}/etc/platform
	fi

	# Generate /etc/fstab.
	cat << EOF > ${ALTROOT}/etc/fstab
# Device    Mountpoint    FStype    Options    Dump    Pass#
#
EOF

	# Generate /etc/swapdevice.
	if [ ! -z "${SWAP_SIZE}" ]; then
		cat << EOF > ${ALTROOT}/etc/swapdevice
swapinfo
EOF
	fi

	# Generating the /etc/cfdevice (this file is linked in /var/etc at bootup)
	# This file is used by the firmware and mount check and is normally
	# generated with 'liveCD' and 'embedded' during startup, but need to be
	# created during install of 'full'.
	# This is for backward compatibility only.
	if [ ! -z "${disklist}" ]; then
		if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
			# Use diskid if encryption enabled.
			CFDEVS=${disklist}
		elif [ "${RAID10}" = 0 ]; then
			CFDEVS="${ZROOT_DEVLIST1} ${ZROOT_DEVLIST2}"
		else
			CFDEVS="${ZROOT_DEVLIST}"
		fi
		cat << EOF > ${ALTROOT}/etc/cfdevice
${CFDEVS}
EOF
	fi

	# Post install configuration.
	post_install_config
	echo "Done!"
}

copy_media_files()
{
	# Copy files from live media source.
	mkdir -p ${ALTROOT}/dev
	mkdir -p ${ALTROOT}/mnt
	mkdir -p ${ALTROOT}/tmp
	mkdir -p ${ALTROOT}/var
	chmod 1777 ${ALTROOT}/tmp
	mkdir -p ${ALTROOT}/boot/defaults
	cp -r ${CDPATH}/boot/* ${ALTROOT}/boot/
	cp -r ${CDPATH}/boot/defaults/* ${ALTROOT}/boot/defaults/
	cp -r ${CDPATH}/boot/kernel/* ${ALTROOT}/boot/kernel/

	# Decompress kernel.
	gzip -d -f ${ALTROOT}/boot/kernel/kernel.gz

	# Decompress modules (legacy versions).
	cd ${ALTROOT}/boot/kernel
	for FILE in *.gz
	do
		if [ -f "${FILE}" ]; then
			gzip -d -f ${FILE}
		fi
	done
	cd
}

post_install_config()
{
	# Configure some rc variables here before reboot.
	# Clear default router and netwait ip on new installs.
	if sysrc -f ${ALTROOT}/etc/rc.conf -qc defaultrouter=""; then
		sysrc -f ${ALTROOT}/etc/rc.conf defaultrouter="" > /dev/null 2>&1
	fi
	if sysrc -f ${ALTROOT}/etc/rc.conf -qc netwait_ip=""; then
		sysrc -f ${ALTROOT}/etc/rc.conf netwait_ip="" > /dev/null 2>&1
	fi

	# Disable /var md option as it may override our zroot/tmp dataset.
	if sysrc -f ${ALTROOT}/etc/rc.conf -qc varmfs="YES"; then
		sysrc -f ${ALTROOT}/etc/rc.conf varmfs="NO" > /dev/null 2>&1
	fi

	# Set zfs_enable to yes to automount our datasets on boot.
	if [ -f "${ALTROOT}/etc/rc.conf.local" ]; then
		if ! sysrc -f ${ALTROOT}/etc/rc.conf.local -qc zfs_enable="YES"; then
			sysrc -f ${ALTROOT}/etc/rc.conf.local zfs_enable="YES" > /dev/null 2>&1
		fi
	else
		touch ${ALTROOT}/etc/rc.conf.local
		chmod 0644 ${ALTROOT}/etc/rc.conf.local
		sysrc -f ${ALTROOT}/etc/rc.conf.local zfs_enable="YES" > /dev/null 2>&1
	fi
}

create_user_dataset()
{
	# Create user Dataset on request.
	if [ ! -z "${DATASET_NAME}" ]; then
		echo "Creating dataset ${DATASET_NAME}..."
		# Prevent for dataset directory creation here(backward compatibility only).
		# Users should never write data to this directory unless dataset mounted.
		# So we will set canmount after dataset creation for safety.
		zfs create -o compression=lz4 -o canmount=off -o atime=off -o mountpoint=${ALTROOT}/${DATASET_NAME} ${ZROOT}/${DATASET_NAME}
		zfs set canmount=on ${ZROOT}/${DATASET_NAME}
		if [ $? -eq 1 ]; then
			echo "Error: A problem has occurred while creating ${DATASET_NAME} dataset."
			exit 1
		fi
		echo "Done!"
		sleep 1
	fi
}

create_default_snapshot()
{
	echo "Creating system default snapshot..."
	zfs snapshot ${ZROOT}${DATASET}${BOOTENV}@factory-defaults
	echo "Done!"
	sleep 1

	if [ "${BOOT_MODE}" = 3 ]; then
		echo "Creating ${BOOTPOOL} default snapshot..."
		zfs snapshot ${BOOTPOOL}@factory-defaults
		echo "Done!"
		sleep 1
	fi
}

zpool_check()
{
	# Check if a zroot pool already exist and/or mounted.
	echo "Check for existing ${ZROOT} pool..."
	if zpool import | grep -qw ${ZROOT} || zpool status | grep -qw ${ZROOT}; then
		printf '\033[1;37;43m WARNING \033[0m\033[1;37m A pool called '${ZROOT}' already exist.\033[0m\n'
		while :
			do
				read -p "Do you wish to proceed with the installation anyway? [y/N]:" yn
				case ${yn} in
				[Yy]) break;;
				[Nn]) exit 0;;
				esac
			done
		echo "Proceeding..."
		# Export existing zroot pool.
		if zpool status | grep -q ${ZROOT}; then
			zpool export -f ${ZROOT} > /dev/null 2>&1
		fi
	fi
}

bootpool_check()
{
	# Check if a bootpool pool already exist and/or mounted.
	echo "Check for existing ${BOOTPOOL} pool..."
	if zpool import | grep -qw ${BOOTPOOL} || zpool status | grep -qw ${BOOTPOOL}; then
		printf '\033[1;37;43m WARNING \033[0m\033[1;37m A pool called '${BOOTPOOL}' already exist.\033[0m\n'
		while :
			do
				read -p "Do you wish to proceed with the installation anyway? [y/N]:" yn
				case ${yn} in
				[Yy]) break;;
				[Nn]) exit 0;;
				esac
			done
		echo "Proceeding..."
		# Export existing zroot pool.
		if zpool status | grep -q ${BOOTPOOL}; then
			zpool export -f ${BOOTPOOL} > /dev/null 2>&1
		fi
	fi
}

install_yesno()
{
	DISKS=$(echo ${disklist})
	cdialog --title "Proceed with ${PRDNAME} ${APPNAME} Install" \
	--backtitle "${PRDNAME} ${APPNAME} Installer" \
	--yesno "Continuing with the installation will destroy all data on <${DISKS}> device(s), do you really want to continue?" 0 0
	if [ 0 -ne $? ]; then
		exit 0
	fi
}

menu_swap()
{
	cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Customize Swap size" \
	--form "\nEnter a desired SWAP size, default size is 2G, leave empty for none." 0 0 0 \
	"Enter swap size:" 1 1 "2G" 1 25 25 25 \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	swap_size=$(cat ${tmpfile})
	if [ ! -z "${swap_size}" ]; then
		# Perform some input validation.
		if [ ! $(echo "${swap_size}" | egrep -o '^[1-9][0-9]*[kmgKMG]$') ]; then
			echo ""
			echo "ERROR: Invalid swap size specified, allowed suffixes are K,M,G."
			read -p "Press Enter to retry." RETRY
			menu_swap
		fi

		SWAP_SIZE="${swap_size}"
		if [ "${choice}" -ge 2 ]; then
			cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "System Swap Mode" \
			--radiolist "Select system Swap mode, (default mirrored)." 10 50 4 \
			1 "Mirrored System Swap" on \
			2 "Multiple System Swap" off \
			2>${tmpfile}
			if [ 0 -ne $? ]; then
				exit 0
			fi
			SWAPMODE=$(cat ${tmpfile})
		fi
	fi
}

menu_zrootsize()
{
	cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Customize zroot pool partition" \
	--radiolist "Would you like to customize the ${ZROOT} partition size?" 10 60 4 \
	1 "Yes, I want to customize." off \
	2 "No, leave the defaults." on \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	CUSTOM_ROOT=$(cat ${tmpfile})
	if [ "${CUSTOM_ROOT}" = 1 ]; then
		cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Customize zroot pool size" \
		--form "\nEnter a desired zroot pool size, for example 10G, or leave empty to use all remaining disk space (default empty)." 0 0 0 \
		"Enter zroot pool size:" 1 1 "" 1 25 25 25 \
		2>${tmpfile}
		if [ 0 -ne $? ]; then
			exit 0
		fi

		root_size=$(cat ${tmpfile})
		if [ ! -z "${root_size}" ]; then
			# Perform some input validation.
			if [ ! $(echo "${root_size}" | egrep -o '^[1-9][0-9]*[kmgKMG]$') ]; then
				echo ""
				echo "ERROR: Invalid zroot size specified, allowed suffixes are K,M,G."
				read -p "Press Enter to retry." RETRY
				menu_zrootsize
			fi

			ZROOT_SIZE="-s ${root_size}"
		fi
	fi
}

menu_dataset()
{
	cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Create user Dataset" \
	--radiolist "Would you like to create a ZFS Dataset for data store?" 10 60 4 \
	1 "Yes, I want a Dataset." off \
	2 "No, leave the defaults." on \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	dataset=$(cat ${tmpfile})
	if [ "${dataset}" = 1 ]; then
		cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Enter Dataset name" \
		--form "\nEnter a desired Dataset name, or leave empty to cancel Dataset creation (default empty)." 0 0 0 \
		"Enter Dataset name:" 1 1 "" 1 25 25 25 \
		2>${tmpfile}
		if [ 0 -ne $? ]; then
			exit 0
		fi

		dataset_name=$(cat ${tmpfile})
		if [ ! -z "${dataset_name}" ]; then
			check_name
			DATASET_NAME="${dataset_name}"
		fi
	fi
}

check_name()
{
	# Perform some input validation.
	DATASETNAME="${dataset_name}"
	CHECK_NAME=$(echo "${dataset_name}" | tr -c -d 'a-zA-Z0-9-_.:')
	if [ "${DATASETNAME}" != "${CHECK_NAME}" ]; then

		echo -e "
ERROR: Can not create ZFS Dataset with '${DATASETNAME}' name.

    Allowed characters for ZFS Datasets are:
      Alphanumeric: (a-z) (A-Z) (0-9)
      Hypen: (-)
      Underscore: (_)
      Dot: (.)
      Colon: (:)

    Name '${CHECK_NAME}' which uses only allowed characters can be used.
"
		read -p "Press Enter to retry." RETRY
		menu_dataset
	fi
}

menu_bootmode()
{
	cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Boot mode selection menu" \
	--radiolist "Select system boot mode, default is GPT BIOS." 10 50 4 \
	1 "GPT BIOS System Boot" on \
	2 "GPT BIOS+UEFI System Boot" off \
	3 "MBR BIOS System Boot (Legacy)" off \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	BOOT_MODE=$(cat ${tmpfile})
}

menu_encryption()
{
	cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Encryption option menu" \
	--checklist "Select the desired items to be encrypted (optional), if you don't know about GELI/Encryption, please disregard this and press Enter to continue." 12 55 8 \
	"DISK+GELI" "Encrypt Disks?" off \
	"SWAP+GELI" "Encrypt Swap?" off \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	GELI_MODE=$(cat ${tmpfile})

	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		GELIPASS1="/tmp/gelipass1.$$"
		GELIPASS2="/tmp/gelipass2.$$"
		trap "rm -f ${GELIPASS1} ${GELIPASS2}" 0 1 2 5 15

		# Ask for a password.
		cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Encryption option menu" --clear --insecure --passwordbox \
		"Enter a strong passphrase, used to protect your encryption keys. You will be required to enter this passphrase each time the system is booted." 8 80 \
		2> ${GELIPASS1}

		# Re-enter password.
		cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Encryption option menu" --clear --insecure --passwordbox "Re-enter password." 8 60 \
		2> ${GELIPASS2}

		# Check passwords.
		PASS1=$(cat ${GELIPASS1})
		PASS2=$(cat ${GELIPASS2})
		if [ "${PASS1}" != "${PASS2}" -o -z "${PASS1}" -o -z "${PASS2}" ]; then
			rm -f ${GELIPASS1} ${GELIPASS2}
			cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Encryption option menu" --msgbox "Passwords do not match." 5 28
			menu_encryption
		fi
	fi
}

menu_zroot_create()
{
	load_kmods
	menu_install
	menu_swap
	menu_zrootsize
	menu_dataset
	menu_bootmode
	menu_encryption
	install_yesno
	zroot_init
}

sort_disklist()
{
	sed 's/\([^0-9]*\)/\1 /' | sort +0 -1 +1n | tr -d ' '
}

get_disklist()
{
	local disklist

	for disklist in $(sysctl -n kern.disks)
	do
		VAL="${VAL} ${disklist}"
	done

	VAL=$(echo ${VAL} | tr ' ' '\n'| grep -v '^cd' | sort_disklist)
}

get_media_desc()
{
	local media
	local description
	local cap

	media=$1
	VAL=""
	if [ -n "${media}" ]; then
		# Try to get model information for each detected device.
		description=$(camcontrol identify ${media} | grep 'device model' | sed 's/device\ model\ *//g')
	if [ -z "${description}" ] ; then
		# Re-try with "camcontrol inquiry" instead.
		description=$(camcontrol inquiry ${media} | awk 'NR==1' | cut -d '<' -f2 | cut -d '>' -f1)
		if [ -z "${description}" ] ; then
			description="Disk Drive"
		fi
	fi
		cap=$(diskinfo ${media} | awk '{
			capacity = $3;
			if (capacity >= 1099511627776) {
				printf("%.1f TiB", capacity / 1099511627776.0);
			} else if (capacity >= 1073741824) {
				printf("%.1f GiB", capacity / 1073741824.0);
			} else if (capacity >= 1048576) {
				printf("%.1f MiB", capacity / 1048576.0);
			} else {
				printf("%d Bytes", capacity);
			}}')
		VAL="${description} -- ${cap}"
	fi
}

menu_install()
{
	tmplist=/tmp/zfsinstall.$$
	get_disklist
	disklist="${VAL}"
	list=""
	items=0

	for disklist in ${disklist}
		do
			get_media_desc "${disklist}"
			desc="${VAL}"
			list="${list} ${disklist} '${desc}' off"
			items=$((${items} + 1))
		done

	if [ "${items}" -ge 10 ]; then
		items=10
		menuheight=20
	else
		menuheight=10
		menuheight=$((${menuheight} + ${items}))
	fi

	if [ "${items}" -eq 0 ]; then
		eval "cdialog --title 'Choose destination drive(s)' --msgbox 'No drives available' 5 60" \
		2>${tmpfile}
		exit 1
	fi

	eval "cdialog --backtitle '${PRDNAME} ${APPNAME} Installer'   --title 'Choose destination drive' \
		--checklist 'Select (one) or (more) drives where ${PRDNAME} should be installed, use arrow keys to navigate to the drive(s) for installation then select a drive with the spacebar.' \
		${menuheight} 60 ${items} ${list}" 2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	if [ ! -z "${tmpfile}" ]; then
		#disklist=$(eval "echo $(cat ${tmpfile})")
		disklist=$(echo $(cat ${tmpfile}))
	fi

	count="1"
	for item in ${disklist}
	do
		DEV_COUNT=$(echo $count)
		count=$(expr $count + 1)
	done

	# Check for empty user input.
	if [ -z "${disklist}" ]; then
		if [ "${choice}" = 1 ]; then
			cdialog --msgbox "Notice: You need to select at least one disk!" 6 55; exit 1
		elif [ "${choice}" = 2 ]; then
			cdialog --msgbox "Notice: You need to select at least two disks!" 6 55; exit 1
		elif [ "${choice}" = 3 ]; then
			cdialog --msgbox "Notice: You need to select at least four disks!" 6 55; exit 1
		elif [ "${choice}" = 4 ]; then
			cdialog --msgbox "Notice: You need to select at least three disks!" 6 55; exit 1
		elif [ "${choice}" = 5 ]; then
			cdialog --msgbox "Notice: You need to select at least four disks!" 6 55; exit 1
		elif [ "${choice}" = 6 ]; then
			cdialog --msgbox "Notice: You need to select at least five disks!" 6 55; exit 1
		fi
	fi

	# Check for absolute minimum disks selection.
	if [ -n "${disklist}" ]; then
		if [ "${choice}" = 2 ]; then
			if [ "${DEV_COUNT}" -le 1 ]; then
				cdialog --msgbox "Notice: You need to select a minimum of two disks!" 6 60; exit 1
			fi
		elif [ "${choice}" = 3 ]; then
			if [ "${DEV_COUNT}" -le 3 ]; then
				cdialog --msgbox "Notice: You need to select a minimum of four disks!" 6 60; exit 1
			fi
		elif [ "${choice}" = 4 ]; then
			if [ "${DEV_COUNT}" -le 2 ]; then
				cdialog --msgbox "Notice: You need to select a minimum of three disks!" 6 60; exit 1
			fi
		elif [ "${choice}" = 5 ]; then
			if [ "${DEV_COUNT}" -le 3 ]; then
				cdialog --msgbox "Notice: You need to select a minimum of four disks!" 6 60; exit 1
			fi
		elif [ "${choice}" = 6 ]; then
			if [ "${DEV_COUNT}" -le 4 ]; then
				cdialog --msgbox "Notice: You need to select a minimum of five disks!" 6 60; exit 1
			fi
		fi
	fi
	cat /dev/null > ${tmplist}
	for disk in ${disklist}; do
		#echo "/dev/${disk}" >> ${tmplist}
		echo "${disk}" >> ${tmplist}
	done

	DEVICE_LIST=$(cat ${tmplist})
	rm -f ${tmplist}
}

menu_main()
{
	while :
		do
			cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --clear --title "${PRDNAME} Installer Options" --cancel-label "Exit" --menu "" 12 50 10 \
			"1" "Install ${PRDNAME} ${APPNAME} on Stripe" \
			"2" "Install ${PRDNAME} ${APPNAME} on Mirror" \
			"3" "Install ${PRDNAME} ${APPNAME} on RAID10" \
			"4" "Install ${PRDNAME} ${APPNAME} on RAIDZ1" \
			"5" "Install ${PRDNAME} ${APPNAME} on RAIDZ2" \
			"6" "Install ${PRDNAME} ${APPNAME} on RAIDZ3" \
			2>${tmpfile}
			if [ 0 -ne $? ]; then
				exit 0
			fi
			choice=$(cat "${tmpfile}")
			case "${choice}" in
				1) STRIPE="0" && menu_zroot_create ;;
				2) MIRROR="0" && menu_zroot_create ;;
				3) RAID10="0" && menu_zroot_create ;;
				4) RAIDZ1="0" && menu_zroot_create ;;
				5) RAIDZ2="0" && menu_zroot_create ;;
				6) RAIDZ3="0" && menu_zroot_create ;;
			esac
		done
}
menu_main
