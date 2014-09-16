<?php

/**
 * Plugin Name: CRB Columns Manager
 */
abstract class Carbon_Admin_Columns_Manager {
	
	/**
	 * @var array
	 */
	public $object_types = array();

	/**
	 * Sepcify columns for removal.
	 * The value might be a string or an array with columns
	 *
	 * @see remove()
	 * @var array|string $columns_to_remove
	 */
	protected $columns_to_remove;

	protected $columns_objects = array();

	static function modify_post_type_columns( $post_types ) {
		return new Carbon_Admin_Columns_Manager_Post_Columns($post_types);
	}

	static function modify_users_columns() {
		return new Carbon_Admin_Columns_Manager_User_Columns();
	}

	static function modify_taxonomy_columns( $taxonomies ) {
		return new Carbon_Admin_Columns_Manager_Taxonomy_Columns($taxonomies);
	}

	private function __construct($object_types=array()) {
		$this->object_types = (array) $object_types;
	}

	public function unset_admin_columns($columns) {
		foreach ( $this->columns_to_remove as $column_name ) {
			unset( $columns[$column_name] );
		}

		return $columns;
	}

	abstract public function is_correct_location();
	/* abstract public function column_callback(various args ... );*/

	public function add( $columns ) {
		if ( !$this->is_correct_location() ) {
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
					$this->get_column_filter_name( $object_type ),
					array($column, 'register_column'),
					15
				);

				// Filter the columns content for each row
				add_action(
					$this->get_column_filter_content( $object_type ),
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

	public function render_column_value($looped_column_name, $object_id) {
		echo $this->get_column_value($looped_column_name, $object_id);
	}

	public function get_column_value($looped_column_name, $object_id) {
		if ( !isset($this->columns_objects[ $looped_column_name ]) ) {
			return;
		}

		$column = $this->columns_objects[ $looped_column_name ];

		# check whether this is the right column
		if ( $column->get_column_name() !== $looped_column_name ) {
			return;
		}

		$callback = $column->get_callback();
		$results = '';

		if ( is_callable($callback) ) {
			$results = call_user_func($callback, $object_id);
		} else {
			// Fallback to meta value whenever callback is not set
			$results = $this->get_meta_value($object_id, $column->get_field());
		}

		return $results;
	}
}