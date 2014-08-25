<?php 

/**
 * This class contains the information how many times the callback is being hit
 * It's required in cases such as User Columns where the value "returns" instead "echoes"
 */
class Carbon_Admin_Column_Callback_Helper {

	// stores the number of that how many times the callback has been executed
	protected $callback_request_number = 0;

	protected $columns = array();

	function __construct() {

	}

	public function increase_callback_request_number() {
		$this->callback_request_number++;

		if ( $this->callback_request_number===pow($this->get_total_columns(), 2) ) {
			$this->callback_request_number = 0;
		}

		return $this;
	}

	public function get_callback_request_number() {
		return $this->callback_request_number;
	}

	public function get_index() {
		return floor( $this->get_callback_request_number() / $this->get_total_columns() );
	}

	public function set_columns($columns) {
		$this->columns = $columns;

		return $this;
	}

	public function get_columns() {
		return $this->columns;
	}

	public function get_total_columns() {
		return count($this->get_columns());
	}

	public function get_field() {
		$columns = $this->get_columns();
		$column_index = $this->get_index();
		
		return $columns[ $column_index ][ 'meta_key' ];
	}

	public function get_callback() {
		$columns = $this->get_columns();
		$column_index = $this->get_index();

		return $columns[ $column_index ][ 'callback_function' ];
	}
}