</div>
</main>
<footer id="g4f">
	<div id="gapfooter"></div>
	<div id="pagefooter">
		<table class="area_data_settings"><colgroup><col style="width:10%"><col style="width:80%"><col style="width:10%"></colgroup><tbody><tr>
			<td class="g4fl">
<?php
				if(Session::isAdmin()):
					if(file_exists($d_sysrebootreqd_path)):
?>
				<a href="/reboot.php" class="g4fi">
					<img src="/images/notify_reboot.png" title="<?=gtext('A reboot is required');?>" alt="<?=gtext('Reboot Required');?>">
				</a>
<?php
					endif;
				endif;
?>
			</td>
			<td class="g4fc"><?=htmlspecialchars(get_product_copyright());?></td>
			<td class="g4fr"></td>
		</tr></tbody></table>
	</div>
</footer>
</body>
</html>
