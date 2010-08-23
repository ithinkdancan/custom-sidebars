
<div class="wrap">
<div id="icon-themes" class="icon32"><br /></div>
<h2><?php _e('Custom Sidebars','custom-sidebars')?></h2>
<?php $this->message(); ?>

<div id="poststuff">
<div id="col-right">


<h2 class="title"><?php _e('New Sidebar','custom-sidebars'); ?></h2>
<p><?php _e('When a custom sidebar is created, it is shown in the widgets view and you can define what the new sidebar will contain. Once the sidebar is setted up, it is possible to select it for displaying in any post or page.', 'custom-sidebars'); ?></p>
<form action="themes.php?page=customsidebars" method="post">
<div id="namediv" class="stuffbox">
<h3><label for="sidebar_name"><?php _e('Name','custom-sidebars'); ?></label></h3>
<div class="inside">
	<input type="text" name="sidebar_name" size="30" tabindex="1" value="" id="link_name" />
    <p><?php _e('The name has to be unique.','custom-sidebars')?></p>
</div>
</div>

<div id="addressdiv" class="stuffbox">

<h3><label for="sidebar_description"><?php echo _e('Description','custom-sidebars'); ?></label></h3>
<div class="inside">
	<input type="text" name="sidebar_description" size="30" class="code" tabindex="1" value="" id="link_url" />
</div>
</div>



<input type="hidden" id="_wpnonce" name="_wpnonce" value="0a6b5c3eae" /><input type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/themes.php?page=customsidebars" /><p class="submit"><input type="submit" class="button-primary" name="create-sidebars" value="<?php _e('Create Sidebar','custom-sidebars'); ?>" /></p>
</form>


<h2><?php _e('All the Custom Sidebars','custom-sidebars'); ?></h2>
<p><?php _e('If a sidebar is deleted and is currently on use, the posts and pages which uses it will show the default sidebar instead.','custom-sidebars'); ?></p>
<table class="widefat fixed" cellspacing="0">

<thead>
<tr class="thead">
	<th scope="col" id="name" class="manage-column column-name" style=""><?php _e('Name','custom-sidebars'); ?></th>
	<th scope="col" id="email" class="manage-column column-email" style=""><?php _e('Description','custom-sidebars'); ?></th>
	<th scope="col" id="role" class="manage-column column-role" style=""><?php _e('Delete','custom-sidebars'); ?></th>

</tr>
</thead>

<script type="text/javascript">
	jQuery(document).ready( function($){
		$('.csdeletelink').click(function(){
			return confirm('<?php _e('Are you sure to delete this sidebar?','custom-sidebars');?>');
		});
	});
</script>
<tbody id="custom-sidebars" class="list:user user-list">

	<?php if(sizeof($customsidebars)>0): foreach($customsidebars as $cs):?>
	<tr id="cs-1" class="alternate">
		<td class="name column-name"><?php echo $cs['name']?></td>
		<td class="email column-email"><?php echo $cs['description']?></td>
		<td class="role column-role"><a class="csdeletelink" href="themes.php?page=customsidebars&delete=<?php echo $cs['id']; ?>"><?php _e('Delete','custom-sidebars'); ?></a></td>
	</tr>
	<?php endforeach;else:?>
	<tr id="cs-1" class="alternate">
		<td colspan="3"><?php _e('There are no custom sidebars available. You can create a new one using the left form.','custom-sidebars'); ?></td>
	</tr>
	<?php endif;?>
	
</tbody>

</table>


</div>



<div id="col-left">

<form action="themes.php?page=customsidebars" method="post">
<div id="modifiable-sidebars">
<h2><?php _e('Replaceable Sidebars','custom-sidebars'); ?></h2>
<p><?php _e('Select here the sidebars available for replacing. They will appear for replace when a post or page is edited or created. They will be also available for the default replacements of post type sidebars. You can select several bars holding the SHIFT key when clicking on them.','custom-sidebars'); ?></p>
<div id="msidebardiv" class="stuffbox">
<h3><label for="sidebar_name"><?php _e('Select the boxes available for substitution','custom-sidebars'); ?></label></h3>
<div class="inside">
	<select name="modifiable[]" multiple="multiple" size="5" style="height:auto;">
	<?php foreach($themesidebars as $ts):?>
		<option id="<?php echo $ts;?>" <?php echo (!empty($modifiable) && array_search($ts, $modifiable)!== FALSE) ? 'selected="selected"' : ''; ?>>
		<?php echo $ts;?>
		</option>
	<?php endforeach;?>
	</select>
</div>
</div>
</div>
<input type="hidden" id="_wpnonce" name="_wpnonce" value="0a6b5c3eae" /><input type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/themes.php?page=customsidebars" /><p class="submit"><input type="submit" class="button-primary" name="update-modifiable" value="<?php _e('Save Changes','custom-sidebars'); ?>" /></p>



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
				<?php foreach($allsidebars as $sb):?>
					<option value="<?php echo $sb; ?>" <?php echo (isset($defaults[$pt][$m]) && $defaults[$pt][$m]==$sb) ? 'selected="selected"' : ''; ?>>
						<?php echo $sb; ?>
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
</div>

<p class="submit"><input type="submit" class="button-primary" name="update-modifiable" value="<?php _e('Save Changes','custom-sidebars'); ?>" /></p>


</form>
</div>

</div>


