<?php
/*
Plugin Name: Custom sidebars
Plugin URI: http://marquex.posterous.com/pages/custom-sidebars
Description: Allows to create your own widgetized areas and sidebars, and select what sidebars to use for each post or page.
Version: 0.1
Author: Javier Marquez (marquex@gmail.com)
Author URI: http://marquex.mp
*/

if(!class_exists('CustomSidebars')):

class CustomSidebars{
	var $message = '';
	var $message_class = '';
	var $option_name = "cs_sidebars";
	var $option_modifiable = "cs_modifiable";
	var $sidebar_prefix = 'cs-';
	var $postmeta_key = '_cs_replacements';
	var $cap_required = 'edit_themes';
	var $ignore_post_types = array('attachment', 'revision', 'nav_menu_item');
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
		$sidebars = get_option('sidebars_widgets');
		$themesidebars = array();
		if($sidebars){
			foreach(array_keys($sidebars) as $sb){
				if(array_search($sb, array('wp_inactive_widgets', 'array_version')) === FALSE){
					if($include_custom_sidebars OR substr($sb, 0, 3) != $this->sidebar_prefix)
						$themesidebars[] = $sb;
				}
			}
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
		
		if(!is_single() && !is_page())
			return;
		
		global $_wp_sidebars_widgets, $post, $wp_registered_sidebars;
		$replacements = $this->getReplacements($post->ID);
		$post_type = get_post_type($post);
		
		//defult post type replacements
		$default_replacements = $this->getDefaultReplacements();
		if(isset($default_replacements[$post_type]))
			$default_replacements = $default_replacements[$post_type];
		else
			$default_replacements = array();
		
		$updated = FALSE;
		$modifiable = $this->getModifiableSidebars();
		if(!empty($modifiable)){
		//Here, where the magic happens 
		foreach($modifiable as $sb){
			//specific post sidebars
			if(isset($replacements[$sb])){
				if(array_search($replacements[$sb], array_keys($wp_registered_sidebars)) !== FALSE)
					$_wp_sidebars_widgets[$sb] = $_wp_sidebars_widgets[$replacements[$sb]];
				else{
					unset($replacements[$sb]);
					$updated = TRUE;
				}
			}
			//otherwise post type sidebars
			else if(isset($default_replacements[$sb])){
				if(array_search($default_replacements[$sb], array_keys($wp_registered_sidebars)) !== FALSE)
					$_wp_sidebars_widgets[$sb] = $_wp_sidebars_widgets[$default_replacements[$sb]];
				else{
					unset($default_replacements[$sb]);
					$updated = TRUE;
				}
			}
		}
		}//endif modifiable
		
		//If the replacements were not correct, we will update to fix them
		if($updated){
			if(empty($replacements))
				delete_post_meta($post->ID, $this->postmeta_key);
			else
				update_post_meta($post_>ID, $this->postmeta_key);
			
			
			$options = $this->options;//get_option($this->option_modifiable);
			if(isset($options['defaults'][$post_type])){
				if(empty($default_replacements))
					unset($options['defaults'][$post_type]);
				else
					$options['defaults'][$post_type] = $default_replacements;
			}
			update_option($this->option_modifiable, $options);
		}
	}
	
	function deleteSidebar(){
		if(! current_user_can($this->cap_required) )
			return new WP_Error('cscantdelete', __('You do not have permission to delete sidebars','custom-sidebars'));
		
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
		if($deleted)
			$this->setMessage(sprintf(__('The sidebar "%s" has been deleted.','custom-sidebars'), $_GET['delete']));
		else
			$this->setError(sprintf(__('There was not any sidebar called "%s" and it could not been deleted.','custom-sidebars'), $_GET['delete']));
	}
	
	function createPage(){
		if(!empty($_POST)){
			if(isset($_POST['create-sidebars']))
				$this->storeSidebar();
			else if(isset($_POST['update-modifiable']))
				$this->updateModifiable();
				
			$this->retrieveOptions();
		}
		else if(!empty($_GET['delete'])){
			$this->deleteSidebar();
			$this->retrieveOptions();			
		}
		
		$customsidebars = $this->getCustomSidebars();
		$themesidebars = $this->getThemeSidebars();
		$allsidebars = $this->getThemeSidebars(TRUE);
		$defaults = $this->getDefaultReplacements();
		$modifiable = $this->getModifiableSidebars();
		$post_types = $this->getPostTypes();
		
		//Form			
		include('view.php');		
	}
	
	function addSubMenus(){
		$page = add_submenu_page('themes.php', __('Custom sidebars','custom-sidebars'), __('Custom sidebars','custom-sidebars'), 'edit_themes', 'customsidebars', array($this, 'createPage'));
		
        add_action('admin_print_scripts-' . $page, array($this, 'addScripts'));
	}
	
	function addScripts(){
		wp_enqueue_script('post');
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
		global $post;
		
		$replacements = $this->getReplacements($post->ID);
			
		$available = array_merge(array(''), $this->getThemeSidebars(TRUE));
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
	
	private function getReplacements($postid){
		$replacements = get_post_meta($postid, $this->postmeta_key, TRUE);
		if($replacements == '')
			$replacements = array();
		else
			$replacements = $replacements;
		return $replacements;
	}
	
	private function getModifiableSidebars(){
		if( $modifiable = $this->options ); //get_option($this->option_modifiable) )
			return $modifiable['modifiable'];
		return array(); 
	}
	
	private function getDefaultReplacements(){
		if( $modifiable = $this->options ); //get_option($this->option_modifiable) )
			return $modifiable['defaults'];
		return array(); 
	}
	
	private function updateModifiable(){
		$options = array();
		
		//Modifiable bars
		if(isset($_POST['modifiable']) && is_array($_POST['modifiable']))
			$options['modifiable'] = $_POST['modifiable'];
			
		//Default bars
		$options['defaults'] = array();
		foreach($this->getPostTypes() as $pt){
			$modifiable = $this->getModifiableSidebars();
			if(!empty($modifiable)){
				foreach($this->getModifiableSidebars() as $m){
					if(isset($_POST["ptdefault-$pt-$m"]) && $_POST["ptdefault-$pt-$m"]!=''){
						if(! isset($options['defaults'][$pt]))
							$options['defaults'][$pt] = array();
						
						$options['defaults'][$pt][$m] = $_POST["ptdefault-$pt-$m"];
					}
				}
			}
		}
			
		if($this->options !== FALSE)
			update_option($this->option_modifiable, $options);
		else
			add_option($this->option_modifiable, $options);
			
		$this->setMessage(__('The custom sidebars settings has been updated successfully.','custom-sidebars'));
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
	
	private function storeSidebar(){
		$name = trim($_POST['sidebar_name']);
		$description = trim($_POST['sidebar_description']);
		if(empty($name) OR empty($description))
			$this->setError(__('You have to fill all the fields to create a new sidebar.','custom-sidebars'));
		else{
			$id = $this->sidebar_prefix . sanitize_title_with_dashes($name);
			$sidebars = get_option($this->option_name);
			if($sidebars){
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
				$this->setMessage( __('The sidebar has been created successfully.','custom-sidebars'));					
			}
		}
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
	
	private function setMessage($text){
		$this->message = $text;
		$this->message_class = 'updated';
	}
	
	private function setError($text){
		$this->message = $text;
		$this->message_class = 'error';
	}
	
	private function getPostTypes(){
		$pt = get_post_types();
		$ptok = array();
		
		foreach($pt as $t){
			if(array_search($t, $this->ignore_post_types) === FALSE)
				$ptok[] = $t;
		}
		
		return $ptok; 
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