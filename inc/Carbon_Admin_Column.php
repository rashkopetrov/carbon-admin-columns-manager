<?php 

class Carbon_Admin_Column {

	/**
	 * Column type
	 *
	 * Available options: custom_field | callback
	 *
	 * @see remove()
	 * @var string $type
	 */
	protected $type;

	/**
	 * Contains the column label
	 *
	 * @var string $label
	 */
	protected $label;

	/**
	 * Columns name
	 *
	 * @see set_column_name()
	 * @see get_column_name()
	 * @var string $name
	 */
	protected $name;

	/**
	 * Defines if the column is sortable or not
	 *
	 * @see set_sortable()
	 * @var boolean $is_sortable as first parameter
	 */
	protected $is_sortable = false;

	/**
	 * Default ::: escaped( $label )
	 * $_GET[orderby] = $sortable_key
	 *
	 * @see set_sortable()
	 * @var boolean $sortable_key as second parameter
	 */
	protected $sortable_key;

	/**
	 * An array with the available column contains
	 *
	 * @see verify_column_container()
	 * @var array
	 */
	protected $allowed_containers = array('post_columns', 'taxonomy_columns', 'user_columns' );

	/**
	 * An instance of Main Carbon Columns Container
	 *
	 * @var object $manager
	 */
	protected $manager;

	/**
	 * Contains the targets of the main container
	 *
	 * @var string|array $container_targets
	 */
	protected $container_targets;

	/**
	 * @see set_field()
	 * @see get_field()
	 * @var string $meta_key
	 */
	protected $meta_key = null;

	/**
	 * @see set_callback()
	 * @see get_callback()
	 * @var string $callback_function
	 */
	protected $callback_function = null;

	/**
	 * Instance of Carbon_Admin_Column_Callback_Helper
	 * @see new Carbon_Admin_Column_Callback_Helper()
	 */
	protected $callback_helper = null;

	/**
	 * Defines if is callback
	 * @var boolean $is_callback
	 */
	protected $is_callback = false;

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
		if ( $this->is_callback() && !empty($this->callback_helper) ) {
			return $this->callback_helper->get_field();
		}

		return $this->meta_key;
	}

	public function set_callback($callback_function) {
		$this->callback_function = $callback_function;

		return $this;
	}

	public function get_callback() {
		if ( $this->is_callback() && !empty($this->callback_helper) ) {
			return $this->callback_helper->get_callback();
		}

		return $this->callback_function;
	}

	public function set_column_callback_helper($callback_helper) {
		$this->callback_helper = $callback_helper;

		return $this;
	}

	public function get_column_label() {
		return $this->label;
	}

	public function set_sortable($is_sortable, $sortable_key=null) {
		$this->sortable_key = $sortable_key;
		$this->is_sortable = $is_sortable;

		return $this;
	}

	public function get_sortable_key() {
		$sortable_key = $this->sortable_key;

		if ( !$sortable_key ) {
			$sortable_key = $this->get_column_name();
		}

		return $sortable_key;
	}

	public function is_sortable() {
		return $this->is_sortable;
	}

	public function is_callback() {
		return $this->is_callback===true;
	}

	/**
	 * @see Carbon_Admin_Columns_Manager -> add()
	 */
	public function set_manager( $manager ) {
		$this->manager = $manager;

		return $this;
	}

	public function get_container_type() {
		return $this->manager->get_type();
	}

	public function get_targets() {
		return $this->manager->get_targets();
	}

	public function verify_column_container($container) {
		return in_array($container, $this->allowed_containers);
	}

	/**
	 * Set column column hooks
	 */
	public function init() {
		$targets = $this->get_targets();
		$is_sortable = $this->is_sortable();
		$container_type = $this->get_container_type();

		if ( !$this->verify_column_container($container_type) ) {
			wp_die( 'Unknown column container "' . $container_type . '".' );
		}

		$column_header = array($this, 'init_column_label');
		$column_content = array($this, 'init_' . $container_type . '_callback');
		$column_sortable = array($this, 'init_column_sortable');

		foreach ($targets as $object) {
			$filter_name = $this->manager->get_column_filter_name( $object );
			$filter_content = $this->manager->get_column_filter_content( $object );
			$filter_sortable = $this->manager->get_column_filter_sortable( $object );

			add_filter( $filter_name, $column_header, 15);
			add_action( $filter_content, $column_content, 15, 3);

			if ( $is_sortable ) {
				add_filter( $filter_sortable, $column_sortable );
			}
		}
	}

	public function init_column_label($columns) {
		$columns[ $this->get_column_name() ] 	= $this->get_column_label();

		return $columns;
	}

	public function init_column_sortable($columns) {
		// $columns[ column_name ] 	= sortable_key;
		$columns[ $this->get_column_name() ] 	= $this->get_sortable_key();

		return $columns;  
	}

	public function init_user_columns_callback($null, $column_name, $user_id) {
		return $this->init_column_callback($this->get_column_name(), $user_id);
	}

	public function init_taxonomy_columns_callback($null, $column_name, $term_id) {
		echo $this->init_column_callback($column_name, $term_id);
	}

	public function init_post_columns_callback($column_name, $post_id) {
		echo $this->init_column_callback($column_name, $post_id);
	}

	public function init_column_callback( $column_name, $object_id ) {

		$this->is_callback = true;

		$this_column_name = $this->get_column_name();

		# check if on the right column
		if ( $this_column_name!==$column_name ) {
			return;
		}

		$meta_key = $this->get_field();

		$callback_function_name = $this->get_callback();

		if ( $meta_key && $callback_function_name ) {
			wp_die( 'You can use set_field() or set_callback(), but not both of them.' );
		}

		# Prepare the result
		$results = '';

		if ( !empty($this->callback_helper) ) {
			$this->callback_helper->increase_callback_request_number();

			# prevent multiple callback function calling
			if ( $this->callback_helper->get_callback_request_number()%$this->callback_helper->get_total_columns()!==0 ) {
				return;
			}
		}

		if ( $meta_key ) {
			
			$results = $this->manager->get_meta_value($object_id, $meta_key);

		} else if ($callback_function_name){

			if ( !function_exists($callback_function_name) ) {
				wp_die( 'Missing Carbon Admin Column callback function : "' . $container_type . '".' );
			}

			$results = $callback_function_name( $object_id );
		}

		return $results;
	}
}