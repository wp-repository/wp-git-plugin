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
        
        const ID		= 'wp-git-plugin'; // TODO:
		const KEY		= 'wp_git_plugin'; // TODO:
		const NAME		= 'WP-Git Plugin'; // TODO:
		const VERSION	= '0.1-dev'; // TODO:

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
		}

		protected function init() {

		}

		protected function frontend_init() {

		}

		protected function admin_init() {
            add_filter( 'plugin_row_meta', array( $this, 'set_plugin_meta' ), 10, 2 );
		}

		protected function network_admin_init() {

		}
        
		function __construct() {
			
		}

		function localization() {
			load_plugin_textdomain( 'multisite-plugin-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
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