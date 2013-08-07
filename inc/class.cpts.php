<?php
//avoid direct calls to this file
if ( ! function_exists( 'add_filter' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class WPGitPlugin_CPTs extends WPGitPlugin {
	
	// Plugin instance
	protected static $instance = NULL;
    
	public function __construct() {
		add_action( 'init', array( $this, 'init_cpts' ) );
	}
    
	// Access this plugin’s working instance
	public static function get_instance() {
		if ( NULL === self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
	
	function init_cpts() {
		// set lables for plugins cpts
		$plugin_labels = array(
			'name' => __(' Plugins', 'wp-git-plugins'),
			'singular_name' => __(' Plugin', 'wp-git-plugins'),
			'add_new' => __(' Add New', 'wp-git-plugins'),
			'add_new_item' => __(' Add New Plugin', 'wp-git-plugins'),
			'edit_item' => __(' Edit Plugin', 'wp-git-plugins'),
			'new_item' => __(' New Plugin', 'wp-git-plugins'),
			'view_item' => __(' View Plugin', 'wp-git-plugins'),
			'search_items' => __(' Search Plugins', 'wp-git-plugins'),
			'not_found' =>  __(' No Plugins found', 'wp-git-plugins'),
			'not_found_in_trash' => __(' No Plugins in the trash', 'wp-git-plugins'),
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
			'name'                => _x('Categories', 'taxonomy general name', 'wp-git-plugins'),
			'singular_name'       => _x('Category', 'taxonomy singular name', 'wp-git-plugins'),
			'search_items'        => __(' Search Categories', 'wp-git-plugins'),
			'all_items'           => __(' All Categories', 'wp-git-plugins'),
			'parent_item'         => __(' Parent Category', 'wp-git-plugins'),
			'parent_item_colon'   => __(' Parent Category:', 'wp-git-plugins'),
			'edit_item'           => __(' Edit Category', 'wp-git-plugins'), 
			'update_item'         => __(' Update Category', 'wp-git-plugins'),
			'add_new_item'        => __(' Add New Category', 'wp-git-plugins'),
			'new_item_name'       => __(' New Category Name', 'wp-git-plugins'),
			'menu_name'           => __(' Categories', 'wp-git-plugins')
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
			'name' => __(' Themes', 'wp-git-plugins'),
			'singular_name' => __(' Theme', 'wp-git-plugins'),
			'add_new' => __(' Add New', 'wp-git-plugins'),
			'add_new_item' => __(' Add New Theme', 'wp-git-plugins'),
			'edit_item' => __(' Edit Theme', 'wp-git-plugins'),
			'new_item' => __(' New Theme', 'wp-git-plugins'),
			'view_item' => __(' View Theme', 'wp-git-plugins'),
			'search_items' => __(' Search Themes', 'wp-git-plugins'),
			'not_found' =>  __(' No Themes found', 'wp-git-plugins'),
			'not_found_in_trash' => __(' No Themes in the trash', 'wp-git-plugins'),
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
			'name'                => _x('Categories', 'taxonomy general name', 'wp-git-plugins'),
			'singular_name'       => _x('Category', 'taxonomy singular name', 'wp-git-plugins'),
			'search_items'        => __(' Search Categories', 'wp-git-plugins'),
			'all_items'           => __(' All Categories', 'wp-git-plugins'),
			'parent_item'         => __(' Parent Category', 'wp-git-plugins'),
			'parent_item_colon'   => __(' Parent Category:', 'wp-git-plugins'),
			'edit_item'           => __(' Edit Category', 'wp-git-plugins'), 
			'update_item'         => __(' Update Category', 'wp-git-plugins'),
			'add_new_item'        => __(' Add New Category', 'wp-git-plugins'),
			'new_item_name'       => __(' New Category Name', 'wp-git-plugins'),
			'menu_name'           => __(' Categories', 'wp-git-plugins')
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
			'name' => __(' Mirrors', 'wp-git-plugins'),
			'singular_name' => __(' Mirror', 'wp-git-plugins'),
			'add_new' => __(' Add New', 'wp-git-plugins'),
			'add_new_item' => __(' Add New Mirror', 'wp-git-plugins'),
			'edit_item' => __(' Edit Mirror', 'wp-git-plugins'),
			'new_item' => __(' New Mirror', 'wp-git-plugins'),
			'view_item' => __(' View Mirror', 'wp-git-plugins'),
			'search_items' => __(' Search Mirrors', 'wp-git-plugins'),
			'not_found' =>  __(' No Mirrors found', 'wp-git-plugins'),
			'not_found_in_trash' => __(' No Mirrors in the trash', 'wp-git-plugins'),
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
			'name'                => _x('Categories', 'taxonomy general name', 'wp-git-plugins'),
			'singular_name'       => _x('Category', 'taxonomy singular name', 'wp-git-plugins'),
			'search_items'        => __(' Search Categories', 'wp-git-plugins'),
			'all_items'           => __(' All Categories', 'wp-git-plugins'),
			'parent_item'         => __(' Parent Category', 'wp-git-plugins'),
			'parent_item_colon'   => __(' Parent Category:', 'wp-git-plugins'),
			'edit_item'           => __(' Edit Category', 'wp-git-plugins'), 
			'update_item'         => __(' Update Category', 'wp-git-plugins'),
			'add_new_item'        => __(' Add New Category', 'wp-git-plugins'),
			'new_item_name'       => __(' New Category Name', 'wp-git-plugins'),
			'menu_name'           => __(' Categories', 'wp-git-plugins')
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
			'name' => __(' Projects', 'wp-git-plugins'),
			'singular_name' => __(' Project', 'wp-git-plugins'),
			'add_new' => __(' Add New', 'wp-git-plugins'),
			'add_new_item' => __(' Add New Project', 'wp-git-plugins'),
			'edit_item' => __(' Edit Project', 'wp-git-plugins'),
			'new_item' => __(' New Project', 'wp-git-plugins'),
			'view_item' => __(' View Project', 'wp-git-plugins'),
			'search_items' => __(' Search Projects', 'wp-git-plugins'),
			'not_found' =>  __(' No Projects found', 'wp-git-plugins'),
			'not_found_in_trash' => __(' No Projects in the trash', 'wp-git-plugins'),
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
			'name'                => _x('Categories', 'taxonomy general name', 'wp-git-plugins'),
			'singular_name'       => _x('Category', 'taxonomy singular name', 'wp-git-plugins'),
			'search_items'        => __(' Search Categories', 'wp-git-plugins'),
			'all_items'           => __(' All Categories', 'wp-git-plugins'),
			'parent_item'         => __(' Parent Category', 'wp-git-plugins'),
			'parent_item_colon'   => __(' Parent Category:', 'wp-git-plugins'),
			'edit_item'           => __(' Edit Category', 'wp-git-plugins'), 
			'update_item'         => __(' Update Category', 'wp-git-plugins'),
			'add_new_item'        => __(' Add New Category', 'wp-git-plugins'),
			'new_item_name'       => __(' New Category Name', 'wp-git-plugins'),
			'menu_name'           => __(' Categories', 'wp-git-plugins')
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
	
} // END class WPGitPlugin_CPTs

$wpgitplugin_cpts = WPGitPlugin_CPTs::get_instance();