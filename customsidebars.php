<?php
/*
Plugin Name: Custom sidebars
Plugin URI: http://marquex.posterous.com/pages/custom-sidebars
Description: Allows to create your own widgetized areas and custom sidebars, and select what sidebars to use for each post or page.
Version: 0.6
Author: Javier Marquez (marquex@gmail.com)
Author URI: http://marquex.mp
*/

if(!class_exists('CustomSidebars')):

class CustomSidebars{
	
	var $message = '';
	var $message_class = '';
	
	//The name of the option that stores the info of the new bars.
	var $option_name = "cs_sidebars";
	//The name of the option that stores which bars are replaceable, and the default
	//replacements. The value is stored in $this->options
	var $option_modifiable = "cs_modifiable";
	
	
	var $sidebar_prefix = 'cs-';
	var $postmeta_key = '_cs_replacements';
	var $cap_required = 'edit_themes';
	var $ignore_post_types = array('attachment', 'revision', 'nav_menu_item', 'pt-widget');
	var $options = array();
	
	function CustomSidebars(){
		$this->retrieveOptions();
	}
	
	function retrieveOptions(){
		$this->options = get_option($this->option_modifiable);
	}
	
	function getCustomSidebars(){
		$sidebars = get_option($this->option_name);
		if($sidebars)
			return $sidebars;
		return array();
	}
	
	function getThemeSidebars($include_custom_sidebars = FALSE){
		/*
		$sidebars = get_option('sidebars_widgets');
		$themesidebars = array();
		$customsidebars = array();
		if($sidebars){
			foreach(array_keys($sidebars) as $sb){
				if(array_search($sb, array('wp_inactive_widgets', 'array_version')) === FALSE){
					if(substr($sb, 0, 3) != $this->sidebar_prefix)
						$themesidebars[] = $sb;
					else
						$customsidebars[] = $sb;
				}
			}
		}
		
		if($include_custom_sidebars){
			sort($customsidebars);
			return array_merge($customsidebars , $themesidebars);
		}
			
		return $themesidebars;*/
		
		global $wp_registered_sidebars;		
		$allsidebars = $wp_registered_sidebars;
		ksort($allsidebars);
		if($include_custom_sidebars)
			return $allsidebars;
		
		$themesidebars = array();
		foreach($allsidebars as $key => $sb){
			if(substr($key, 0, 3) != $this->sidebar_prefix)
				$themesidebars[$key] = $sb;
		}
		
		return $themesidebars;
	}
	
	function registerCustomSidebars(){
		$sb = $this->getCustomSidebars();
		if(!empty($sb)){
			foreach($sb as $sidebar){
				register_sidebar($sidebar);
			}
		}
	}
	
	function replaceSidebars(){
		
		global $_wp_sidebars_widgets, $post, $wp_registered_sidebars, $wp_registered_widgets;
		
		$updated = FALSE;
		$modifiable = $this->getModifiableSidebars();
		if(!empty($modifiable)){
			//Here, where the magic happens
			$default_replacements = $this->getDefaultReplacements();
			foreach($modifiable as $sb){
				
				if($replacement = $this->determineReplacement($default_replacements, $sb)){
					
					//var_dump($replacement);
					list($replacement, $replacement_type) = $replacement;
					if(sizeof($_wp_sidebars_widgets[$replacement]) == 0){ //No widgets on custom bar, show nothing
						$wp_registered_widgets['csemptywidget'] = $this->getEmptyWidget();
						$_wp_sidebars_widgets[$sb] = array('csemptywidget');
					}
					else{
						$_wp_sidebars_widgets[$sb] = $_wp_sidebars_widgets[$replacement];
						//replace before/after widget/title?
						$sidebar_for_replacing = $wp_registered_sidebars[$replacement];
						if($this->replace_before_after_widget($sidebar_for_replacing))
							$wp_registered_sidebars[$sb] = $sidebar_for_replacing;
					}
				}
				else
					echo 'No replacement';
			}
		}
	}
	
	function replace_before_after_widget($sidebar){
		return (trim($sidebar['before_widget']) != '' OR
			trim($sidebar['after_widget']) != '' OR
			trim($sidebar['before_title']) != '' OR
			trim($sidebar['after_title']) != '');
	}
	
