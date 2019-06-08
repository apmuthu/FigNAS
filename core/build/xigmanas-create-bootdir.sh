#!/bin/sh
#
# Part of XigmaNAS (https://www.xigmanas.com).
# Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
# All rights reserved.
#

XIGMANAS_PRODUCTNAME=$(cat $XIGMANAS_SVNDIR/etc/prd.name)
MINIBSD_DIR=${XIGMANAS_ROOTDIR}/bootloader;

# Initialize variables.
opt_a=0
opt_b=0
opt_d=0
opt_m=0
opt_s=0
opt_f=0

# Parse the command-line options.
while getopts 'abdfhms' option
do
	case "$option" in
    "a")  opt_a=1;;
    "b")  opt_b=1;;
    "d")  opt_d=1;;
    "m")  opt_m=1;;
    "f")  opt_f=1;;
    "s")  opt_s=1;;
    "h")  echo "$(basename $0): Build boot loader";
          echo "Common Options:";
          echo "  -a    Disable ACPI"
          echo "  -b    Enable bootsplash";
          echo "  -d    Enable debug"
          echo "  -m    Enable bootmenu";
          echo "  -s    Enable serial console";
          echo "  -f    Force executing this script";
          exit 1;;
    ?)    echo "$0: Unknown option. Exiting...";
          exit 1;;
  esac
done

shift `expr $OPTIND - 1`

echo "Building the boot loader..."

if [ -n "$1" ]; then
  MINIBSD_DIR=$1
  echo "Using directory $1."
fi

if [ 1 != $opt_f -a -d "$MINIBSD_DIR" ]; then
  echo
  echo "=> $MINIBSD_DIR directory does already exist. Remove it"
  echo "=> before running this script."
  echo
  echo "=> Exiting..."
  echo
  exit 1
fi

# Create the boot directory that will contain boot, and kernel
mkdir $MINIBSD_DIR
mkdir $MINIBSD_DIR/defaults
mkdir $MINIBSD_DIR/dtb
mkdir $MINIBSD_DIR/dtb/overlays
mkdir $MINIBSD_DIR/firmware
mkdir $MINIBSD_DIR/kernel
mkdir $MINIBSD_DIR/lua
mkdir $MINIBSD_DIR/modules
mkdir $MINIBSD_DIR/zfs

# Copy required files
cp -v ${XIGMANAS_WORLD}/boot/defaults/loader.conf $MINIBSD_DIR/defaults
cp -v ${XIGMANAS_WORLD}/boot/loader $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/boot $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/entropy $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/mbr $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/gptboot $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/pmbr $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/cdboot $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/loader.4th $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/support.4th $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/device.hints $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/lua/cli.lua $MINIBSD_DIR/lua
cp -v ${XIGMANAS_WORLD}/boot/lua/color.lua $MINIBSD_DIR/lua
cp -v ${XIGMANAS_WORLD}/boot/lua/config.lua $MINIBSD_DIR/lua
cp -v ${XIGMANAS_WORLD}/boot/lua/core.lua $MINIBSD_DIR/lua
cp -v ${XIGMANAS_WORLD}/boot/lua/drawer.lua $MINIBSD_DIR/lua
cp -v ${XIGMANAS_WORLD}/boot/lua/hook.lua $MINIBSD_DIR/lua
cp -v ${XIGMANAS_WORLD}/boot/lua/loader.lua $MINIBSD_DIR/lua
cp -v ${XIGMANAS_WORLD}/boot/lua/menu.lua $MINIBSD_DIR/lua
cp -v ${XIGMANAS_WORLD}/boot/lua/password.lua $MINIBSD_DIR/lua
cp -v ${XIGMANAS_WORLD}/boot/lua/screen.lua $MINIBSD_DIR/lua
cp -v ${XIGMANAS_WORLD}/boot/efi.4th $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/loader_4th $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/loader_4th.efi $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/loader_lua $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/loader_lua.efi $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/loader_simp $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/loader_simp.efi $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/userboot_4th.so $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/userboot_lua.so $MINIBSD_DIR
cp -v ${XIGMANAS_WORLD}/boot/kernel/linker.hints $MINIBSD_DIR/kernel

# Copy files required by bootmenu
if [ 0 != $opt_m ]; then
	cp -v ${XIGMANAS_WORLD}/boot/beastie.4th $MINIBSD_DIR
	cp -v ${XIGMANAS_WORLD}/boot/brand.4th $MINIBSD_DIR
	cp -v ${XIGMANAS_WORLD}/boot/check-password.4th $MINIBSD_DIR
	cp -v ${XIGMANAS_WORLD}/boot/color.4th $MINIBSD_DIR
	cp -v ${XIGMANAS_WORLD}/boot/delay.4th $MINIBSD_DIR
	cp -v ${XIGMANAS_WORLD}/boot/frames.4th $MINIBSD_DIR
	cp -v ${XIGMANAS_WORLD}/boot/menusets.4th $MINIBSD_DIR
	cp -v ${XIGMANAS_WORLD}/boot/menu-commands.4th $MINIBSD_DIR
	cp -v ${XIGMANAS_WORLD}/boot/menu.rc $MINIBSD_DIR
	cp -v ${XIGMANAS_WORLD}/boot/screen.4th $MINIBSD_DIR
	cp -v ${XIGMANAS_WORLD}/boot/shortcuts.4th $MINIBSD_DIR
	cp -v ${XIGMANAS_WORLD}/boot/version.4th $MINIBSD_DIR
