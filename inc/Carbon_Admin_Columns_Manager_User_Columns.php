<?php 

class Carbon_Admin_Columns_Manager_User_Columns extends Carbon_Admin_Columns_Manager {

	public function remove($columns_to_remove) {
		$this->columns_to_remove = (array) $columns_to_remove;
		
		add_action('manage_users_columns',array($this, 'unset_admin_columns'));

		return $this;
	}

	public function add( $columns ) {

		$this->targets = array('users');

		$defined_columns = array();
		$callback_helper = new Carbon_Admin_Column_Callback_Helper();

		foreach ($columns as $column_index => $column) {
			if ( !is_a($column, 'Carbon_Admin_Column') ) {
				wp_die( 'Object must be of type Carbon_Admin_Column' );
			}

			$column->set_manager( $this );
			$column->set_column_callback_helper( $callback_helper );
			$column->init();

			$defined_columns[$column_index] = array(
				'column_name' => $column->get_column_name(),
				'meta_key' => $column->get_field(),
				'callback_function' => $column->get_callback()
			);
		}

		$callback_helper->set_columns($defined_columns);
	}

	public function get_column_filter_name( $null ) {
		return 'manage_users_columns';
	}

	public function get_column_filter_content( $null ) {
		return 'manage_users_custom_column';
	}

	public function get_column_filter_sortable( $null ) {
		return 'manage_users_sortable_columns';
	}

	public function get_meta_value($object_id, $meta_key) {
		return get_user_meta($object_id, $meta_key, true);
	}
}