	function deleteSidebar(){
		if(! current_user_can($this->cap_required) )
			return new WP_Error('cscantdelete', __('You do not have permission to delete sidebars','custom-sidebars'));
			
		if (! wp_verify_nonce($_REQUEST['_n'], 'custom-sidebars-delete') ) die('Security check stop your request.'); 
		
		$newsidebars = array();
		$deleted = FALSE;
		
		$custom = $this->getCustomSidebars();
		
		if(!empty($custom)){
		
		foreach($custom as $sb){
			if($sb['id']!=$_GET['delete'])
				$newsidebars[] = $sb;
			else
				$deleted = TRUE;
		}
		}//endif custom
		//update option
		update_option( $this->option_name, $newsidebars );
		
		/*//Let's delete it also in the sidebar-widgets
		$sidebars2 = get_option('sidebars_widgets');
		if(array_search($id, array_keys($sidebars2))!==FALSE){
			unset($sidebars2[$id]);
			update_option('sidebars_widgets', $sidebars2);			 
		}*/

		$this->refreshSidebarsWidgets();
		
		if($deleted)
			$this->setMessage(sprintf(__('The sidebar "%s" has been deleted.','custom-sidebars'), $_GET['delete']));
		else
			$this->setError(sprintf(__('There was not any sidebar called "%s" and it could not been deleted.','custom-sidebars'), $_GET['delete']));
	}
	
	function createPage(){
		
		//$this->refreshSidebarsWidgets();
		if(!empty($_POST)){
			if(isset($_POST['create-sidebars'])){
				check_admin_referer('custom-sidebars-new');
				$this->storeSidebar();
			}
			else if(isset($_POST['update-sidebar'])){
				check_admin_referer('custom-sidebars-update');
				$this->updateSidebar();
			}		
			else if(isset($_POST['update-modifiable']))
				$this->updateModifiable();
			else if(isset($_POST['update-defaults-posts']) OR isset($_POST['update-defaults-pages'])){
				$this->storeDefaults();
			
			}
				
			else if(isset($_POST['reset-sidebars']))
				$this->resetSidebars();			
				
			$this->retrieveOptions();
		}
		else if(!empty($_GET['delete'])){
			$this->deleteSidebar();
			$this->retrieveOptions();			
		}
		else if(!empty($_GET['p'])){
			if($_GET['p']=='edit' && !empty($_GET['id'])){
				$customsidebars = $this->getCustomSidebars();
				if(! $sb = $this->getSidebar($_GET['id'], $customsidebars))
					return new WP_Error('cscantdelete', __('You do not have permission to delete sidebars','custom-sidebars'));
				include('view-edit.php');
				return;	
			}
		}
		
		$customsidebars = $this->getCustomSidebars();
		$themesidebars = $this->getThemeSidebars();
		$allsidebars = $this->getThemeSidebars(TRUE);
		$defaults = $this->getDefaultReplacements();
		$modifiable = $this->getModifiableSidebars();
		$post_types = $this->getPostTypes();
		
		$deletenonce = wp_create_nonce('custom-sidebars-delete');
		
		//var_dump($defaults);
		
		//Form
		if(!empty($_GET['p'])){
			if($_GET['p']=='defaults'){
				$categories = get_categories(array('hide_empty' => 0));
				include('view-defaults.php');
			}
			else if($_GET['p']=='edit')
				include('view-edit.php');
			else
				include('view.php');	
				
		}
		else		
			include('view.php');		
	}
	
	function addSubMenus(){
		$page = add_submenu_page('themes.php', __('Custom sidebars','custom-sidebars'), __('Custom sidebars','custom-sidebars'), 'edit_themes', 'customsidebars', array($this, 'createPage'));
		
        add_action('admin_print_scripts-' . $page, array($this, 'addScripts'));
	}
	
	function addScripts(){
		wp_enqueue_script('post');
		echo '<link type="text/css" rel="stylesheet" href="'. plugins_url('/cs_style.css', __FILE__) .'" />';
	}
	
	function addPostMetabox(){
		if(current_user_can('edit_themes'))
			add_meta_box('customsidebars-mb', 'Sidebars', array($this,'printMetabox'), 'post', 'side');
	}
	function addPageMetabox(){
		if(current_user_can('edit_themes'))
			add_meta_box('customsidebars-mb', 'Sidebars', array($this,'printMetabox'), 'page', 'side');
	}
	
