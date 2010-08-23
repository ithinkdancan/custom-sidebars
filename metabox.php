<p><?php _e('You can assign specific sidebars to this post, just select a sidebar and the default one will be replaced, if it is available on your template.','custom-sidebars')?></p>
<?php if(!empty($sidebars)): foreach($sidebars as $s):?>
	<p><b><?php echo $s;?></b>: 
	<select name="cs_replacement_<?php echo $s ?>">
		<?php foreach($available as $a):?>
		<option id="<?php echo $a?>" <?php echo ($selected[$s]==$a) ? 'selected="selected"' : ''; ?>>
			<?php echo $a; ?>
		</option>
		<?php endforeach;?>
	</select>
	</p>
<?php endforeach; else: ?>
	<p id="message" class="updated"><?php _e('There are not replaceable sidebars selected. You can define what sidebar will be able for replacement in the <a href="themes.php?page=customsidebars">Custom Sidebars config page</a>.','custom-sidebars')?></p>
<?php endif;?>