<?php

abstract class Carbon_Admin_Columns_Manager {
	
	/**
	 * @var array
	 */
	public $object_types = array();

	/**
	 * Specify columns for removal.
	 * The value might be a string or an array with columns
	 *
	 * @see remove()
	 * @var array|string $columns_to_remove
	 */
	protected $columns_to_remove = array();

	protected $sorted_columns = array();

	protected $columns_objects = array();

	static function modify_columns($type, $object_types = null) {
		$type = str_replace(" ", '_', ucwords(str_replace("_", ' ', $type)));
		$class = 'Carbon_Admin_Columns_Manager_' . $type;

		if (!class_exists($class)) {
			wp_die( 'Invalid columns manager type: "' . $type . '".');
		}

		return new $class($object_types);
	}

	private function __construct($object_types=array()) {
		$this->object_types = (array) $object_types;
	}

	public function remove( $columns_to_remove ) {
		$this->columns_to_remove = (array) $columns_to_remove;
		
		$this->columns_modifier();

		return $this;
	}

	public function sort( $sorted_columns ) {
		$this->sorted_columns = array_reverse( (array) $sorted_columns );
		
		$this->columns_modifier();

		return $this;
	}

	public function modify_admin_columns($columns) {

		# remove unnecessary columns
		foreach ( $this->columns_to_remove as $column_name ) {
			unset( $columns[$column_name] );
		}

		# reorder the column
		foreach ( $this->sorted_columns as $column_name ) {
			if ( empty($columns[$column_name]) ) {
				continue;
			}

			$column_to_move = $columns[$column_name];
			unset( $columns[$column_name] );

			$columns = array( $column_name => $column_to_move ) + $columns;
		}

		return $columns;
	}

	abstract public function is_correct_location();

	abstract public function columns_modifier();

	public function add( $columns ) {
		if ( !$this->is_correct_location() ) {
			return $this;
		}

		foreach ($columns as $column) {
			if ( !is_a($column, 'Carbon_Admin_Column') ) {
				wp_die( 'Object must be of type "Carbon_Admin_Column"' );
			}

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

			$this->columns_objects[ $column->get_name() ] = $column;
		}

		# include additional CSS code to the admin header to manage the columns width
		add_action( 'admin_head', array($this, 'admin_head') );

		return $this;
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
		if ( $column->get_name() !== $looped_column_name ) {
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

	public function admin_head() {
		$css = '';

		foreach ($this->columns_objects as $column) {
			$column_width = $column->get_width();

			if ( !$column_width ) {
				continue;
			}

			$column_name = $column->get_name();

			$css .= "#{$column_name} { width : {$column_width}; }";
		}

		if ( $css ) {
			?>
			<!-- Added by Carbon Admin Columns Manager - START -->
			<style type="text/css">
				<?php echo $css ?>
			</style>
			<!-- Added by Carbon Admin Columns Manager - END -->
			<?php
		}
	}
}