	function printMetabox(){
		global $post, $wp_registered_sidebars;
		
		$replacements = $this->getReplacements($post->ID);
			
		//$available = array_merge(array(''), $this->getThemeSidebars(TRUE));
		$available = $wp_registered_sidebars;
		ksort($available);
		$sidebars = $this->getModifiableSidebars();
		$selected = array();
		if(!empty($sidebars)){
			foreach($sidebars as $s){
				if(isset($replacements[$s]))
					$selected[$s] = $replacements[$s];
				else
					$selected[$s] = '';
			}
		}
		
		include('metabox.php');
	}
	
	function loadTextDomain(){
		$dir = basename(dirname(__FILE__))."/lang";
		load_plugin_textdomain( 'custom-sidebars', 'wp-content/plugins/'.$dir, $dir);
	}
	
	function getReplacements($postid){
		$replacements = get_post_meta($postid, $this->postmeta_key, TRUE);
		if($replacements == '')
			$replacements = array();
		else
			$replacements = $replacements;
		return $replacements;
	}
	
	function getModifiableSidebars(){
		if( $modifiable = $this->options ) //get_option($this->option_modifiable) )
			return $modifiable['modifiable'];
		return array(); 
	}
	
	function getDefaultReplacements(){
		if( $defaults = $this->options ){//get_option($this->option_modifiable) )
			$defaults['post_type_posts'] = $defaults['defaults'];
			unset($defaults['modifiable']);
			unset($defaults['defaults']);
			return $defaults;
		}
		return array(); 
	}
	
	function updateModifiable(){
		check_admin_referer('custom-sidebars-options', 'options_wpnonce');
		$options = $this->options ? $this->options : array();
		
		//Modifiable bars
		if(isset($_POST['modifiable']) && is_array($_POST['modifiable']))
			$options['modifiable'] = $_POST['modifiable'];

		
		if($this->options !== FALSE)
			update_option($this->option_modifiable, $options);
		else
			add_option($this->option_modifiable, $options);
			
		$this->setMessage(__('The custom sidebars settings has been updated successfully.','custom-sidebars'));
	}
	
	function storeDefaults(){
		
		$options = $this->options;
		$modifiable = $this->getModifiableSidebars();
		
		//Post-types posts and lists. Posts data are called default in order to keep backwards compatibility;
		
		$options['defaults'] = array();
		$options['post_type_pages'] = array();
		
		foreach($this->getPostTypes() as $pt){
			if(!empty($modifiable)){
				foreach($modifiable as $m){
					if(isset($_POST["type_posts_{$pt}_$m"]) && $_POST["type_posts_{$pt}_$m"]!=''){
						if(! isset($options['defaults'][$pt]))
							$options['defaults'][$pt] = array();
						
						$options['defaults'][$pt][$m] = $_POST["type_posts_{$pt}_$m"];
					}
					
					if(isset($_POST["type_page_{$pt}_$m"]) && $_POST["type_page_{$pt}_$m"]!=''){
						if(! isset($options['post_type_pages'][$pt]))
							$options['post_type_pages'][$pt] = array();
						
						$options['post_type_pages'][$pt][$m] = $_POST["type_page_{$pt}_$m"];
					}
				}
			}
		}
		
		
		//Category posts and post lists.
		
		$options['category_posts'] = array();
		$options['category_pages'] = array();
		$categories = get_categories(array('hide_empty' => 0));
		foreach($categories as $c){
			if(!empty($modifiable)){
				foreach($modifiable as $m){
					$catid = $c->cat_ID;
					if(isset($_POST["category_posts_{$catid}_$m"]) && $_POST["category_posts_{$catid}_$m"]!=''){
						if(! isset($options['category_posts'][$catid]))
							$options['category_posts'][$catid] = array();
						
						$options['category_posts'][$catid][$m] = $_POST["category_posts_{$catid}_$m"];
					}
					
					if(isset($_POST["category_page_{$catid}_$m"]) && $_POST["category_page_{$catid}_$m"]!=''){
						if(! isset($options['category_pages'][$catid]))
							$options['category_pages'][$catid] = array();
						
						$options['category_pages'][$catid][$m] = $_POST["category_page_{$catid}_$m"];
					}
				}
			}
		}
		
		// Blog page
		
		$options['blog'] = array();
		if(!empty($modifiable)){
			foreach($modifiable as $m){
				if(isset($_POST["blog_page_$m"]) && $_POST["blog_page_$m"]!=''){
					if(! isset($options['blog']))
						$options['blog'] = array();
					
					$options['blog'][$m] = $_POST["blog_page_$m"];
				}
			}
		}
		
		// Tag page
		
		$options['tags'] = array();
		if(!empty($modifiable)){
			foreach($modifiable as $m){
				if(isset($_POST["tag_page_$m"]) && $_POST["tag_page_$m"]!=''){
					if(! isset($options['tags']))
						$options['tags'] = array();
					
					$options['tags'][$m] = $_POST["tag_page_$m"];
				}
			}
		}
		
		// Author page
		
		$options['authors'] = array();
		if(!empty($modifiable)){
			foreach($modifiable as $m){
				if(isset($_POST["authors_page_$m"]) && $_POST["authors_page_$m"]!=''){
					if(! isset($options['authors']))
						$options['authors'] = array();
					
					$options['authors'][$m] = $_POST["authors_page_$m"];
				}
			}
		}
		
		
		//Store defaults
		if($this->options !== FALSE)
			update_option($this->option_modifiable, $options);
		else{
			$options['modifiable'] = array();
			add_option($this->option_modifiable, $options);
		}
			
		$this->setMessage(__('The default sidebars has been updated successfully.','custom-sidebars'));
		
	}
	
