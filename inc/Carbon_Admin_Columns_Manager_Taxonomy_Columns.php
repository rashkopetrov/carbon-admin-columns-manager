<?php 

class Carbon_Admin_Columns_Manager_Taxonomy_Columns extends Carbon_Admin_Columns_Manager {
	
	public function remove($columns_to_remove) {
		$this->columns_to_remove = (array) $columns_to_remove;

		foreach ($this->object_types as $taxonomy) {
			add_filter( 'manage_edit-' . $taxonomy . '_columns' , array($this, 'unset_admin_columns') );
		}

		return $this;
	}

	public function get_column_filter_name( $taxonomy_name ) {
		return 'manage_edit-' . $taxonomy_name . '_columns';
	}
 
	public function get_column_filter_content( $taxonomy_name ) {
		return 'manage_' . $taxonomy_name . '_custom_column';
	}

	public function is_correct_location() {
		$taxonomy_name = 'category';

		if ( !empty($_GET['taxonomy']) ) {
			$taxonomy_name = $_GET['taxonomy'];
		}

		return in_array($taxonomy_name, $this->object_types);
	}

	public function column_callback( $null, $column_name, $object_id ) {
		$this->render_column_value($column_name, $object_id);
	}

	public function get_meta_value( $object_id, $meta_key ) {
		if ( function_exists('carbon_get_term_meta') ) {
			return carbon_get_term_meta($object_id, $meta_key);
		}
	}
}