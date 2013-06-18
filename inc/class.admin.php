<?php
//avoid direct calls to this file
if ( ! function_exists( 'add_filter' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class WPGitPlugin_Admin extends WPGitPlugin {
	
	// Plugin instance
	protected static $instance = NULL;
    
	public function __construct() {
		if ( ! is_admin() )
			return NULL;
		
		add_action( 'admin_menu' , array( $this, 'remove_taxonomy_boxes') );
		add_action( 'add_meta_boxes', array( $this, 'properties_meta_boxes') );
		add_filter( 'plugin_row_meta', array( $this, 'set_plugin_meta' ), 10, 2 );
	}
    
	// Access this plugin’s working instance
	public static function get_instance() {
		if ( NULL === self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
	
	function remove_taxonomy_boxes() {  
		remove_meta_box( 'plugin-categorydiv', 'plugin', 'normal');
		remove_meta_box( 'theme-categorydiv', 'theme', 'normal'); 
	}
	
	// Add the Meta Boxes  
	function properties_meta_boxes() {  
		add_meta_box(  
			'plugin_properties', // $id  
			__('Plugin Properties', WPGIT_NAME), // $title   
			array( $this, 'plugin_meta_box'), // $callback
			'plugin', // $page  
			'normal', // $context  
			'high' // $priority
		);  

		add_meta_box(  
			'theme_properties', // $id  
			__('Theme Properties', WPGIT_NAME), // $title   
			array( $this, 'theme_meta_box'), // $callback
			'theme', // $page  
			'normal', // $context  
			'high' // $priority
		);  

		add_meta_box(  
			'mirror_properties', // $id  
			__('Mirror Properties', WPGIT_NAME), // $title   
			array( $this, 'mirror_meta_box'), // $callback
			'mirror', // $page  
			'normal', // $context  
			'high' // $priority
		);

		add_meta_box(  
			'project_properties', // $id  
			__('Project Properties', WPGIT_NAME), // $title   
			array( $this, 'project_meta_box'), // $callback
			'project', // $page  
			'normal', // $context  
			'high' // $priority
		);  
	}
	
	function set_plugin_meta( $links, $file ) {	
		if ( $file == plugin_basename( __FILE__ ) ) {
			return array_merge(
				$links,
				array( '<a href="https://github.com/wp-repository/wp-git-plugin" target="_blank">GitHub</a>' )
			);
		}
		return $links;
	}

} // END class WPGitPlugin_Admin

$wpgitplugin_admin = WPGitPlugin_Admin::get_instance();