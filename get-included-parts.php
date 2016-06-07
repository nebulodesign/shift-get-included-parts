<?php 
/*
Plugin Name:	SHIFT - Get Included Parts
Plugin URI:		https://github.com/nebulodesign/shift-get-included-parts/
Description:	Displays a list of currently included template parts in the Admin Bar
Version:			1.0.2
Author:				Nebulo Design
Author URI:		http://www.nebulodesign.com
License:			GPL
*/

/**
 * Initiate custom plugin class - fetches updates from our own public GitHub repository.
 */
if( is_admin() && class_exists( 'Shift_Plugin_Updater' ) ) new Shift_Plugin_Updater( __FILE__ );


// Add currently active Shift parts and template files to the admin bar
add_action('admin_bar_menu', function(){

	global $wp_admin_bar, $template;

	// Locate all template parts and components
	$all_template_parts = glob( get_stylesheet_directory() . '/parts/{,components/}*.php', GLOB_BRACE );

	// Filtered list of included files in the current template directory
	$included_template_parts = array_filter( array_map( function( $filepath ){
		// Exclude common template parts
		$common_template_parts = array( 'head.php', 'site-header.php', 'site-footer.php' );
		return ( strpos( $filepath, get_stylesheet_directory() ) > -1 && !in_array( basename( $filepath ), $common_template_parts ) ) ? $filepath : null;
	}, get_included_files() ) );

	// List the remaining included files that are also located within the parts and components folders
	$shift_template_parts = array_intersect($all_template_parts, $included_template_parts);

	if( is_super_admin() && !is_admin() ) {

		$menu_id = 'shift-parts';

		$parent_icon = '&#9776; '; // list icon
		$post_file_icon = '&#9733; '; // star icon
		$part_icon = '&nbsp;&bullet;&nbsp; '; // long dash

		// Main admin bar button (hover over to view list)
		$parent_args = array(
			'id' => $menu_id,
			'title' => $parent_icon . 'Active Template Files'
		);

		$wp_admin_bar->add_menu( $parent_args );

		if( is_page() || is_single() ) {

			// Display Page or Post ID#
			$post_args = array(
				'parent' => $menu_id,
				'title' => $post_file_icon . get_post_type_object( get_post_type() )->labels->singular_name . ' ID: ' . get_the_ID(),
				'id' => 'shift-page-id'
			);

			$wp_admin_bar->add_menu( $post_args );
		}

		// Display base template file
		$file_args = array(
			'parent' => $menu_id,
			'title' => $post_file_icon . str_replace( $template->slug . '-', '', $template->templates[0] ),
			'id' => 'shift-base'
		);

		$wp_admin_bar->add_menu( $file_args );

		// Display list of the active template parts
		if( count( $shift_template_parts ) > 0 ) {
			foreach( $shift_template_parts as $part ) {

				$part_args = array(
					'parent' => $menu_id,
					'title' => $part_icon . str_replace( trailingslashit( get_stylesheet_directory() ), '', $part ),
					'id' => 'shift-' . $part
				);

				$wp_admin_bar->add_menu( $part_args );
			}
		}

		// Create link to disable plugin
		$link_args = array(
			'parent' => $menu_id,
			'title' => 'Disable this plugin',
			'id' => 'disable',
			'href' => get_site_url() . '/wp-admin/plugins.php',
			'meta' => array( 'target' => '_blank' )
		);

		$wp_admin_bar->add_menu( $link_args );
	}

}, 999 );
