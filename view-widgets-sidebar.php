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
    <div class="widgets-holder-wrap">
        <div class="sidebar-name">
            <div class="sidebar-name-arrow"><br></div>
            <h3><?php _e('New Sidebar','custom-sidebars') ?><span><img src="http://local.wp33/wp-admin/images/wpspin_dark.gif" class="ajax-feedback" title="" alt=""></span></h3>
        </div>
        <div id="sidebar-2" class="widgets-sortables ui-sortable" style="min-height: 50px; ">
            <div class="sidebar-description">
                <p class="description">The sidebar for the optional Showcase Template</p>
            </div>
        </div>
    </div>
</div>