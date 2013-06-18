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
			'name' => __('Plugins', WPGIT_NAME),
			'singular_name' => __('Plugin', WPGIT_NAME),
			'add_new' => __('Add New', WPGIT_NAME),
			'add_new_item' => __('Add New Plugin', WPGIT_NAME),
			'edit_item' => __('Edit Plugin', WPGIT_NAME),
			'new_item' => __('New Plugin', WPGIT_NAME),
			'view_item' => __('View Plugin', WPGIT_NAME),
			'search_items' => __('Search Plugins', WPGIT_NAME),
			'not_found' =>  __('No Plugins found', WPGIT_NAME),
			'not_found_in_trash' => __('No Plugins in the trash', WPGIT_NAME),
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
			'name'                => _x('Categories', 'taxonomy general name', WPGIT_NAME),
			'singular_name'       => _x('Category', 'taxonomy singular name', WPGIT_NAME),
			'search_items'        => __('Search Categories', WPGIT_NAME),
			'all_items'           => __('All Categories', WPGIT_NAME),
			'parent_item'         => __('Parent Category', WPGIT_NAME),
			'parent_item_colon'   => __('Parent Category:', WPGIT_NAME),
			'edit_item'           => __('Edit Category', WPGIT_NAME), 
			'update_item'         => __('Update Category', WPGIT_NAME),
			'add_new_item'        => __('Add New Category', WPGIT_NAME),
			'new_item_name'       => __('New Category Name', WPGIT_NAME),
			'menu_name'           => __('Categories', WPGIT_NAME)
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
			'name' => __('Themes', WPGIT_NAME),
			'singular_name' => __('Theme', WPGIT_NAME),
			'add_new' => __('Add New', WPGIT_NAME),
			'add_new_item' => __('Add New Theme', WPGIT_NAME),
			'edit_item' => __('Edit Theme', WPGIT_NAME),
			'new_item' => __('New Theme', WPGIT_NAME),
			'view_item' => __('View Theme', WPGIT_NAME),
			'search_items' => __('Search Themes', WPGIT_NAME),
			'not_found' =>  __('No Themes found', WPGIT_NAME),
			'not_found_in_trash' => __('No Themes in the trash', WPGIT_NAME),
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
			'name'                => _x('Categories', 'taxonomy general name', WPGIT_NAME),
			'singular_name'       => _x('Category', 'taxonomy singular name', WPGIT_NAME),
			'search_items'        => __('Search Categories', WPGIT_NAME),
			'all_items'           => __('All Categories', WPGIT_NAME),
			'parent_item'         => __('Parent Category', WPGIT_NAME),
			'parent_item_colon'   => __('Parent Category:', WPGIT_NAME),
			'edit_item'           => __('Edit Category', WPGIT_NAME), 
			'update_item'         => __('Update Category', WPGIT_NAME),
			'add_new_item'        => __('Add New Category', WPGIT_NAME),
			'new_item_name'       => __('New Category Name', WPGIT_NAME),
			'menu_name'           => __('Categories', WPGIT_NAME)
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
			'name' => __('Mirrors', WPGIT_NAME),
			'singular_name' => __('Mirror', WPGIT_NAME),
			'add_new' => __('Add New', WPGIT_NAME),
			'add_new_item' => __('Add New Mirror', WPGIT_NAME),
			'edit_item' => __('Edit Mirror', WPGIT_NAME),
			'new_item' => __('New Mirror', WPGIT_NAME),
			'view_item' => __('View Mirror', WPGIT_NAME),
			'search_items' => __('Search Mirrors', WPGIT_NAME),
			'not_found' =>  __('No Mirrors found', WPGIT_NAME),
			'not_found_in_trash' => __('No Mirrors in the trash', WPGIT_NAME),
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
			'name'                => _x('Categories', 'taxonomy general name', WPGIT_NAME),
			'singular_name'       => _x('Category', 'taxonomy singular name', WPGIT_NAME),
			'search_items'        => __('Search Categories', WPGIT_NAME),
			'all_items'           => __('All Categories', WPGIT_NAME),
			'parent_item'         => __('Parent Category', WPGIT_NAME),
			'parent_item_colon'   => __('Parent Category:', WPGIT_NAME),
			'edit_item'           => __('Edit Category', WPGIT_NAME), 
			'update_item'         => __('Update Category', WPGIT_NAME),
			'add_new_item'        => __('Add New Category', WPGIT_NAME),
			'new_item_name'       => __('New Category Name', WPGIT_NAME),
			'menu_name'           => __('Categories', WPGIT_NAME)
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
			'name' => __('Projects', WPGIT_NAME),
			'singular_name' => __('Project', WPGIT_NAME),
			'add_new' => __('Add New', WPGIT_NAME),
			'add_new_item' => __('Add New Project', WPGIT_NAME),
			'edit_item' => __('Edit Project', WPGIT_NAME),
			'new_item' => __('New Project', WPGIT_NAME),
			'view_item' => __('View Project', WPGIT_NAME),
			'search_items' => __('Search Projects', WPGIT_NAME),
			'not_found' =>  __('No Projects found', WPGIT_NAME),
			'not_found_in_trash' => __('No Projects in the trash', WPGIT_NAME),
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
			'name'                => _x('Categories', 'taxonomy general name', WPGIT_NAME),
			'singular_name'       => _x('Category', 'taxonomy singular name', WPGIT_NAME),
			'search_items'        => __('Search Categories', WPGIT_NAME),
			'all_items'           => __('All Categories', WPGIT_NAME),
			'parent_item'         => __('Parent Category', WPGIT_NAME),
			'parent_item_colon'   => __('Parent Category:', WPGIT_NAME),
			'edit_item'           => __('Edit Category', WPGIT_NAME), 
			'update_item'         => __('Update Category', WPGIT_NAME),
			'add_new_item'        => __('Add New Category', WPGIT_NAME),
			'new_item_name'       => __('New Category Name', WPGIT_NAME),
			'menu_name'           => __('Categories', WPGIT_NAME)
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