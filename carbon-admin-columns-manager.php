<?php  
if ( class_exists('Carbon_Admin_Column') ) {
	return;
}

require_once('inc/Carbon_Admin_Columns_Manager.php');

require_once('inc/Carbon_Admin_Columns_Manager_Post_Columns.php');

require_once('inc/Carbon_Admin_Columns_Manager_Taxonomy_Columns.php');

require_once('inc/Carbon_Admin_Columns_Manager_User_Columns.php');

require_once('inc/Carbon_Admin_Column.php');