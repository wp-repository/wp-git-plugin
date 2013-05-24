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
            add_action( 'init', array( $this, 'add_cpts') );
		}

		protected function frontend_init() {

		}

		protected function admin_init() {
            add_filter( 'plugin_row_meta', array( $this, 'set_plugin_meta' ), 10, 2 );
		}

		protected function network_admin_init() {

		}
        
        function add_cpts() {
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

            register_post_type(
                'plugins', 
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
                    'supports' => array( 'editor' ),
                    // 'register_meta_box_cb' => 'testimonials_meta_boxes',
                )
            );
            
            // set lables for themes cpts
//            $themes_labels = array(
//                'name' => __('Themes', self::ID),
//                'singular_name' => __('Theme', self::ID),
//                'add_new' => __('Add New'),
//                'add_new_item' => __('Add New Theme', self::ID),
//                'edit_item' => __('Edit Theme', self::ID),
//                'new_item' => __('New Theme', self::ID),
//                'view_item' => __('View Theme', self::ID),
//                'search_items' => __('Search Themes', self::ID),
//                'not_found' =>  __('No Themes found', self::ID),
//                'not_found_in_trash' => __('No Themes in the trash', self::ID),
//                'parent_item_colon' => '',
//            );
            
//            register_post_type(
//                'themes', 
//                array(
//                    'labels' => $themes_labels,
//                    'public' => true,
//                    'publicly_queryable' => true,
//                    'show_ui' => true,
//                    'exclude_from_search' => false,
//                    'query_var' => true,
//                    'rewrite' => true, // check
//                    'capability_type' => 'post',
//                    //'capabilities' => '';
//                    'has_archive' => true,
//                    'hierarchical' => false,
//                    'menu_position' => 10,
//                    'supports' => array( 'editor' ),
//                    // 'register_meta_box_cb' => 'testimonials_meta_boxes',
//                )
//            );
            
        }
        
        function activation() {
            // add CPTs to the system
            add_cpts();

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