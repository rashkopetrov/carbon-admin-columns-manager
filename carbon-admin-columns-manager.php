<?php  
/**
 * Plugin Name: CRB Columns Manager
 */

if ( class_exists('Carbon_Admin_Column') ) {
	return;
}

require_once('inc/Carbon_Admin_Columns_Manager.php');

require_once('inc/Carbon_Admin_Columns_Manager_Post.php');

require_once('inc/Carbon_Admin_Columns_Manager_Taxonomy.php');

require_once('inc/Carbon_Admin_Columns_Manager_User.php');

require_once('inc/Carbon_Admin_Column.php');