	function storeReplacements( $post_id ){
		if(! current_user_can('edit_themes'))
			return;
		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
		// to do anything (Copied and pasted from wordpress add_metabox_tutorial)
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_id;
			
		// make sure meta is added to the post, not a revision
		if ( $the_post = wp_is_post_revision($post_id) )
			$post_id = $the_post;
		
		$sidebars = $this->getModifiableSidebars();
		$data = array();
		if(!empty($sidebars)){
		foreach($sidebars as $s){
			if(isset($_POST["cs_replacement_$s"])){
				$it = $_POST["cs_replacement_$s"];
				if(!empty($it) && $it!='')
					$data[$s] = $it;
			}
		}
		}//endif sidebars
		$old_data = get_post_meta($post_id, $this->postmeta_key, TRUE);
		if($old_data == ''){
			if(!empty($data))
				add_post_meta($post_id, $this->postmeta_key, $data, TRUE);
		}
		else{
			if(!empty($data))
				update_post_meta($post_id, $this->postmeta_key, $data);
			else
				delete_post_meta($post_id, $this->postmeta_key);
		}
	}
	
	function storeSidebar(){
		$name = trim($_POST['sidebar_name']);
		$description = trim($_POST['sidebar_description']);
		if(empty($name) OR empty($description))
			$this->setError(__('You have to fill all the fields to create a new sidebar.','custom-sidebars'));
		else{
			$id = $this->sidebar_prefix . sanitize_title_with_dashes($name);
			$sidebars = get_option($this->option_name, FALSE);
			if($sidebars !== FALSE){
				$sidebars = $sidebars;
				if(! $this->getSidebar($id,$sidebars) ){
					//Create a new sidebar
					$sidebars[] = array(
						'name' => __( $name ,'custom-sidebars'),
						'id' => $id,
						'description' => __( $description ,'custom-sidebars'),
						'before_widget' => '', //all these fields are not needed, theme ones will be used
						'after_widget' => '',
						'before_title' => '',
						'after_title' => '',
						) ;
						
					
					//update option
					update_option( $this->option_name, $sidebars );
					
					/*
					//Let's store it also in the sidebar-widgets
					$sidebars2 = get_option('sidebars_widgets');
					if(array_search($id, array_keys($sidebars2))===FALSE){
						$sidebars2[$id] = array(); 
					}
					
					update_option('sidebars_widgets', $sidebars2); */
						
					$this->refreshSidebarsWidgets();
					
					
					$this->setMessage( __('The sidebar has been created successfully.','custom-sidebars'));
					
					
				}
				else
					$this->setError(__('There is already a sidebar registered with that name, please choose a different one.','custom-sidebars'));
			}
			else{
				$id = $this->sidebar_prefix . sanitize_title_with_dashes($name);
				$sidebars= array(array(
						'name' => __( $name ,'custom-sidebars'),
						'id' => $id,
						'description' => __( $description ,'custom-sidebars'),
						'before_widget' => '',
						'after_widget' => '',
						'before_title' => '',
						'after_title' => '',
						) );
				add_option($this->option_name, $sidebars);
				
			/*	//Let's store it also in the sidebar-widgets
				$sidebars2 = get_option('sidebars_widgets');
				if(array_search($id, array_keys($sidebars2))===FALSE){
					$sidebars2[$id] = array(); 
				}
				
				update_option('sidebars_widgets', $sidebars2); */
				
				$this->refreshSidebarsWidgets();
				
				$this->setMessage( __('The sidebar has been created successfully.','custom-sidebars'));					
			}
		}
	}
	
