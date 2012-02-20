<div class="themes-php">
<div class="wrap">


<div id="defaultsidebarspage">
    
    <form action="themes.php?page=customsidebars&p=defaults" method="post">

<div id="poststuff" class="defaultscontainer">

<div  class="postbox closed">
<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e('In a singular post or page','custom-sidebars'); ?></span></h3>
<div class="inside" id="defaultsforposts">
<p><?php _e('To set the sidebar for a single post or page just set it when creating/editing the post.','custom-sidebars'); ?></p>
</div></div>
    
<div  class="postbox closed">
<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e('As the default sidebar for single entries','custom-sidebars'); ?></span></h3>
<div class="inside" id="defaultsforposts">
<p><?php _e('These replacements will be applied to every single post that matches a certain post type or category.','custom-sidebars'); ?></p>
<p><?php _e('The sidebars by categories work in a hierarchycal way, if a post belongs to a parent and a child category it will show the child category sidebars if they are defined, otherwise it will show the parent ones. If no category sidebar for post are defined, the post will show the post post-type sidebar. If none of those sidebars are defined, the theme default sidebar is shown.','custom-sidebars'); ?></p>

        <?php include 'defaults/single_category.php' ?>

        <?php include 'defaults/single_posttype.php' ?>

<p class="submit"><input type="submit" class="button-primary" name="update-defaults-posts" value="<?php _e('Save Changes','custom-sidebars'); ?>" /></p>
</div></div>



<div  class="postbox closed">
<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e('As the default sidebars for archives','custom-sidebars'); ?></span></h3>
<div class="inside" id="defaultsforpages">

<p><?php _e('You can define specific sidebars for the different Wordpress pages. Sidebars for lists of posts pages work in the same hierarchycal way than the one for single posts.','custom-sidebars'); ?></p>

<?php include 'defaults/archive_category.php' ?>
<?php include 'defaults/archive_tag.php' ?>
<?php include 'defaults/archive_posttype.php' ?>
<?php include 'defaults/archive_blog.php' ?>
<?php include 'defaults/archive_author.php' ?>

<p class="submit"><input type="submit" class="button-primary" name="update-defaults-pages" value="<?php _e('Save Changes','custom-sidebars'); ?>" /></p>
</div>

</div>

</form>

</div>


</div>
</div>
    <script>
    
    jQuery('.defaultsContainer').hide();
    jQuery('#defaultsidebarspage').on('click', '.csh3title', function(){
        jQuery(this).siblings('.defaultsContainer').toggle();
    });
    jQuery('#defaultsidebarspage').on('click', '.hndle', function(){
        jQuery(this).siblings('.inside').toggle();
    })
    
</script>