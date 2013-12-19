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
 
REQUIREMENT: m4tthumphrey/php-gitlab-api: 0.* via composer
    
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

/** Register autoloader */
spl_autoload_register( 'WPGit::autoload' );

class WPGit {
	
	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Current version of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.0-beta';
	// public $db_version = '1';

	/**
	 * Holds a copy of the main plugin filepath.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private static $file = __FILE__;

	public function __construct() {
		
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		register_activation_hook( __FILE__, array( 'WPGitPlugin', 'activate_plugin') );
		register_deactivation_hook( __FILE__, array( 'WPGitPlugin', 'deactivate_plugin') );
	}
	
	/**
	 * @TODO
	 *
	 * @since 1.0.0
	 */
	public function init() {
		
		if ( is_admin() ) {
			
			$wpc_languageservices_admin = new WPC_LanguageServices_Admin();
			
		}
		
	} // END init()

	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		return self::$instance;

	} // END get_instance()
	
	/**
	 * PSR-0 compliant autoloader to load classes as needed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $classname The name of the class
	 * @return null Return early if the class name does not start with the correct prefix
	 */
	public static function autoload( $classname ) {

		if ( 'WPGit' !== mb_substr( $classname, 0, 5 ) )
			return;

		$filename = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . str_replace( '_', DIRECTORY_SEPARATOR, $classname ) . '.php';
		if ( file_exists( $filename ) )
			require $filename;

	} // END autoload()

	/* Only run our customization on the 'edit.php' page in the admin. */


	function my_edit_movie_load() {
		add_filter( 'request', array( &$this, 'my_sort_movies') );
	}

	/* Sorts the movies. */
	function my_sort_movies( $vars ) {

		/* Check if we're viewing the 'mirror' post type. */
		if ( isset( $vars['post_type'] ) && 'mirror' == $vars['post_type'] ) {

			/* Check if 'orderby' is set to 'duration'. */
			if ( isset( $vars['orderby'] ) && 'mirror_wpslug' == $vars['orderby'] ) {

				/* Merge the query vars with our custom variables. */
				$vars = array_merge(
					$vars,
					array(
						'meta_key' => 'mirror_wpslug',
						'orderby' => 'meta_value_num'
					)
				);
			}
		}

		return $vars;
	}

	protected function admin_init() {

		add_action( 'load-edit.php', array( $this, 'my_edit_movie_load') );


		add_action( 'save_post', array( $this, 'save_meta_data') );



	}

	// The Callback  
	function plugin_meta_box() {  
		global $plugin_meta_fields, $post;

		// Field Array  
		$plugin_meta_fields = array(  
			array(  
				'label' => __('Description', 'wp-git-plugin'),  
				'desc'  => __('The description.', 'wp-git-plugin'),   
				'id'    => 'wp_git_plugin_plugin_desc',  
				'type'  => 'textarea'  
			),
			array(  
				'label' => __('Homepage', 'wp-git-plugin'),  
				'desc'  => __("URL to the plugin's homepage, begin with http:// or https://", 'wp-git-plugin'),
				'id'    => 'wp_git_plugin_plugin_url',
				'placeholder'    => 'http://',
				'type'  => 'text'
			),
			array(  
				'label' => __('Category', 'wp-git-plugin'),  
				'id'    => 'plugin-category',
				'type'  => 'tax_select'  
			),
			array(  
				'label' => __('Features', 'wp-git-plugin'), // TODO: different title
				'desc'  => __('Select all included features.', 'wp-git-plugin'),  
				'id'    => 'wp_git_plugin_plugin_features',  
				'type'  => 'checkbox_group',  
				'options' => array (  
					'mu' => array (  
						'label' => __('Multisite compatible', 'wp-git-plugin'),  
						'value' => 'mu'  
					),  
					'gettext' => array (  
						'label' => __('Translation support', 'wp-git-plugin'),  
						'value' => 'gettext'  
					),  
					'unit' => array (  
						'label' => __('Unit testing', 'wp-git-plugin'),  
						'value' => 'unit'  
					)  
				)  
			),
			array(  
				'label' => __('Slug', 'wp-git-plugin'),  
				'desc'  => __('The plugin slug.', 'wp-git-plugin'),  
				'id'    => 'wp_git_plugin_plugin_slug',
				'placeholder'    => 'plugin-name',
				'type'  => 'text'
			),
			array(  
				'label' => __('GitHub user/org name', 'wp-git-plugin'),  
				'desc'  => __('The GitHub user or organization hosting the repo.', 'wp-git-plugin'),  
				'id'    => 'wp_git_plugin_plugin_github_user',
				'placeholder'    => 'wp-repository',
				'type'  => 'text'
			),
			array(  
				'label' => __('GitHub repo title', 'wp-git-plugin'),  
				'desc'  => __('The Repository name on GitHub.', 'wp-git-plugin'),  
				'id'    => 'wp_git_plugin_plugin_github_repo',
				'placeholder'    => 'plugin-name',
				'type'  => 'text'
			)
		);

		// Use nonce for verification  
		echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce( basename(__FILE__) ).'" />';  

		// Begin the field table and loop  
		echo '<table class="form-table">';  
		foreach ( $plugin_meta_fields as $field ) {  
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
									<option value="">' . __('Select One', 'wp-git-plugin') . '</option>'; // Select One  
							$terms = get_terms($field['id'], 'get=all');  
							$selected = wp_get_object_terms($post->ID, $field['id']);  
							foreach ($terms as $term) {  
								if (!empty($selected) && !strcmp($term->slug, $selected[0]->slug))   
									echo '<option value="'.$term->slug.'" selected="selected">'.$term->name.'</option>';   
								else  
									echo '<option value="'.$term->slug.'">'.$term->name.'</option>';   
							}  
							$taxonomy = get_taxonomy($field['id']);
							echo '</select><br /><span class="description"><a href="'.get_bloginfo('home').'/wp-admin/edit-tags.php?taxonomy='.$field['id'].'&post_type=plugin">' . sprintf( __('Manage %s', 'wp-git-plugin'), $taxonomy->label ) . '</a></span>';  
						break;  
					} //end switch  
			echo '</td></tr>';  
		} // end foreach
		echo '
			<tr>
				<th>' . __('Maintainer(s)/Collaborator(s)', 'wp-git-plugin') . ':</th> 
				<td>' . __('will be fetched from GitHub directly and refreshed daily', 'wp-git-plugin') . '</td>
			</tr>';
		echo '
			<tr>
				<th>' . __('Contributor(s)', 'wp-git-plugin') . ':</th> 
				<td>' . __('will be fetched from GitHub directly and refreshed daily', 'wp-git-plugin') . '</td>
			</tr>';
		echo '</table>'; // end table 

	}  

	// The Callback  
	function theme_meta_box() {  
		global $theme_meta_fields, $post;

		// Field Array  
		$theme_meta_fields = array(  
			array(  
				'label' => __('Description', 'wp-git-plugin'),  
				'desc'  => __('The description.', 'wp-git-plugin'),   
				'id'    => 'wp_git_plugin_theme_desc',  
				'type'  => 'textarea'  
			),
			array(  
				'label' => __('Homepage', 'wp-git-plugin'),  
				'desc'  => __("URL to the theme's homepage, begin with http:// or https://", 'wp-git-plugin'),
				'id'    => 'wp_git_plugin_theme_url',
				'placeholder'    => 'http://',
				'type'  => 'text'
			),
			array(  
				'label' => __('Category', 'wp-git-plugin'),  
				'id'    => 'theme-category',
				'type'  => 'tax_select'  
			),
			array(  
				'label' => __('Slug', 'wp-git-plugin'),  
				'desc'  => __('The theme slug.', 'wp-git-plugin'),  
				'id'    => 'wp_git_plugin_plugin_slug',
				'placeholder'    => 'theme-name',
				'type'  => 'text'
			),
			array(  
				'label' => __('GitHub user/org name', 'wp-git-plugin'),  
				'desc'  => __('The GitHub user or organization hosting the repo.', 'wp-git-plugin'),  
				'id'    => 'wp_git_plugin_theme_github_user',
				'placeholder'    => 'wp-repository',
				'type'  => 'text'
			),
			array(  
				'label' => __('GitHub repo title', 'wp-git-plugin'),  
				'desc'  => __('The Repository name on GitHub.', 'wp-git-plugin'),  
				'id'    => 'wp_git_plugin_theme_github_repo',
				'placeholder'    => 'plugin-name',
				'type'  => 'text'
			),
			array(  
				'label' => __('Theme type', 'wp-git-plugin'),  
				'desc'  => __('Select the type.', 'wp-git-plugin'),  
				'id'    => 'wp_git_plugin_theme_type',  
				'type'  => 'select',  
				'options' => array (  
					'standalone' => array (  
						'label' => __('Standalone', 'wp-git-plugin'),  
						'value' => 'standalone'  
					),  
					'child' => array (  
						'label' => __('Child', 'wp-git-plugin'),  
						'value' => 'child'  
					),
					'parent' => array (  
						'label' => __('Parent', 'wp-git-plugin'),  
						'value' => 'parent'  
					),
					'framework' => array (  
						'label' => __('Framework', 'wp-git-plugin'),  
						'value' => 'framework'  
					)  
				)  
			)  
		);

		// Use nonce for verification  
		echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce( basename(__FILE__) ).'" />';  

		// Begin the field table and loop  
		echo '<table class="form-table">';  
		foreach ( $theme_meta_fields as $field ) {  
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
									<option value="">' . __('Select One', 'wp-git-plugin') . '</option>'; // Select One  
							$terms = get_terms($field['id'], 'get=all');  
							$selected = wp_get_object_terms($post->ID, $field['id']);  
							foreach ($terms as $term) {  
								if (!empty($selected) && !strcmp($term->slug, $selected[0]->slug))   
									echo '<option value="'.$term->slug.'" selected="selected">'.$term->name.'</option>';   
								else  
									echo '<option value="'.$term->slug.'">'.$term->name.'</option>';   
							}  
							$taxonomy = get_taxonomy($field['id']);
							echo '</select><br /><span class="description"><a href="'.get_bloginfo('home').'/wp-admin/edit-tags.php?taxonomy='.$field['id'].'&post_type=theme">' . sprintf( __('Manage %s', 'wp-git-plugin'), $taxonomy->label ) . '</a></span>';  
						break;
					} //end switch  
			echo '</td></tr>';  
		} // end foreach
		echo '
			<tr>
				<th>' . __('Maintainer(s)/Collaborator(s)', 'wp-git-plugin') . ':</th> 
				<td>' . __('will be fetched from GitHub directly and refreshed daily', 'wp-git-plugin') . '</td>
			</tr>';
		echo '
			<tr>
				<th>' . __('Contributor(s)', 'wp-git-plugin') . ':</th> 
				<td>' . __('will be fetched from GitHub directly and refreshed daily', 'wp-git-plugin') . '</td>
			</tr>';
		echo '</table>'; // end table  
	}  

	// The Callback  
	function mirror_meta_box() {  
		global $mirror_meta_fields, $post;

		// Field Array  
		$mirror_meta_fields = array(
			array(  
				'label' => __('WP.org-Slug', 'wp-git-plugin'),  
				'desc'  => __('The plugin slug set on WordPress.org', 'wp-git-plugin'),  
				'id'    => 'wp_git_plugin_mirror_wpslug',
				'placeholder'    => 'plugin-name',
				'type'  => 'text'
			),
			array(  
				'label'=> __('Sync interval', 'wp-git-plugin'),  
				'desc'  => 'A description for the field',  
				'id'    => 'wp_git_plugin_mirror_sync',  
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
		foreach ( $mirror_meta_fields as $field ) {  
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
				<th>' . __('SVN-Source', 'wp-git-plugin') . ':</th> 
				<td>http://plugins.svn.wordpress.org/<code> WP.org-Slug </code>/</td>
			</tr>';
		echo '
			<tr>
				<th>' . __('Homepage', 'wp-git-plugin') . ':</th> 
				<td>http://wordpress.org/plugins/<code> WP.org-Slug </code>/</td>
			</tr>';
		echo '
			<tr>
				<th>' . __('GitHub Target-Repo', 'wp-git-plugin') . ':</th> 
				<td>https://github.com/wp-mirrors/<code> WP.org-Slug </code>/</td>
			</tr>';
		echo '
			<tr>
				<th>' . __('Repo Description', 'wp-git-plugin') . ':</th> 
				<td>WordPress-Mirror: <code> Plugin name </code> SVN repository (http://plugins.svn.wordpress.org/<code> WP.org-Slug </code>/)</td>
			</tr>';
		echo '
			<tr>
				<th>' . __('Repo Link', 'wp-git-plugin') . ':</th> 
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
				'label' => __('Slug', 'wp-git-plugin'),  
				'desc'  => __('The plugin slug.', 'wp-git-plugin'),  
				'id'    => 'wp_git_plugin_project_slug',
				'placeholder'    => 'plugin-name',
				'type'  => 'text'
			),
			array(  
				'label' => __('WP.org-Slug', 'wp-git-plugin'),  
				'desc'  => __('The plugin slug set on WordPress.org', 'wp-git-plugin'),  
				'id'    => 'wp_git_plugin_project_wpslug',
				'placeholder'    => 'plugin-name',
				'type'  => 'text'
			),
			array(  
				'label' => __('<b>NO</b> WordPress.org Plugin', 'wp-git-plugin'),  
				'desc'  => __('Check if the plugin is <b>NOT</b> listed on WP.org', 'wp-git-plugin'),  
				'id'    => $prefix.'project_nowporg',
				'type'  => 'checkbox'
			), 
			array(  
				'label' => __('GitHub user/org name', 'wp-git-plugin'),  
				'desc'  => __('The GitHub user or organization hosting the repo.', 'wp-git-plugin'),  
				'id'    => 'wp_git_plugin_project_ghuser',
				'placeholder'    => 'wp-repository',
				'type'  => 'text'
			),
			array(  
				'label' => __('GitHub repo title', 'wp-git-plugin'),  
				'desc'  => __('The Repository name on GitHub.', 'wp-git-plugin'),  
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
				<th>' . __('Before creating a new project', 'wp-git-plugin') . '</th> 
				<td>' . sprintf( __('Make sure you add the %s wordpress.org-user to the svn-repo to allow push access for the platform', 'wp-git-plugin'), '<code>wp-repository</code>') . '</td>
			</tr>';
		foreach ( $project_meta_fields as $field ) {  
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
				<th>' . __('SVN-Target', 'wp-git-plugin') . ':</th> 
				<td>http://plugins.svn.wordpress.org/<code> WP.org-Slug </code>/</td>
			</tr>';
		echo '
			<tr>
				<th>' . __('GlotPress', 'wp-git-plugin') . ':</th> 
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

	function activate_plugin() {
		// add CPTs to the system
		WPGitPlugin::init_cpts();

		// flush the rewrites to add CPTs
		flush_rewrite_rules();
	}

	function deactivate_plugin() {
		// flush the rewrites to remove CPTs
		flush_rewrite_rules();
	}

	function localization() {
		load_plugin_textdomain( 'wp-git-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

} // END class WPGit

$wpgit = new WPGit;
