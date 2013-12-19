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

class WPGit_CPTs  {
	
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
		
		add_action( 'init', array( $this, 'init_cpts' ) );
		
	} // END __construct()
	
	function init_cpts() {
		// set lables for plugins cpts
		$plugin_labels = array(
			'name' => __(' Plugins', 'wp-git-plugin'),
			'singular_name' => __(' Plugin', 'wp-git-plugin'),
			'add_new' => __(' Add New', 'wp-git-plugin'),
			'add_new_item' => __(' Add New Plugin', 'wp-git-plugin'),
			'edit_item' => __(' Edit Plugin', 'wp-git-plugin'),
			'new_item' => __(' New Plugin', 'wp-git-plugin'),
			'view_item' => __(' View Plugin', 'wp-git-plugin'),
			'search_items' => __(' Search Plugins', 'wp-git-plugin'),
			'not_found' =>  __(' No Plugins found', 'wp-git-plugin'),
			'not_found_in_trash' => __(' No Plugins in the trash', 'wp-git-plugin'),
			'parent_item_colon' => '',
		);

		// add the plugins cpt itself
		register_post_type(
			'plugin', 
			array(
				'labels' => $plugin_labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'exclude_from_search' => false,
				'query_var' => true,
				'rewrite' => true, // check
				'capability_type' => 'post',
				//'capabilities' => '';
				'has_archive' => true,
				'hierarchical' => false,
				'menu_position' => 10,
				'supports' => array( 'title', 'thumbnail' ),
				// 'register_meta_box_cb' => 'testimonials_meta_boxes',
			)
		);

		// attach hierarchical taxonomy to plugin cpt
		$plugin_tax_labels = array(
			'name'                => _x('Categories', 'taxonomy general name', 'wp-git-plugin'),
			'singular_name'       => _x('Category', 'taxonomy singular name', 'wp-git-plugin'),
			'search_items'        => __(' Search Categories', 'wp-git-plugin'),
			'all_items'           => __(' All Categories', 'wp-git-plugin'),
			'parent_item'         => __(' Parent Category', 'wp-git-plugin'),
			'parent_item_colon'   => __(' Parent Category:', 'wp-git-plugin'),
			'edit_item'           => __(' Edit Category', 'wp-git-plugin'), 
			'update_item'         => __(' Update Category', 'wp-git-plugin'),
			'add_new_item'        => __(' Add New Category', 'wp-git-plugin'),
			'new_item_name'       => __(' New Category Name', 'wp-git-plugin'),
			'menu_name'           => __(' Categories', 'wp-git-plugin')
		); 	

		$plugin_tax_args = array(
			'hierarchical'        => true,
			'labels'              => $plugin_tax_labels,
			'show_ui'             => true,
			'show_admin_column'   => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'plugins' )
		);

		register_taxonomy( 'plugin_category', array( 'plugin' ), $plugin_tax_args );
	
		// set lables for themes cpts
		$themes_labels = array(
			'name' => __(' Themes', 'wp-git-plugin'),
			'singular_name' => __(' Theme', 'wp-git-plugin'),
			'add_new' => __(' Add New', 'wp-git-plugin'),
			'add_new_item' => __(' Add New Theme', 'wp-git-plugin'),
			'edit_item' => __(' Edit Theme', 'wp-git-plugin'),
			'new_item' => __(' New Theme', 'wp-git-plugin'),
			'view_item' => __(' View Theme', 'wp-git-plugin'),
			'search_items' => __(' Search Themes', 'wp-git-plugin'),
			'not_found' =>  __(' No Themes found', 'wp-git-plugin'),
			'not_found_in_trash' => __(' No Themes in the trash', 'wp-git-plugin'),
			'parent_item_colon' => '',
		);

		// add the themes cpt itself
		register_post_type(
			'theme', 
			array(
				'labels' => $themes_labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'exclude_from_search' => false,
				'query_var' => true,
				'rewrite' => true, // check
				'capability_type' => 'post',
				//'capabilities' => '';
				'has_archive' => true,
				'hierarchical' => false,
				'menu_position' => 10,
				'supports' => array( 'title', 'thumbnail' ),
				// 'register_meta_box_cb' => 'testimonials_meta_boxes',
			)
		);
            
		// attach hierarchical taxonomy to theme cpt
		$theme_tax_labels = array(
			'name'                => _x('Categories', 'taxonomy general name', 'wp-git-plugin'),
			'singular_name'       => _x('Category', 'taxonomy singular name', 'wp-git-plugin'),
			'search_items'        => __(' Search Categories', 'wp-git-plugin'),
			'all_items'           => __(' All Categories', 'wp-git-plugin'),
			'parent_item'         => __(' Parent Category', 'wp-git-plugin'),
			'parent_item_colon'   => __(' Parent Category:', 'wp-git-plugin'),
			'edit_item'           => __(' Edit Category', 'wp-git-plugin'), 
			'update_item'         => __(' Update Category', 'wp-git-plugin'),
			'add_new_item'        => __(' Add New Category', 'wp-git-plugin'),
			'new_item_name'       => __(' New Category Name', 'wp-git-plugin'),
			'menu_name'           => __(' Categories', 'wp-git-plugin')
		);  	

		$theme_tax_args = array(
		  'hierarchical'        => true,
		  'labels'              => $theme_tax_labels,
		  'show_ui'             => true,
		  'show_admin_column'   => true,
		  'query_var'           => true,
		  'rewrite'             => array( 'slug' => 'themes' )
		);

		register_taxonomy( 'theme_category', array( 'theme' ), $theme_tax_args );

		// set lables for themes cpts
		$mirror_labels = array(
			'name' => __(' Mirrors', 'wp-git-plugin'),
			'singular_name' => __(' Mirror', 'wp-git-plugin'),
			'add_new' => __(' Add New', 'wp-git-plugin'),
			'add_new_item' => __(' Add New Mirror', 'wp-git-plugin'),
			'edit_item' => __(' Edit Mirror', 'wp-git-plugin'),
			'new_item' => __(' New Mirror', 'wp-git-plugin'),
			'view_item' => __(' View Mirror', 'wp-git-plugin'),
			'search_items' => __(' Search Mirrors', 'wp-git-plugin'),
			'not_found' =>  __(' No Mirrors found', 'wp-git-plugin'),
			'not_found_in_trash' => __(' No Mirrors in the trash', 'wp-git-plugin'),
			'parent_item_colon' => '',
		);

		// add the themes cpt itself
		register_post_type(
			'mirror', 
			array(
				'labels' => $mirror_labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'exclude_from_search' => false,
				'query_var' => true,
				'rewrite' => true, // check
				'capability_type' => 'post',
				//'capabilities' => '';
				'has_archive' => true,
				'hierarchical' => false,
				'menu_position' => 10,
				'supports' => array( 'title' ),
				// 'register_meta_box_cb' => 'testimonials_meta_boxes',
			)
		);
            
		// attach hierarchical taxonomy to theme cpt
		$mirror_tax_labels = array(
			'name'                => _x('Categories', 'taxonomy general name', 'wp-git-plugin'),
			'singular_name'       => _x('Category', 'taxonomy singular name', 'wp-git-plugin'),
			'search_items'        => __(' Search Categories', 'wp-git-plugin'),
			'all_items'           => __(' All Categories', 'wp-git-plugin'),
			'parent_item'         => __(' Parent Category', 'wp-git-plugin'),
			'parent_item_colon'   => __(' Parent Category:', 'wp-git-plugin'),
			'edit_item'           => __(' Edit Category', 'wp-git-plugin'), 
			'update_item'         => __(' Update Category', 'wp-git-plugin'),
			'add_new_item'        => __(' Add New Category', 'wp-git-plugin'),
			'new_item_name'       => __(' New Category Name', 'wp-git-plugin'),
			'menu_name'           => __(' Categories', 'wp-git-plugin')
		);  	

		$mirror_tax_args = array(
		  'hierarchical'        => true,
		  'labels'              => $mirror_tax_labels,
		  'show_ui'             => true,
		  'show_admin_column'   => true,
		  'query_var'           => true,
		  'rewrite'             => array( 'slug' => 'mirrors' )
		);

		// set lables for themes cpts
		$project_labels = array(
			'name' => __(' Projects', 'wp-git-plugin'),
			'singular_name' => __(' Project', 'wp-git-plugin'),
			'add_new' => __(' Add New', 'wp-git-plugin'),
			'add_new_item' => __(' Add New Project', 'wp-git-plugin'),
			'edit_item' => __(' Edit Project', 'wp-git-plugin'),
			'new_item' => __(' New Project', 'wp-git-plugin'),
			'view_item' => __(' View Project', 'wp-git-plugin'),
			'search_items' => __(' Search Projects', 'wp-git-plugin'),
			'not_found' =>  __(' No Projects found', 'wp-git-plugin'),
			'not_found_in_trash' => __(' No Projects in the trash', 'wp-git-plugin'),
			'parent_item_colon' => '',
		);

		// add the themes cpt itself
		register_post_type(
			'project', 
			array(
				'labels' => $project_labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'exclude_from_search' => false,
				'query_var' => true,
				'rewrite' => true, // check
				'capability_type' => 'post',
				//'capabilities' => '';
				'has_archive' => true,
				'hierarchical' => false,
				'menu_position' => 10,
				'supports' => array( 'title' ),
				// 'register_meta_box_cb' => 'testimonials_meta_boxes',
			)
		);
            
		// attach hierarchical taxonomy to theme cpt
		$project_tax_labels = array(
			'name'                => _x('Categories', 'taxonomy general name', 'wp-git-plugin'),
			'singular_name'       => _x('Category', 'taxonomy singular name', 'wp-git-plugin'),
			'search_items'        => __(' Search Categories', 'wp-git-plugin'),
			'all_items'           => __(' All Categories', 'wp-git-plugin'),
			'parent_item'         => __(' Parent Category', 'wp-git-plugin'),
			'parent_item_colon'   => __(' Parent Category:', 'wp-git-plugin'),
			'edit_item'           => __(' Edit Category', 'wp-git-plugin'), 
			'update_item'         => __(' Update Category', 'wp-git-plugin'),
			'add_new_item'        => __(' Add New Category', 'wp-git-plugin'),
			'new_item_name'       => __(' New Category Name', 'wp-git-plugin'),
			'menu_name'           => __(' Categories', 'wp-git-plugin')
		);  	

		$project_tax_args = array(
		  'hierarchical'        => true,
		  'labels'              => $project_tax_labels,
		  'show_ui'             => true,
		  'show_admin_column'   => true,
		  'query_var'           => true,
		  'rewrite'             => array( 'slug' => 'projects' )
		);
	}
	
} // END class WPGit_CPTs