	function updateSidebar(){
		$id = trim($_POST['cs_id']);
		$name = trim($_POST['sidebar_name']);
		$description = trim($_POST['sidebar_description']);
		$before_widget = trim($_POST['cs_before_widget']);
		$after_widget = trim($_POST['cs_after_widget']);
		$before_title = trim($_POST['cs_before_title']);
		$after_title = trim($_POST['cs_after_title']);
		
		$sidebars = $this->getCustomSidebars();
		
		//Check the id		
		$url = parse_url($_POST['_wp_http_referer']);
		
		if(isset($url['query'])){
			parse_str($url['query'], $args);
			if($args['id'] != $id)
				return new WP_Error(__('The operation is not secure and it cannot be completed.','custom-sidebars'));
		}
		else
			return new WP_Error(__('The operation is not secure and it cannot be completed.','custom-sidebars'));
		
		
		$newsidebars = array();
		foreach($sidebars as $sb){
			if($sb['id'] != $id)
				$newsidebars[] = $sb;
			else
				$newsidebars[] = array(
						'name' => __( $name ,'custom-sidebars'),
						'id' => $id,
						'description' => __( $description ,'custom-sidebars'),
						'before_widget' =>  __( $before_widget ,'custom-sidebars'),
						'after_widget' => __( $after_widget ,'custom-sidebars'),
						'before_title' =>  __( $before_title ,'custom-sidebars'),
						'after_title' =>  __( $after_title ,'custom-sidebars'),
						) ;
		}
		
		//update option
		update_option( $this->option_name, $newsidebars );
		$this->refreshSidebarsWidgets();
		
		$this->setMessage( sprintf(__('The sidebar "%s" has been updated successfully.','custom-sidebars'), $id ));
	}
	
	function createCustomSidebar(){
		echo '<div class="widget-liquid-left" style="text-align:right"><a href="themes.php?page=customsidebars" class="button">' . __('Create a new sidebar','custom-sidebars') . '</a></div>';
	}
	
	function getSidebar($id, $sidebars){
		$sidebar = false;
		$nsidebars = sizeof($sidebars);
		$i = 0;
		while(! $sidebar && $i<$nsidebars){
			if($sidebars[$i]['id'] == $id)
				$sidebar = $sidebars[$i];
			$i++;
		}
		return $sidebar;
	}
	
	function message($echo = TRUE){
		$message = '';
		if(!empty($this->message))
			$message = '<div id="message" class="' . $this->message_class . '">' . $this->message . '</div>';
		
		if($echo)
			echo $message;
		else
			return $message;		
	}
	
	function setMessage($text){
		$this->message = $text;
		$this->message_class = 'updated';
	}
	
	function setError($text){
		$this->message = $text;
		$this->message_class = 'error';
	}
	
	function getPostTypes(){
		$pt = get_post_types();
		$ptok = array();
		
		foreach($pt as $t){
			if(array_search($t, $this->ignore_post_types) === FALSE)
				$ptok[] = $t;
		}
		
		return $ptok; 
	}
	
	function getEmptyWidget(){
		return array(
			'name' => 'CS Empty Widget',
			'id' => 'csemptywidget',
			'callback' => array(new CustomSidebarsEmptyPlugin(), 'display_callback'),
			'params' => array(array('number' => 2)),
			'classname' => 'CustomSidebarsEmptyPlugin',
			'description' => 'CS dummy widget'
		);
	}
	
