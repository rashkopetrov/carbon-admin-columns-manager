<?php
 
class Carbon_Admin_Columns_Manager_Post extends Carbon_Admin_Columns_Manager {

	public function columns_modifier() {
		foreach ($this->object_types as $object_type) {
			add_filter( 'manage_edit-' . $object_type . '_columns' , array($this, 'modify_admin_columns'), 16 );
		}
	}

	public function get_column_filter_name( $post_type_name ) {
		return 'manage_edit-' . $post_type_name . '_columns';
	}
 
	public function get_column_filter_content( $post_type_name ) {
		return 'manage_' . $post_type_name . '_posts_custom_column';
	}

	public function is_correct_location() {
		$post_type = 'post';

		if ( !empty($_GET['post_type']) ) {
			$post_type = $_GET['post_type'];
		}

		return in_array($post_type, $this->object_types);
	}

	public function column_callback( $column_name, $object_id ) {
		$this->render_column_value($column_name, $object_id);
	}

	public function get_meta_value( $object_id, $meta_key ) {
		return get_post_meta($object_id, $meta_key, true);
	}
}