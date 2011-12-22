<?php

/**
 * The view for the widgets page of the admin area.
 * There are some HTML to be added for having all the functionality, so we 
 * include it at the begining of the page, and it's placed later via js.
 */
?>
<div id="cs-widgets-extra">
    <div id="cs-title-options">
        <h2><?php _e('Sidebars','custom-sidebars') ?></h2>
        <div class="cs-options" style="text-align:right">
            <a href="themes.php?page=customsidebars" class="button create-sidebar-button"><?php _e('Create a new sidebar','custom-sidebars') ?></a>
        </div>
    </div>
    <div class="widgets-holder-wrap new-sidebar-holder">
        <div class="sidebar-name">
            <div class="sidebar-name-arrow"><br></div>
            <h3><?php _e('New Sidebar','custom-sidebars') ?><span><img src="http://local.wp33/wp-admin/images/wpspin_dark.gif" class="ajax-feedback" title="" alt=""></span></h3>
        </div>
        <div id="new-sidebar" class="widgets-sortables ui-sortable" style="min-height: 50px; ">
            
        </div>
    </div>
    <div id="new-sidebar-form">
        <form action="themes.php?page=customsidebars" method="post">
		<?php wp_nonce_field( 'cs-create-sidebar', '_create_nonce');?>
		<?php wp_nonce_field( 'cs-wpnonce', '_nonce_nonce');?>
		<div id="namediv">
			<label for="sidebar_name"><?php _e('Name','custom-sidebars'); ?></label>
			<input type="text" name="sidebar_name" size="30" tabindex="1" value="" id="sidebar_name" />
			<p class="description"><?php _e('The name has to be unique.','custom-sidebars')?></p>
		</div>
			
		<div id="addressdiv">			
			<label for="sidebar_description"><?php echo _e('Description','custom-sidebars'); ?></label>
			<input type="text" name="sidebar_description" size="30" class="code" tabindex="1" value="" id="sidebar_description" />
		</div>
		<p class="submit"><input type="submit" class="button-primary" id="cs-create-sidebar" name="cs-create-sidebar" value="<?php _e('Create Sidebar','custom-sidebars'); ?>" /></p>
	</form>        
    </div>
</div>