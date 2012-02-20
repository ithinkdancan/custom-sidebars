<div class="defaultsSelector">
  
<h3 class="csh3title"><?php _e('Category posts list','custom-sidebars'); ?></h3>
<div class="defaultsContainer"><?php if(!empty($categories)): foreach($categories as $c): if($c->cat_ID != 1):?>
        <div id="category-page-<?php echo $c->id; ?>" class="postbox closed" >
            <div class="handlediv" title="Haz clic para cambiar"><br /></div>
            <h3 class='hndle'><span><?php _e($c->name); ?></span></h3>
            
            <div class="inside">
            <?php if(!empty($modifiable)): foreach($modifiable as $m): $sb_name = $allsidebars[$m]['name'];?>
                <p><?php echo $sb_name; ?>: 
                    <select name="category_page_<?php echo $c->cat_ID; ?>_<?php echo $m;?>">
                        <option value=""></option>
                    <?php foreach($allsidebars as $key => $sb):?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($defaults['category_pages'][$c->cat_ID][$m]) && $defaults['category_pages'][$c->cat_ID][$m]==$key) ? 'selected="selected"' : ''; ?>>
                            <?php echo $sb['name']; ?>
                        </option>
                    <?php endforeach;?>
                    </select>
                    <a href="#" class="selectSidebar"><?php _e('<- Here' )?></a>
                </p>
            <?php endforeach;else:?>
                <p><?php _e('There are no replaceable sidebars selected. You must select some of them in the form above to be able for replacing them in all the post type entries.','custom-sidebars'); ?></p>
            <?php endif;?>
            </div>
            
        </div>
        
        <?php endif;endforeach;else: ?>
            <p><?php _e('There are no categories available.','custom-sidebars'); ?></p>
        <?php endif;?></div>
</div>