<?php 

class Carbon_Admin_Columns_Manager_User_Columns extends Carbon_Admin_Columns_Manager {

	public function remove($columns_to_remove) {
		$this->columns_to_remove = (array) $columns_to_remove;
		
		add_action('manage_users_columns', array($this, 'unset_admin_columns'));

		return $this;
	}

	public function add( $columns ) {
		$this->object_types = array('users');

		foreach ($columns as $column_index => $column) {
			if ( !is_a($column, 'Carbon_Admin_Column') ) {
				wp_die( 'Object must be of type Carbon_Admin_Column' );
			}

			$this->columns_objects[ $column->get_column_name() ] = $column;

			foreach ($this->object_types as $object_type) {
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
	}

	public function column_callback( $null, $column_name, $object_id ) {
		if ( !isset($this->columns_objects[ $column_name ]) ) {
			return;
		}

		$column = $this->columns_objects[ $column_name ];

		$this_column_name = $column->get_column_name();

		# check whether this is the right column
		if ( $this_column_name !== $column_name ) {
			return;
		}

		$callback_type = $column->get_callback();
		$results = '';

		if ( $callback_type==='get_meta_value' ) {
			$results = get_user_meta($object_id, $column->get_field(), true);
		} else {
			$results = call_user_func($column->get_callback(), $object_id);
		}

		return $results;
	}
}