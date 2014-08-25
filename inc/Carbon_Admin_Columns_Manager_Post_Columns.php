<?php

class Carbon_Admin_Columns_Manager_Post_Columns extends Carbon_Admin_Columns_Manager {

	public function remove($columns_to_remove) {
		$this->columns_to_remove = (array) $columns_to_remove;
		
		// currently available only for post types
		add_filter( 'manage_posts_columns' , array($this, 'unset_admin_columns') );
		add_filter( 'manage_pages_columns' , array($this, 'unset_admin_columns') );

		return $this;
	}

	public function add( $columns ) {
		if ( !$this->is_correct_post_type_location() ) {
			return;
		}

		foreach ($columns as $column) {
			if ( !is_a($column, 'Carbon_Admin_Column') ) {
				wp_die( 'Object must be of type Carbon_Admin_Column' );
			}

			$column->set_manager( $this );
			$column->init();
		}
	}

	public function is_correct_post_type_location() {
		$post_type = 'post';

		if ( !empty($_GET['post_type']) ) {
			$post_type = $_GET['post_type'];
		}

		return in_array($post_type, $this->get_targets());
	}

	public function get_column_filter_name( $post_type_name ) {
		return 'manage_' . $post_type_name . '_posts_columns';
	}

	public function get_column_filter_content( $post_type_name ) {
		return 'manage_' . $post_type_name . '_posts_custom_column';
	}

	public function get_column_filter_sortable( $post_type_name ) {
		return 'manage_edit-' . $post_type_name . '_sortable_columns';
	}

	public function get_meta_value($object_id, $meta_key) {
		return get_post_meta($object_id, $meta_key, true);
	}
}