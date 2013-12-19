<?php
/**
 * @author WP-Cloud <code@wp-cloud.de>
 * @license GPLv2 <http://www.gnu.org/licenses/gpl-2.0.html>
 * @package WP-Git Plugin
 */

//avoid direct calls to this file
if ( !function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class WPGit_Admin {
	
	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		return self::$instance;

	} // END get_instance()

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		
		self::$instance = $this;
		
		add_action( 'admin_menu' , array( $this, 'remove_taxonomy_boxes') );
		add_action( 'add_meta_boxes', array( $this, 'properties_meta_boxes') );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		
	}
	
	function remove_taxonomy_boxes() {  
		remove_meta_box( 'plugin-categorydiv', 'plugin', 'normal');
		remove_meta_box( 'theme-categorydiv', 'theme', 'normal'); 
	}
	
	// Add the Meta Boxes  
	function properties_meta_boxes() {  
		add_meta_box(  
			'plugin_properties', // $id  
			__(' Plugin Properties', 'wp-git-plugin'), // $title   
			array( $this, 'plugin_meta_box'), // $callback
			'plugin', // $page  
			'normal', // $context  
			'high' // $priority
		);  

		add_meta_box(  
			'theme_properties', // $id  
			__(' Theme Properties', 'wp-git-plugin'), // $title   
			array( $this, 'theme_meta_box'), // $callback
			'theme', // $page  
			'normal', // $context  
			'high' // $priority
		);  

		add_meta_box(  
			'mirror_properties', // $id  
			__(' Mirror Properties', 'wp-git-plugin'), // $title   
			array( $this, 'mirror_meta_box'), // $callback
			'mirror', // $page  
			'normal', // $context  
			'high' // $priority
		);

		add_meta_box(  
			'project_properties', // $id  
			__(' Project Properties', 'wp-git-plugin'), // $title   
			array( $this, 'project_meta_box'), // $callback
			'project', // $page  
			'normal', // $context  
			'high' // $priority
		);  
	}
	
	/**
	 * @TODO
	 *
	 * @since 1.0.0
	 */	
	function plugin_row_meta( $links, $file ) {
		
		if ( $file == plugin_basename( __FILE__ ) ) {
			return array_merge(
				$links,
				array( '<a href="https://github.com/wp-repository/wp-git-plugin" target="_blank">GitHub</a>' )
			);
		}
		return $links;
		
	} // END plugin_row_meta()

} // END class WPGit_Admin
