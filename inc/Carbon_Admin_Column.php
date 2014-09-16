<?php 

class Carbon_Admin_Column {
	/**
	 * Column Label
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * Column name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The field that will be used for ordeing the posts. Null value will
	 * disable the ordering capability.
	 * 
	 * @var string $sort_field
	 */
	public $sort_field;

	/**
	 * @var string $meta_key
	 */
	public $meta_key = null;

	/**
	 * Callback that will be used for rendering columns 
	 * values in WP admin listing screen. By default, this 
	 * will print  custom field value associated with 
	 * the column name.
	 */
	public $callback;

	static function create($label, $name = null) {
		if ( !$label ) {
			wp_die( 'Column label is required.' );
		}

		return new self($label, $name);
	}

	private function __construct($label, $name) {
		$this->label = $label;

		if ( empty($name) ) {
			$name = 'carbon-' . preg_replace('~[^a-zA-Z0-9.]~', '', $label);
		}
		$this->set_column_name($name);

		return $this;
	}

	public function set_column_name($name) {
		$this->name = $name;

		return $this;
	}

	public function get_column_name() {
		return $this->name;
	}

	public function set_field($meta_key) {
		$this->meta_key = $meta_key;

		return $this;
	}

	public function get_field() {
		return $this->meta_key;
	}

	public function set_callback($callback) {
		if ( !is_callable($callback) ) {
			trigger_error( "Callback must be callable function. ", E_USER_WARNING);
			return false;
		}

		$this->callback = $callback;

		return $this;
	}

	public function get_callback() {
		return $this->callback;
	}

	public function get_column_label() {
		return $this->label;
	}

	public function set_sort_field($sort_field=null) {
		$this->sort_field = $sort_field;

		return $this;
	}

	public function get_sort_field() {
		$sort_field = $this->sort_field;

		if ( !$sort_field ) {
			$sort_field = $this->get_column_name();
		}

		return $sort_field;
	}

	public function is_callback() {
		return $this->is_callback===true;
	}

	/**
	 * Add this column to registered columns
	 * @param array $columns Columns registered so far
	 */
	public function register_column($columns) {
		$columns[ $this->name ] = $this->label;

		return $columns;
	}

	public function init_column_sortable($columns) {
		$columns[ $this->get_column_name() ] = $this->sort_field;

		return $columns;  
	}
}