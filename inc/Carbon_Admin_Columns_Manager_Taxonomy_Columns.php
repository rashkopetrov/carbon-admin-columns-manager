<?php 

class Carbon_Admin_Columns_Manager_Taxonomy_Columns extends Carbon_Admin_Columns_Manager {

	public function remove($columns_to_remove) {
		$this->columns_to_remove = (array) $columns_to_remove;

		$targets = $this->get_targets();
		foreach ($targets as $taxonomy) {
			add_filter( 'manage_edit-' . $taxonomy . '_columns' , array($this, 'unset_admin_columns') );
		}

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
		$taxonomy_name = 'category';

		if ( !empty($_GET['taxonomy']) ) {
			$taxonomy_name = $_GET['taxonomy'];
		}

		return in_array($taxonomy_name, $this->get_targets());
	}

	public function get_column_filter_name( $taxonomy_name ) {
		return 'manage_edit-' . $taxonomy_name . '_columns';
	}

	public function get_column_filter_content( $taxonomy_name ) {
		return 'manage_' . $taxonomy_name . '_custom_column';
	}

	public function get_column_filter_sortable( $taxonomy_name ) {
		return 'manage_edit-' . $taxonomy_name . '_sortable_columns';
	}

	public function get_meta_value($object_id, $meta_key) {
		if ( function_exists('carbon_get_term_meta') ) {
			return carbon_get_term_meta($object_id, $meta_key);
		} else {
			return;
		}
	}
}