fi

# Generate the loader.rc file used by the default boot loader.
cat << EOF > $MINIBSD_DIR/loader.rc
\ Loader.rc

\ Includes additional commands
include /boot/loader.4th
\ include /boot/efi.4th
try-include /boot/loader.rc.local

\ Reads and processes loader.conf variables
initialize

\ maybe-efi-resizecons

\ Tests for password -- executes autoboot first if a password was defined
check-password

\ Load in the boot menu
include /boot/beastie.4th

\ Start the boot menu
beastie-start
EOF
# Set proper permissions to the loader.rc file.
chmod 444 $MINIBSD_DIR/loader.rc

# Generate the loader.conf file using by bootloader
echo "Generate $MINIBSD_DIR/loader.conf"
echo 'mfsroot_load="YES"' > $MINIBSD_DIR/loader.conf
echo 'mfsroot_type="mfs_root"' >> $MINIBSD_DIR/loader.conf
echo 'mfsroot_name="/mfsroot"' >> $MINIBSD_DIR/loader.conf
echo 'hw.est.msr_info="0"' >> $MINIBSD_DIR/loader.conf
echo 'hw.hptrr.attach_generic="0"' >> $MINIBSD_DIR/loader.conf
echo 'hw.msk.msi_disable="1"' >> $MINIBSD_DIR/loader.conf
echo 'kern.maxfiles="6289573"' >> $MINIBSD_DIR/loader.conf
echo 'kern.cam.boot_delay="12000"' >> $MINIBSD_DIR/loader.conf
echo 'kern.geom.label.disk_ident.enable="0"' >> $MINIBSD_DIR/loader.conf
echo 'kern.geom.label.gptid.enable="0"' >> $MINIBSD_DIR/loader.conf
echo 'hint.acpi_throttle.0.disabled="0"' >> $MINIBSD_DIR/loader.conf
echo 'hint.p4tcc.0.disabled="0"' >> $MINIBSD_DIR/loader.conf

# Enable bootsplash?
if [ 0 != $opt_b ]; then
	echo 'splash_bmp_load="YES"' >> $MINIBSD_DIR/loader.conf
	echo 'bitmap_load="YES"' >> $MINIBSD_DIR/loader.conf
	echo 'bitmap_name="/boot/splash.bmp"' >> $MINIBSD_DIR/loader.conf
fi
# Enable bootmenu?
if [ 0 != $opt_m ]; then
	echo 'autoboot_delay="3"' >> $MINIBSD_DIR/loader.conf
else
	echo 'autoboot_delay="-1"' >> $MINIBSD_DIR/loader.conf
fi
# Enable debug?
if [ 0 != $opt_d ]; then
  echo 'verbose_loading="YES"' >> $MINIBSD_DIR/loader.conf
  echo 'boot_verbose=""' >> $MINIBSD_DIR/loader.conf
fi
# Enable serial console?
if [ 0 != $opt_s ]; then
  echo 'console="vidconsole,comconsole"' >> $MINIBSD_DIR/loader.conf
fi
# Disable ACPI?
if [ 0 != $opt_a ]; then
  echo 'hint.acpi.0.disabled="1"' >> $MINIBSD_DIR/device.hints
fi
# iSCSI driver
# echo 'isboot_load="YES"' >> $MINIBSD_DIR/loader.conf
# preload kernel drivers
echo 'zfs_load="YES"' >> $MINIBSD_DIR/loader.conf

# Xen
if [ "dom0" == ${XIGMANAS_XARCH} ]; then
    echo 'xen_kernel="/boot/xen"' >> $MINIBSD_DIR/loader.conf
    echo 'xen_cmdline="dom0_mem=4096M dom0_max_vcpus=4 dom0pvh=1 com1=115200,8n1 guest_loglvl=all loglvl=all console=com1,vga"' >> $MINIBSD_DIR/loader.conf
    echo 'vfs.zfs.arc_max="2G"' >> $MINIBSD_DIR/loader.conf
    echo 'vfs.zfs.arc_min="1G"' >> $MINIBSD_DIR/loader.conf
fi

# Copy kernel.
if [ -e "${XIGMANAS_WORKINGDIR}/kernel.gz" ] ; then
  cp ${XIGMANAS_WORKINGDIR}/kernel.gz $MINIBSD_DIR/kernel
else
  echo "=> ERROR: File kernel.gz does not exist!";
  exit 1;
fi