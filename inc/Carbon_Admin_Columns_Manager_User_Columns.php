<?php 

class Carbon_Admin_Columns_Manager_User_Columns extends Carbon_Admin_Columns_Manager {

	public function remove($columns_to_remove) {
		$this->columns_to_remove = (array) $columns_to_remove;
		
		add_action('manage_users_columns', array($this, 'unset_admin_columns'));

		return $this;
	}

	public function is_correct_location() {
		// There aren't object types for users ... 
		return false;
	}

	public function add( $columns=0 ) {
		foreach ($columns as $column_index => $column) {
			if ( !is_a($column, 'Carbon_Admin_Column') ) {
				wp_die( 'Object must be of type Carbon_Admin_Column' );
			}

			$this->columns_objects[ $column->get_column_name() ] = $column;

			// Filter the columns list
			add_filter(
				'manage_users_columns',
				array($column, 'register_column'),
				15
			);

			// Filter the columns content for each row
			add_action(
				'manage_users_custom_column',
				array($this, 'column_callback'),
				15,
				3
			);

			if ( $column->sort_field ) {
				// If necessary, filter sortable flags. 
				add_filter(
					'manage_users_sortable_columns',
					array($column, 'init_column_sortable')
				);
			}
		}
	}

	/**
	 * Column callback for users screen is a little bit different than posts and taxonomy ones:
	 *   - it should return value instead of print it
	 *   - it should return default value when the currently looped column doesn't
	 *     match with the object's registered column
	 * @param  string $default     The default value for that column
	 * @param  string $column_name 
	 * @param  int $object_id   
	 * @return string
	 */
	public function column_callback( $default, $column_name, $object_id ) {
		if ( !isset($this->columns_objects[ $column_name ]) ) {
			// Users columns require callback to return the default value
			// whenever the column doesn't match with the looped one
			return $default;
		}

		return $this->get_column_value($column_name, $object_id);
	}

	public function get_meta_value( $user_id, $meta_key ) {
		return get_user_meta($user_id, $meta_key, true);
	}
}