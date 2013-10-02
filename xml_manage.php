<div class="wrap">
	<h2>
	XML Feed Management
	</h2>
	<br>
	<br>
	
	<br>
	<?php global $wpdb; 
	$xml_setting=$wpdb->get_row("SELECT url FROM xml_feed  ", ARRAY_N);
	
	?>
	<form action="" method="post" >
	<label>XML Feed URL</label>
	<input type="text" name="url" value="<?php echo $xml_setting[0];?>" /><br />
	<?php submit_button();?>
	</form>
	</div>
