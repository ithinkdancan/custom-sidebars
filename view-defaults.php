<div class="themes-php">
<div class="wrap">

<?php include('view-tabs.php'); ?>

<div id="defaultsidebarspage">
<div id="poststuff">

<h2><?php _e('Default Sidebars','custom-sidebars'); ?></h2>
<p><?php _e('These sidebars replacements will be applied to every entry of the post type, unless the entry was told to display a specific sidebar.','custom-sidebars'); ?></p>

<div id="posttypes-default" class="meta-box-sortables">
	<?php foreach($post_types as $pt):?>
	<div id="pt-<?php echo $pt; ?>" class="postbox closed" >
		<div class="handlediv" title="Haz clic para cambiar"><br /></div>
		<h3 class='hndle'><span><?php _e($pt); ?></span></h3>
		
		<div class="inside">
		<?php if(!empty($modifiable)): foreach($modifiable as $m):?>
			<p><?php echo $m; ?>: 
				<select name="ptdefault-<?php echo $pt;?>-<?php echo $m;?>">
					<option value=""></option>
				<?php foreach($allsidebars as $key => $sb):?>
					<option value="<?php echo $key; ?>" <?php echo (isset($defaults[$pt][$m]) && $defaults[$pt][$m]==$key) ? 'selected="selected"' : ''; ?>>
						<?php echo $sb['name']; ?>
					</option>
				<?php endforeach;?>
				</select>
			</p>
		<?php endforeach;else:?>
			<p><?php _e('There are no replaceable sidebars selected. You must select some of them in the form above to be able for replacing them in all the post type entries.','custom-sidebars'); ?></p>
		<?php endif;?>
		</div>
		
	</div>
	
	<?php endforeach; ?>
</div> 

<p class="submit"><input type="submit" class="button-primary" name="update-modifiable" value="<?php _e('Save Changes','custom-sidebars'); ?>" /></p>





</div>
</div>

<?php include('view-footer.php'); ?>

</div>
</div>