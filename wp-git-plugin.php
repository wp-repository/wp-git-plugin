<?php
/*
Plugin Name: WP-Git Plugin
Plugin URI: https://github.com/wp-repository/wp-git-plugin
Description: Manage SVN2Git mirror sync and other Git-related services for WP-Repository.org
Version: 0.1-dev
Author: Foe Services Labs
Author URI: http://labs.foe-services.de
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-git-plugins
Domain Path: /languages
    
    WP-Git Plugin
    
    Copyright (C) 2013 Foe Services Labs (http://labs.foe-services.de)

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

if ( !class_exists('WPGitPlugin') ) { 

	class WPGitPlugin {
        
        const ID		= 'wp-git-plugin';
        const KEY		= 'wp_git_plugin';
        const NAME		= 'WP-Git Plugin';
        const VERSION	= '0.1-dev';

        protected $prefix = 'wp_git_plugin_';

        public function __construct() {
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

        protected function init() {
            add_action( 'init', array( $this, 'add_plugin_cpt') );
            add_action( 'init', array( $this, 'add_theme_cpt') );
        }

        protected function frontend_init() {

        }

        protected function admin_init() {
            add_action( 'add_meta_boxes', array( $this, 'properties_meta_boxes') );  
            add_filter( 'plugin_row_meta', array( $this, 'set_plugin_meta' ), 10, 2 );
        }

        protected function network_admin_init() {

        }
        
        function add_plugin_cpt() {
            // set lables for plugins cpts
            $plugin_labels = array(
                'name' => __('Plugins', self::ID),
                'singular_name' => __('Plugin', self::ID),
                'add_new' => __('Add New'),
                'add_new_item' => __('Add New Plugin', self::ID),
                'edit_item' => __('Edit Plugin', self::ID),
                'new_item' => __('New Plugin', self::ID),
                'view_item' => __('View Plugin', self::ID),
                'search_items' => __('Search Plugins', self::ID),
                'not_found' =>  __('No Plugins found', self::ID),
                'not_found_in_trash' => __('No Plugins in the trash', self::ID),
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
                    'supports' => array( 'title', 'editor', 'thumbnail' ),
                    // 'register_meta_box_cb' => 'testimonials_meta_boxes',
                )
            );
            
            // attach hierarchical taxonomy to plugin cpt
            $plugin_tax_labels = array(
              'name'                => _x('Categories', 'taxonomy general name', self::ID),
              'singular_name'       => _x('Category', 'taxonomy singular name', self::ID),
              'search_items'        => __('Search Categories', self::ID),
              'all_items'           => __('All Categories', self::ID),
              'parent_item'         => __('Parent Category', self::ID),
              'parent_item_colon'   => __('Parent Category:', self::ID),
              'edit_item'           => __('Edit Category', self::ID), 
              'update_item'         => __('Update Category', self::ID),
              'add_new_item'        => __('Add New Category', self::ID),
              'new_item_name'       => __('New Category Name', self::ID),
              'menu_name'           => __('Categories', self::ID)
            ); 	

            $plugin_tax_args = array(
              'hierarchical'        => true,
              'labels'              => $plugin_tax_labels,
              'show_ui'             => true,
              'show_admin_column'   => true,
              'query_var'           => true,
              'rewrite'             => array( 'slug' => 'plugins' )
            );

            register_taxonomy( 'plugin-category', array( 'plugin' ), $plugin_tax_args );
            
            // TODO: add a meta_box for the parameters like repo, maintainer, etc.
        }
        
        function add_theme_cpt() {
            // set lables for themes cpts
            $themes_labels = array(
                'name' => __('Themes', self::ID),
                'singular_name' => __('Theme', self::ID),
                'add_new' => __('Add New'),
                'add_new_item' => __('Add New Theme', self::ID),
                'edit_item' => __('Edit Theme', self::ID),
                'new_item' => __('New Theme', self::ID),
                'view_item' => __('View Theme', self::ID),
                'search_items' => __('Search Themes', self::ID),
                'not_found' =>  __('No Themes found', self::ID),
                'not_found_in_trash' => __('No Themes in the trash', self::ID),
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
                    'supports' => array( 'title', 'editor', 'thumbnail' ),
                    // 'register_meta_box_cb' => 'testimonials_meta_boxes',
                )
            );
            
            // attach hierarchical taxonomy to theme cpt
            $theme_tax_labels = array(
              'name'                => _x('Categories', 'taxonomy general name', self::ID),
              'singular_name'       => _x('Category', 'taxonomy singular name', self::ID),
              'search_items'        => __('Search Categories', self::ID),
              'all_items'           => __('All Categories', self::ID),
              'parent_item'         => __('Parent Category', self::ID),
              'parent_item_colon'   => __('Parent Category:', self::ID),
              'edit_item'           => __('Edit Category', self::ID), 
              'update_item'         => __('Update Category', self::ID),
              'add_new_item'        => __('Add New Category', self::ID),
              'new_item_name'       => __('New Category Name', self::ID),
              'menu_name'           => __('Categories', self::ID)
            );  	

            $theme_tax_args = array(
              'hierarchical'        => true,
              'labels'              => $theme_tax_labels,
              'show_ui'             => true,
              'show_admin_column'   => true,
              'query_var'           => true,
              'rewrite'             => array( 'slug' => 'themes' )
            );

            register_taxonomy( 'theme-category', array( 'theme' ), $theme_tax_args );
            
            // TODO: add a meta_box for the parameters like repo, maintainer, etc.
        }
        
        // Add the Meta Boxes  
        function properties_meta_boxes() {  
            add_meta_box(  
                'plugin_properties', // $id  
                __('Plugin Properties', self::ID), // $title   
                array( $this, 'plugin_meta_box'), // $callback    --- TODO
                'plugin', // $page  
                'normal', // $context  
                'high' // $priority
            );  
            
            add_meta_box(  
                'theme_properties', // $id  
                __('Theme Properties', self::ID), // $title   
                array( $this, 'theme_meta_box'), // $callback  --- TODO
                'theme', // $page  
                'normal', // $context  
                'high' // $priority
            );  
        }  
        
        // The Callback  
        function plugin_meta_box() {  
            global $post;

            // Field Array  
            $custom_meta_fields = array(  
                array(  
                    'label'=> 'Text Input',  
                    'desc'  => 'A description for the field.',  
                    'id'    => $this->prefix.'plugin_text',  
                    'type'  => 'text'  
                ),  
                array(  
                    'label'=> 'Textarea',  
                    'desc'  => 'A description for the field.',  
                    'id'    => $this->prefix.'plugin_textarea',  
                    'type'  => 'textarea'  
                ),  
                array(  
                    'label'=> 'Checkbox Input',  
                    'desc'  => 'A description for the field.',  
                    'id'    => $this->prefix.'plugin_checkbox',  
                    'type'  => 'checkbox'  
                ),  
                array(  
                    'label'=> 'Select Box',  
                    'desc'  => 'A description for the field.',  
                    'id'    => $this->prefix.'plugin_select',  
                    'type'  => 'select',  
                    'options' => array (  
                        'one' => array (  
                            'label' => 'Option One',  
                            'value' => 'one'  
                        ),  
                        'two' => array (  
                            'label' => 'Option Two',  
                            'value' => 'two'  
                        ),  
                        'three' => array (  
                            'label' => 'Option Three',  
                            'value' => 'three'  
                        )  
                    )  
                )  
            );

            // Use nonce for verification  
            echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';  

            // Begin the field table and loop  
            echo '<table class="form-table">';  
            foreach ($custom_meta_fields as $field) {  
                // get value of this field if it exists for this post  
                $meta = get_post_meta($post->ID, $field['id'], true);  
                // begin a table row with  
                echo '<tr> 
                        <th><label for="'.$field['id'].'">'.$field['label'].'</label></th> 
                        <td>';  
                        switch($field['type']) {  
                            // text  
                            case 'text':  
                                echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" /> 
                                    <br /><span class="description">'.$field['desc'].'</span>';  
                            break; 
                            // textarea  
                            case 'textarea':  
                                echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'.$meta.'</textarea> 
                                    <br /><span class="description">'.$field['desc'].'</span>';  
                            break; 
                            // checkbox  
                            case 'checkbox':  
                                echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/> 
                                    <label for="'.$field['id'].'">'.$field['desc'].'</label>';  
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
            echo '</table>'; // end table 

        }  

        // The Callback  
        function theme_meta_box() {  
            global $post;

            // Field Array  
            $custom_meta_fields = array(  
                array(  
                    'label'=> 'Text Input',  
                    'desc'  => 'A description for the field.',  
                    'id'    => $this->prefix.'theme_text',  
                    'type'  => 'text'  
                ),  
                array(  
                    'label'=> 'Textarea',  
                    'desc'  => 'A description for the field.',  
                    'id'    => $this->prefix.'theme_textarea',  
                    'type'  => 'textarea'  
                ),  
                array(  
                    'label'=> 'Checkbox Input',  
                    'desc'  => 'A description for the field.',  
                    'id'    => $this->prefix.'theme_checkbox',  
                    'type'  => 'checkbox'  
                ),  
                array(  
                    'label'=> 'Select Box',  
                    'desc'  => 'A description for the field.',  
                    'id'    => $this->prefix.'theme_select',  
                    'type'  => 'select',  
                    'options' => array (  
                        'one' => array (  
                            'label' => 'Option One',  
                            'value' => 'one'  
                        ),  
                        'two' => array (  
                            'label' => 'Option Two',  
                            'value' => 'two'  
                        ),  
                        'three' => array (  
                            'label' => 'Option Three',  
                            'value' => 'three'  
                        )  
                    )  
                )  
            );

            // Use nonce for verification  
            echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';  

            // Begin the field table and loop  
            echo '<table class="form-table">';  
            foreach ($custom_meta_fields as $field) {  
                // get value of this field if it exists for this post  
                $meta = get_post_meta($post->ID, $field['id'], true);  
                // begin a table row with  
                echo '<tr> 
                        <th><label for="'.$field['id'].'">'.$field['label'].'</label></th> 
                        <td>';  
                        switch($field['type']) {  
                            // case items will go here  
                        } //end switch  
                echo '</td></tr>';  
            } // end foreach  
            echo '</table>'; // end table  
        }  
        
        function activation() {
            // add CPTs to the system
            WPGitPlugin::add_plugin_cpt();
            WPGitPlugin::add_theme_cpt();

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

	$GLOBALS['WPGitPlugin'] = new WPGitPlugin();

} // END if class_exists