	function refreshSidebarsWidgets(){
		$widgetized_sidebars = get_option('sidebars_widgets');
		$delete_widgetized_sidebars = array();
		$cs_sidebars = get_option($this->option_name);
		
		foreach($widgetized_sidebars as $id => $bar){
			if(substr($id,0,3)=='cs-'){
				$found = FALSE;
				foreach($cs_sidebars as $csbar){
					if($csbar['id'] == $id)
						$found = TRUE;
				}
				if(! $found)
					$delete_widgetized_sidebars[] = $id;
			}
		}
		
		
		foreach($cs_sidebars as $cs){
			if(array_search($cs['id'], array_keys($widgetized_sidebars))===FALSE){
				$widgetized_sidebars[$cs['id']] = array(); 
			}
		}
		
		foreach($delete_widgetized_sidebars as $id){
			unset($widgetized_sidebars[$id]);
		}
		
		update_option('sidebars_widgets', $widgetized_sidebars);
		
	}
	
	function resetSidebars(){
		if(! current_user_can($this->cap_required) )
			return new WP_Error('cscantdelete', __('You do not have permission to delete sidebars','custom-sidebars'));
			
		if (! wp_verify_nonce($_REQUEST['reset-n'], 'custom-sidebars-delete') ) die('Security check stopped your request.'); 
		
		delete_option($this->option_modifiable);
		delete_option($this->option_name);
		
		$widgetized_sidebars = get_option('sidebars_widgets');	
		$delete_widgetized_sidebars = array();	
		foreach($widgetized_sidebars as $id => $bar){
			if(substr($id,0,3)=='cs-'){
				$found = FALSE;
				if(empty($cs_sidebars))
					$found = TRUE;
				else{
					foreach($cs_sidebars as $csbar){
						if($csbar['id'] == $id)
							$found = TRUE;
					}
				}
				if(! $found)
					$delete_widgetized_sidebars[] = $id;
			}
		}
		
		foreach($delete_widgetized_sidebars as $id){
			unset($widgetized_sidebars[$id]);
		}
		
		update_option('sidebars_widgets', $widgetized_sidebars);
		
		$this->setMessage( __('The Custom Sidebars data has been removed successfully,','custom-sidebars'));	
	}
	
	/**
	 * 
	 * @param $defaults The default sidebars array
	 * @param $sidebar The current sidebar that we will the replacement for.
	 * @return An array with the replacement and type of replacement or false if no replacement has been found.
	 */
	function determineReplacement($defaults, $sidebar){
		//posts
		if(is_single()){
			//print_r("Single");
			//Post sidebar
			global $post;
			$replacements = get_post_meta($post->ID, $this->postmeta_key, TRUE);
			if(is_array($replacements) && !empty($replacements[$sidebar]))
				return array($replacements[$sidebar], 'particular');
				
			//Category sidebar
			global $sidebar_category;
			if(!empty($sidebar_category) && $sidebar_category !== FALSE){
				if(! empty($defaults['category_posts'][$sidebar_category][$sidebar]))
					return array($defaults['category_posts'][$sidebar_category][$sidebar], 'category_posts');
			}
			else if(empty($sidebar_category)){
				if($sidebar_category = $this->getSidebarCategory($post->ID, $defaults['category_posts'])){
					echo "sidebar category: $sidebar_category";
					//var_dump($defaults['category_posts']);
					return array($defaults['category_posts'][$sidebar_category][$sidebar], 'category_posts');
				}
			}
			
			//Post-type sidebar
			$post_type = get_post_type($post);
			if(isset($defaults['post_type_posts'][$post_type]) && isset($defaults['post_type_posts'][$post_type][$sidebar]))
				return array($defaults['post_type_posts'][$post_type][$sidebar], 'post_type_posts');
			
			//No custom bar
			return FALSE;
		}
		
		if(is_category()){
			//print_r("Category");
			//Category sidebar
			global $sidebar_category;
			if(!empty($sidebar_category) && $sidebar_category !== FALSE){
				if(! empty($defaults['category_pages'][$sidebar_category][$sidebar]))
					return array($defaults['category_pages'][$sidebar_category][$sidebar], 'category_pages');
			}
			else if(empty($sidebar_category)){
				if($sidebar_category = $this->getSidebarCategory(-1, $defaults['category_pages']))
					return array($defaults['category_pages'][$sidebar_category][$sidebar], 'category_pages');
			}
		}
		
		//post type list
		if(!is_category() && !is_singular() && get_post_type!='post'){
			//print_r("Post type list");
			$post_type = get_post_type();
			if(isset($defaults['post_type_pages'][$post_type]) && isset($defaults['post_type_pages'][$post_type][$sidebar]))
				return array($defaults['post_type_pages'][$post_type][$sidebar], 'post_type_pages');
		}
		
		if(is_page()){
			//print_r("Page");
			//Page sidebar
			global $post;
			$replacements = get_post_meta($post->ID, $this->postmeta_key, TRUE);
			if(is_array($replacements) && !empty($replacements[$sidebar]))
				return array($replacements[$sidebar], 'particular');
			
			//Page Post-type sidebar
			$post_type = get_post_type($post);
			if(isset($defaults['post_type_posts'][$post_type]) && isset($defaults['post_type_posts'][$post_type][$sidebar]))
				return array($defaults['post_type_posts'][$post_type][$sidebar], 'post_type_posts');
			
				
			//No custom bar
			return FALSE;
		}
		
		if(is_home()){
			//print_r("Home");
			if(empty($defaults['blog'][$sidebar]))
				return FALSE;
			else
				return array($defaults['blog'][$sidebar], 'blog');
		}
		
		if(is_tag()){
			//print_r("Tag");
			if(empty($defaults['tags'][$sidebar]))
				return FALSE;
			else
				return array($defaults['tags'][$sidebar], 'tags');
		}
		
		if(is_author()){
			//print_r("Author");
			if(empty($defaults['authors'][$sidebar]))
				return FALSE;
			else
				return array($defaults['authors'][$sidebar], 'authors');
		}
			//print_r("Esto no es nada!!!");
		
		//No custom sidebar
		return FALSE;
		
	}
	
