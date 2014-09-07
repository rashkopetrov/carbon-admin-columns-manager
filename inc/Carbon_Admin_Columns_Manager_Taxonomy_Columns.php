<?php 

class Carbon_Admin_Columns_Manager_Taxonomy_Columns extends Carbon_Admin_Columns_Manager {
	
	public function remove($columns_to_remove) {
		$this->columns_to_remove = (array) $columns_to_remove;

		foreach ($this->object_types as $taxonomy) {
			add_filter( 'manage_edit-' . $taxonomy . '_columns' , array($this, 'unset_admin_columns') );
		}

		return $this;
	}

	public function add( $columns ) {
		if ( !$this->is_correct_taxonomy_location() ) {
			return;
		}

		foreach ($columns as $column) {
			if ( !is_a($column, 'Carbon_Admin_Column') ) {
				wp_die( 'Object must be of type Carbon_Admin_Column' );
			}

			$this->columns_objects[ $column->get_column_name() ] = $column;

			foreach ($this->object_types as $object_type) {
				// Filter the columns list
				add_filter(
					'manage_edit-' . $object_type . '_columns',
					array($column, 'register_column'),
					15
				);

				// Filter the columns content for each row
				add_action(
					'manage_' . $object_type . '_custom_column',
					array($this, 'column_callback'),
					15,
					3
				);

				if ( $column->sort_field ) {
					// If necessary, filter sortable flags. 
					add_filter(
						'manage_edit-' . $object_type . '_sortable_columns',
						array($column, 'init_column_sortable')
					);
				}
			}
		}
	}

	public function is_correct_taxonomy_location() {
		$taxonomy_name = 'category';

		if ( !empty($_GET['taxonomy']) ) {
			$taxonomy_name = $_GET['taxonomy'];
		}

		return in_array($taxonomy_name, $this->object_types);
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
			if ( function_exists('carbon_get_term_meta') ) {
				$results = carbon_get_term_meta($object_id, $column->get_field());
			}
		} else {
			$results = call_user_func($column->get_callback(), $object_id);
		}

		echo $results;
	}
}