<div class="themes-php">
<div class="wrap">

<?php include('view-tabs.php'); ?>

<div id="editsidebarpage">
<div id="poststuff">

<?php
global $wp_registered_sidebars;
var_dump($wp_registered_sidebars);
?>

<?php var_dump($this->options); ?>

<h2 style="border-bottom:0"><?php echo $sb['id']?></h2>

<form action="themes.php?page=customsidebars" method="post">
	<?php wp_nonce_field('custom-sidebars-update');?>
	<input type="hidden" name="cs_id" value="<?php echo $sb['id']?>" />
	<div id="titlediv">
		<h3><label for="sidebar_name"><?php _e('Name','custom-sidebars'); ?></label></h3>
		<input type="text" name="sidebar_name" value="<?php echo $sb['name']?>" id="title" maxlength="30" />
	</div>
	
	
	<h3><label for="sidebar_description"><?php _e('Description','custom-sidebars'); ?></label></h3>
	<input type="text" name="sidebar_description" value="<?php echo $sb['description']?>" style="width:100%;margin-bottom:20px;" maxlength="200" />
	
	
	<div id="col-right">
	
		<h3><label for="cs_after_title"><?php _e('After Title','custom-sidebars'); ?></label></h3>
		<textarea name="cs_after_title" class="large-text code" style="height:100px;margin-bottom:20px;"><?php echo $sb['after_title']?></textarea>
		
		<h3><label for="cs_after_widget"><?php _e('After Widget','custom-sidebars'); ?></label></h3>
		<textarea name="cs_after_widget" class="large-text code" style="height:100px"><?php echo $sb['after_widget']?></textarea>
		
	</div>
	
	
	<div id="col-left">
	
		<h3><label for="cs_before_title"><?php _e('Before Title','custom-sidebars'); ?></label></h3>
		<textarea name="cs_before_title" class="large-text code" style="height:100px;margin-bottom:20px;"><?php echo $sb['before_title']?></textarea>
		
		<h3><label for="cs_before_widget"><?php _e('Before Widget','custom-sidebars'); ?></label></h3>
		<textarea name="cs_before_widget" class="large-text code" style="height:100px" ><?php echo $sb['before_widget']?></textarea>
		
	</div>

	<p class="submit"><input type="submit" class="button-primary" name="update-sidebar" value="<?php _e('Save Changes','custom-sidebars'); ?>" /></p>
	
</form>

</div>
</div>


<?php include('view-footer.php'); ?>

</div>
</div>