	function getSidebarCategory($postid, $defaults_per_categories){
		$unorderedcats = get_the_category();
		$cat = FALSE;
		$catlevel = -1;
		foreach($unorderedcats as $key => $c){
			if(isset($defaults_per_categories[$c->cat_ID])){
				if(! $cat){
					$cat = $c;
					$catlevel= $this->getCategoryLevel($c->cat_ID);
					
					echo "Cat: $cat->cat_ID Level: $catlevel";
					
				}
				else{
					$level = $this->getCategoryLevel($c->cat_ID);
					if($level > $catlevel){
						$cat = $c;
						$catlevel= $level;
						
					echo "Cat: $c->cat_ID Level: $level";
					}
					else if($level == $catlevel && strcmp($cat->name, $c->name) > 0){
						$cat = $c;
						$catlevel= $level;
						
					echo "Cat: $c->cat_ID Level: $level";
					}
				}
			}
		}
		return $cat ? $cat->cat_ID : FALSE;
	}
	
	function getCategoryLevel($catid){
		if($catid == 0)
			return 0;
		
		$cat = &get_category($catid);
		return 1 + $this->getCategoryLevel($cat->category_parent);
	}
	
}
endif; //exists class


if(!isset($plugin_sidebars)){
	$plugin_sidebars = new CustomSidebars();	
	add_action( 'widgets_init', array($plugin_sidebars,'registerCustomSidebars') );
	add_action( 'widgets_admin_page', array($plugin_sidebars,'createCustomSidebar'));
	add_action( 'admin_menu', array($plugin_sidebars,'addSubMenus'));
	add_action( 'get_header', array($plugin_sidebars,'replaceSidebars'));
	add_action( 'submitpost_box', array($plugin_sidebars,'addPostMetabox'));
	add_action( 'submitpage_box', array($plugin_sidebars,'addPageMetabox'));
	add_action( 'save_post', array($plugin_sidebars,'storeReplacements'));
	add_action( 'init', array($plugin_sidebars,'loadTextDomain'));
	
}

if(! class_exists('CustomSidebarsEmptyPlugin')){
class CustomSidebarsEmptyPlugin extends WP_Widget {
	function CustomSidebarsEmptyPlugin() {
		parent::WP_Widget(false, $name = 'CustomSidebarsEmptyPlugin');
	}
	function form($instance) {
		//Nothing, just a dummy plugin to display nothing
	}
	function update($new_instance, $old_instance) {
		//Nothing, just a dummy plugin to display nothing
	}
	function widget($args, $instance) {		
		echo '';
	}
} //end class
} //end if class exists