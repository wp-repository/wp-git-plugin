<?php
/*
Plugin Name: WP-Git Plugin
Plugin URI: https://github.com/wp-repository/wp-git-plugin
Description: Manage SVN2Git mirror sync and other Git-related services for WP-Repository.org
Version: 0.1-dev
Author: Foe Services
Author URI: http://labs.foe-services.de
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-git-plugins
Domain Path: /languages
    
    WP-Git Plugin
    
    Copyright (C) 2013 Foe Services (http://labs.foe-services.de)

    This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>. 
*/

//avoid direct calls to this file
if ( ! function_exists( 'add_filter' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

if ( ! class_exists('WPGitPlugin') ) {
	
	// =============
	// Plugin name & textdomain 
	define( 'WPGIT_NAME', 'wp-git-plugin' );
	// =============
	// Database version
	// NEEDED?  define( 'WPGIT_VERSION', '0.1-dev' );
	// =============
	// Database version
	// NEEDED?  define( 'WPGIT_DB_VERSION', 1 );
	// =============
	// Plugin basename
	define( 'WPGIT_BASENAME', plugin_basename( __FILE__ ) );
	// =============
	// Plugin basedir/path
	define( 'WPGIT_PATH', dirname( __FILE__ ) );
	// =============
	// Images path -> CDN
	define( 'WPGIT_IMAGES_PATH', WPGIT_PATH . '/images' ); 
	// =============
	// Plugin URL
	define( 'WPGIT_URL', plugins_url( '', __FILE__ ) );
		// =============
	// Images URL -> CDN
	define( 'WPGIT_IMAGES_URL', WPGIT_URL . '/images' );
	// =============

	add_action(
		'plugins_loaded', 
		array ( 'WPGitPlugin', 'get_instance' )
	);

	class WPGitPlugin {
		
		// Plugin instance
		protected static $instance = NULL;

        public function __construct() {
			
			$this->load_classes();
            $this->init();

            if ( !is_admin() ) {
                // frontend
                $this->frontend_init();
            }

            if ( is_admin() ) {
                // wp-admin
                $this->admin_init();
                if ( is_network_admin() ) {
                    // wp-admin/network
                    $this->network_admin_init();     
                }
            }
            register_activation_hook( __FILE__, array( 'WPGitPlugin', 'activation') );
            register_deactivation_hook( __FILE__, array( 'WPGitPlugin', 'deactivation') );
        }
		
		// Access this plugin’s working instance
		public static function get_instance() {	
			if ( NULL === self::$instance )
				self::$instance = new self;

			return self::$instance;
		}

		// load classes from INC path
		protected function load_classes() {
			foreach( glob( WPGIT_PATH . '/inc/class.*.php' ) as $class ) {
				require_once $class;
			}
		}

        protected function init() {
            add_action( 'init', array( $this, 'add_plugin_cpt'  ) );
            add_action( 'init', array( $this, 'add_theme_cpt'   ) );
            add_action( 'init', array( $this, 'add_mirror_cpt'  ) );
            add_action( 'init', array( $this, 'add_project_cpt' ) );
        }

        protected function frontend_init() {

        }

        protected function admin_init() {
            add_action( 'add_meta_boxes', array( $this, 'properties_meta_boxes') ); 
            add_action( 'save_post', array( $this, 'save_meta_data') );
            add_action( 'admin_menu' , array( $this, 'remove_taxonomy_boxes') );
            add_filter( 'plugin_row_meta', array( $this, 'set_plugin_meta' ), 10, 2 );
            
        }

        protected function network_admin_init() {

        }
        
        function remove_taxonomy_boxes() {  
            remove_meta_box( 'plugin-categorydiv', 'plugin', 'normal');
            remove_meta_box( 'theme-categorydiv', 'theme', 'normal'); 
        }
        
        function add_plugin_cpt() {
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
        }
        
        function add_theme_cpt() {
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
        }
        
        function add_mirror_cpt() {
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
        }
        
        function add_project_cpt() {
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
        
        // The Callback  
        function plugin_meta_box() {  
            global $plugin_meta_fields, $post;

            // Field Array  
            $plugin_meta_fields = array(  
                array(  
                    'label' => __('Description', WPGIT_NAME),  
                    'desc'  => __('The description.', WPGIT_NAME),   
                    'id'    => 'wp_git_plugin_plugin_desc',  
                    'type'  => 'textarea'  
                ),
                array(  
                    'label' => __('Homepage', WPGIT_NAME),  
                    'desc'  => __("URL to the plugin's homepage, begin with http:// or https://", WPGIT_NAME),
                    'id'    => 'wp_git_plugin_plugin_url',
                    'placeholder'    => 'http://',
                    'type'  => 'text'
                ),
                array(  
                    'label' => __('Category', WPGIT_NAME),  
                    'id'    => 'plugin-category',
                    'type'  => 'tax_select'  
                ),
                array(  
                    'label' => __('Features', WPGIT_NAME), // TODO: different title
                    'desc'  => __('Select all included features.', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_plugin_features',  
                    'type'  => 'checkbox_group',  
                    'options' => array (  
                        'mu' => array (  
                            'label' => __('Multisite compatible', WPGIT_NAME),  
                            'value' => 'mu'  
                        ),  
                        'gettext' => array (  
                            'label' => __('Translation support', WPGIT_NAME),  
                            'value' => 'gettext'  
                        ),  
                        'unit' => array (  
                            'label' => __('Unit testing', WPGIT_NAME),  
                            'value' => 'unit'  
                        )  
                    )  
                ),
                array(  
                    'label' => __('Slug', WPGIT_NAME),  
                    'desc'  => __('The plugin slug.', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_plugin_slug',
                    'placeholder'    => 'plugin-name',
                    'type'  => 'text'
                ),
                array(  
                    'label' => __('GitHub user/org name', WPGIT_NAME),  
                    'desc'  => __('The GitHub user or organization hosting the repo.', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_plugin_github_user',
                    'placeholder'    => 'wp-repository',
                    'type'  => 'text'
                ),
                array(  
                    'label' => __('GitHub repo title', WPGIT_NAME),  
                    'desc'  => __('The Repository name on GitHub.', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_plugin_github_repo',
                    'placeholder'    => 'plugin-name',
                    'type'  => 'text'
                )
            );

            // Use nonce for verification  
            echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce( basename(__FILE__) ).'" />';  

            // Begin the field table and loop  
            echo '<table class="form-table">';  
            foreach ($plugin_meta_fields as $field) {  
                // get value of this field if it exists for this post  
                $meta = get_post_meta($post->ID, $field['id'], true);  
                // begin a table row with  
                echo '<tr> 
                        <th><label for="'.$field['id'].'">'.$field['label'].'</label></th> 
                        <td>';  
                        switch($field['type']) {  
                            // text  
                            case 'text':  
                                echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" placeholder="'.$field['placeholder'].'" /> 
                                    <br /><span class="description">'.$field['desc'].'</span>';  
                            break; 
                            // textarea  
                            case 'textarea':  
                                echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'.$meta.'</textarea> 
                                    <br /><span class="description">'.$field['desc'].'</span>';  
                            break; 
                            // checkbox_group  
                            case 'checkbox_group':  
                                foreach ($field['options'] as $option) {  
                                    echo '<input type="checkbox" value="'.$option['value'].'" name="'.$field['id'].'[]" id="'.$option['value'].'"',$meta && in_array($option['value'], $meta) ? ' checked="checked"' : '',' />  
                                            <label for="'.$option['value'].'">'.$option['label'].'</label><br />';  
                                }  
                                echo '<span class="description">'.$field['desc'].'</span>';  
                            break;
                            // tax_select  
                            case 'tax_select':  
                                echo '<select name="'.$field['id'].'" id="'.$field['id'].'"> 
                                        <option value="">' . __('Select One', WPGIT_NAME) . '</option>'; // Select One  
                                $terms = get_terms($field['id'], 'get=all');  
                                $selected = wp_get_object_terms($post->ID, $field['id']);  
                                foreach ($terms as $term) {  
                                    if (!empty($selected) && !strcmp($term->slug, $selected[0]->slug))   
                                        echo '<option value="'.$term->slug.'" selected="selected">'.$term->name.'</option>';   
                                    else  
                                        echo '<option value="'.$term->slug.'">'.$term->name.'</option>';   
                                }  
                                $taxonomy = get_taxonomy($field['id']);
                                echo '</select><br /><span class="description"><a href="'.get_bloginfo('home').'/wp-admin/edit-tags.php?taxonomy='.$field['id'].'&post_type=plugin">' . sprintf( __('Manage %s', WPGIT_NAME), $taxonomy->label ) . '</a></span>';  
                            break;  
                        } //end switch  
                echo '</td></tr>';  
            } // end foreach
            echo '
                <tr>
                    <th>' . __('Maintainer(s)/Collaborator(s)', WPGIT_NAME) . ':</th> 
                    <td>' . __('will be fetched from GitHub directly and refreshed daily', WPGIT_NAME) . '</td>
                </tr>';
            echo '
                <tr>
                    <th>' . __('Contributor(s)', WPGIT_NAME) . ':</th> 
                    <td>' . __('will be fetched from GitHub directly and refreshed daily', WPGIT_NAME) . '</td>
                </tr>';
            echo '</table>'; // end table 

        }  

        // The Callback  
        function theme_meta_box() {  
            global $theme_meta_fields, $post;

            // Field Array  
            $theme_meta_fields = array(  
                array(  
                    'label' => __('Description', WPGIT_NAME),  
                    'desc'  => __('The description.', WPGIT_NAME),   
                    'id'    => 'wp_git_plugin_theme_desc',  
                    'type'  => 'textarea'  
                ),
                array(  
                    'label' => __('Homepage', WPGIT_NAME),  
                    'desc'  => __("URL to the theme's homepage, begin with http:// or https://", WPGIT_NAME),
                    'id'    => 'wp_git_plugin_theme_url',
                    'placeholder'    => 'http://',
                    'type'  => 'text'
                ),
                array(  
                    'label' => __('Category', WPGIT_NAME),  
                    'id'    => 'theme-category',
                    'type'  => 'tax_select'  
                ),
                array(  
                    'label' => __('Slug', WPGIT_NAME),  
                    'desc'  => __('The theme slug.', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_plugin_slug',
                    'placeholder'    => 'theme-name',
                    'type'  => 'text'
                ),
                array(  
                    'label' => __('GitHub user/org name', WPGIT_NAME),  
                    'desc'  => __('The GitHub user or organization hosting the repo.', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_theme_github_user',
                    'placeholder'    => 'wp-repository',
                    'type'  => 'text'
                ),
                array(  
                    'label' => __('GitHub repo title', WPGIT_NAME),  
                    'desc'  => __('The Repository name on GitHub.', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_theme_github_repo',
                    'placeholder'    => 'plugin-name',
                    'type'  => 'text'
                ),
                array(  
                    'label' => __('Theme type', WPGIT_NAME),  
                    'desc'  => __('Select the type.', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_theme_type',  
                    'type'  => 'select',  
                    'options' => array (  
                        'standalone' => array (  
                            'label' => __('Standalone', WPGIT_NAME),  
                            'value' => 'standalone'  
                        ),  
                        'child' => array (  
                            'label' => __('Child', WPGIT_NAME),  
                            'value' => 'child'  
                        ),
                        'parent' => array (  
                            'label' => __('Parent', WPGIT_NAME),  
                            'value' => 'parent'  
                        ),
                        'framework' => array (  
                            'label' => __('Framework', WPGIT_NAME),  
                            'value' => 'framework'  
                        )  
                    )  
                )  
            );

            // Use nonce for verification  
            echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce( basename(__FILE__) ).'" />';  

            // Begin the field table and loop  
            echo '<table class="form-table">';  
            foreach ($theme_meta_fields as $field) {  
                // get value of this field if it exists for this post  
                $meta = get_post_meta($post->ID, $field['id'], true);  
                // begin a table row with  
                echo '<tr> 
                        <th><label for="'.$field['id'].'">'.$field['label'].'</label></th> 
                        <td>';  
                        switch($field['type']) {  
                            // text  
                            case 'text':  
                                echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" placeholder="'.$field['placeholder'].'" /> 
                                    <br /><span class="description">'.$field['desc'].'</span>';  
                            break; 
                            // textarea  
                            case 'textarea':  
                                echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'.$meta.'</textarea> 
                                    <br /><span class="description">'.$field['desc'].'</span>';  
                            break;
                            // select  
                            case 'select':  
                                echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';  
                                foreach ($field['options'] as $option) {  
                                    echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';  
                                }  
                                echo '</select><br /><span class="description">'.$field['desc'].'</span>';  
                            break;
                            // tax_select  
                            case 'tax_select':  
                                echo '<select name="'.$field['id'].'" id="'.$field['id'].'"> 
                                        <option value="">' . __('Select One', WPGIT_NAME) . '</option>'; // Select One  
                                $terms = get_terms($field['id'], 'get=all');  
                                $selected = wp_get_object_terms($post->ID, $field['id']);  
                                foreach ($terms as $term) {  
                                    if (!empty($selected) && !strcmp($term->slug, $selected[0]->slug))   
                                        echo '<option value="'.$term->slug.'" selected="selected">'.$term->name.'</option>';   
                                    else  
                                        echo '<option value="'.$term->slug.'">'.$term->name.'</option>';   
                                }  
                                $taxonomy = get_taxonomy($field['id']);
                                echo '</select><br /><span class="description"><a href="'.get_bloginfo('home').'/wp-admin/edit-tags.php?taxonomy='.$field['id'].'&post_type=theme">' . sprintf( __('Manage %s', WPGIT_NAME), $taxonomy->label ) . '</a></span>';  
                            break;
                        } //end switch  
                echo '</td></tr>';  
            } // end foreach
            echo '
                <tr>
                    <th>' . __('Maintainer(s)/Collaborator(s)', WPGIT_NAME) . ':</th> 
                    <td>' . __('will be fetched from GitHub directly and refreshed daily', WPGIT_NAME) . '</td>
                </tr>';
            echo '
                <tr>
                    <th>' . __('Contributor(s)', WPGIT_NAME) . ':</th> 
                    <td>' . __('will be fetched from GitHub directly and refreshed daily', WPGIT_NAME) . '</td>
                </tr>';
            echo '</table>'; // end table  
        }  
        
        // The Callback  
        function mirror_meta_box() {  
            global $mirror_meta_fields, $post;

            // Field Array  
            $mirror_meta_fields = array(
                array(  
                    'label' => __('WP.org-Slug', WPGIT_NAME),  
                    'desc'  => __('The plugin slug set on WordPress.org', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_mirror_wpslug',
                    'placeholder'    => 'plugin-name',
                    'type'  => 'text'
                ),
                array(  
                    'label'=> __('Sync interval', WPGIT_NAME),  
                    'desc'  => 'A description for the field',  
                    'id'    => $prefix.'mirror_sync',  
                    'type'  => 'select',  
                    'options' => array (  
                        'hourly' => array (  
                            'label' => 'normal (1h)',  
                            'value' => 'hourly'  
                        ),  
                        'daily' => array (  
                            'label' => 'slow (24h)',  
                            'value' => 'daily'  
                        ),  
                        'weekly' => array (  
                            'label' => 'abandoned (7d)',  
                            'value' => 'weekly'  
                        )  
                    )  
                )
            );

            // Use nonce for verification  
            echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce( basename(__FILE__) ).'" />';  

            // Begin the field table and loop  
            echo '<table class="form-table">';
            foreach ($mirror_meta_fields as $field) {  
                // get value of this field if it exists for this post  
                $meta = get_post_meta($post->ID, $field['id'], true);  
                // begin a table row with  
                echo '<tr> 
                        <th><label for="'.$field['id'].'">'.$field['label'].'</label></th> 
                        <td>';  
                        switch($field['type']) {  
                            // text  
                            case 'text':  
                                echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" placeholder="'.$field['placeholder'].'" /> 
                                    <br /><span class="description">'.$field['desc'].'</span>';  
                            break;  
                            // select  
                            case 'select':  
                                echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';  
                                foreach ($field['options'] as $option) {  
                                    echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';  
                                }  
                                echo '</select><br /><span class="description">'.$field['desc'].'</span>';  
                            break;  
                        } //end switch  
                echo '</td></tr>';  
            } // end foreach
            echo '
                <tr>
                    <th>' . __('SVN-Source', WPGIT_NAME) . ':</th> 
                    <td>http://plugins.svn.wordpress.org/<code> WP.org-Slug </code>/</td>
                </tr>';
            echo '
                <tr>
                    <th>' . __('Homepage', WPGIT_NAME) . ':</th> 
                    <td>http://wordpress.org/plugins/<code> WP.org-Slug </code>/</td>
                </tr>';
            echo '
                <tr>
                    <th>' . __('GitHub Target-Repo', WPGIT_NAME) . ':</th> 
                    <td>https://github.com/wp-mirrors/<code> WP.org-Slug </code>/</td>
                </tr>';
            echo '
                <tr>
                    <th>' . __('Repo Description', WPGIT_NAME) . ':</th> 
                    <td>WordPress-Mirror: <code> Plugin name </code> SVN repository (http://plugins.svn.wordpress.org/<code> WP.org-Slug </code>/)</td>
                </tr>';
            echo '
                <tr>
                    <th>' . __('Repo Link', WPGIT_NAME) . ':</th> 
                    <td>http://wordpress.org/plugins/<code> WP.org-Slug </code>/</td>
                </tr>';
            echo '</table>'; // end table
        }
        
        // The Callback  
        function project_meta_box() {  
            global $project_meta_fields, $post;

            // Field Array  
            $project_meta_fields = array(  
                array(  
                    'label' => __('Slug', WPGIT_NAME),  
                    'desc'  => __('The plugin slug.', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_project_slug',
                    'placeholder'    => 'plugin-name',
                    'type'  => 'text'
                ),
                array(  
                    'label' => __('WP.org-Slug', WPGIT_NAME),  
                    'desc'  => __('The plugin slug set on WordPress.org', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_project_wpslug',
                    'placeholder'    => 'plugin-name',
                    'type'  => 'text'
                ),
                array(  
                    'label' => __('<b>NO</b> WordPress.org Plugin', WPGIT_NAME),  
                    'desc'  => __('Check if the plugin is <b>NOT</b> listed on WP.org', WPGIT_NAME),  
                    'id'    => $prefix.'project_nowporg',
                    'type'  => 'checkbox'
                ), 
                array(  
                    'label' => __('GitHub user/org name', WPGIT_NAME),  
                    'desc'  => __('The GitHub user or organization hosting the repo.', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_project_ghuser',
                    'placeholder'    => 'wp-repository',
                    'type'  => 'text'
                ),
                array(  
                    'label' => __('GitHub repo title', WPGIT_NAME),  
                    'desc'  => __('The Repository name on GitHub.', WPGIT_NAME),  
                    'id'    => 'wp_git_plugin_project_ghrepo',
                    'placeholder'    => 'plugin-name',
                    'type'  => 'text'
                )
            );

            // Use nonce for verification  
            echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce( basename(__FILE__) ).'" />';  

            // Begin the field table and loop  
            echo '<table class="form-table">';
            echo '
                <tr>
                    <th>' . __('Before creating a new project', WPGIT_NAME) . '</th> 
                    <td>' . sprintf( __('Make sure you add the %s wordpress.org-user to the svn-repo to allow push access for the platform', WPGIT_NAME), '<code>wp-repository</code>') . '</td>
                </tr>';
            foreach ($project_meta_fields as $field) {  
                // get value of this field if it exists for this post  
                $meta = get_post_meta($post->ID, $field['id'], true);  
                // begin a table row with  
                echo '<tr> 
                        <th><label for="'.$field['id'].'">'.$field['label'].'</label></th> 
                        <td>';  
                        switch($field['type']) {  
                            // text  
                            case 'text':  
                                echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" placeholder="'.$field['placeholder'].'" /> 
                                    <br /><span class="description">'.$field['desc'].'</span>';  
                            break;
                            // checkbox  
                            case 'checkbox':  
                                echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/> 
                                    <label for="'.$field['id'].'">'.$field['desc'].'</label>';  
                            break;
                        } //end switch  
                echo '</td></tr>';  
            } // end foreach
            echo '
                <tr>
                    <th>' . __('SVN-Target', WPGIT_NAME) . ':</th> 
                    <td>http://plugins.svn.wordpress.org/<code> WP.org-Slug </code>/</td>
                </tr>';
            echo '
                <tr>
                    <th>' . __('GlotPress', WPGIT_NAME) . ':</th> 
                    <td>https://translate.wp-repository.org/projects/<code> WP.org-Slug </code>/</td>
                </tr>';
            echo '</table>'; // end table 
            
            /*
             * On creation:
             * 1.: git clone <git-repo> to wp-git.org server
             * 2.: add svn target to git
             * 3.: add project to translate.wp-repository.org
             *  3.1.: generate .pot file
             *  3.2.: import (.pot) gettext strings to glotpress project 
             */

        }
        
        // Save the Data  
        function save_meta_data( $post_id ) {   // TODO $custom_meta_fields vs $plugin/theme_meta_fields
//            global $plugin_meta_fields;  
//
//            // verify nonce  
//            if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__)))   
//                return $post_id;  
//            // check autosave  
//            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)  
//                return $post_id;  
//            // check permissions  
//            if ('page' == $_POST['post_type']) {  
//                if (!current_user_can('edit_page', $post_id))  
//                    return $post_id;  
//                } elseif (!current_user_can('edit_post', $post_id)) {  
//                    return $post_id;  
//            }  
//
//            // loop through fields and save the data  
//            foreach ($plugin_meta_fields as $field) {  
//                $old = get_post_meta($post_id, $field['id'], true);  
//                $new = $_POST[$field['id']];  
//                if ($new && $new != $old) {  
//                    update_post_meta($post_id, $field['id'], $new);  
//                } elseif ('' == $new && $old) {  
//                    delete_post_meta($post_id, $field['id'], $old);  
//                }  
//            } // end foreach  
        }  
        
        function activation() {
            // add CPTs to the system
            WPGitPlugin::add_plugin_cpt();
            WPGitPlugin::add_theme_cpt();
            WPGitPlugin::add_mirror_cpt();
            WPGitPlugin::add_project_cpt();

            // flush the rewrites to add CPTs
            flush_rewrite_rules();
        }
        
        function deactivation() {
            // flush the rewrites to remove CPTs
            flush_rewrite_rules();
        }

		function localization() {
			load_plugin_textdomain( 'wp-git-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
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

	} // END class WPGitPlugin

	// $GLOBALS['WPGitPlugin'] = new WPGitPlugin();

} // END if class_exists