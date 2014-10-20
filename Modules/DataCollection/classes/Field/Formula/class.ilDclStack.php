<?php

/**
 * Class ilDclStack
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDclStack {

	/**
	 * @var array
	 */
	protected $stack = array();


	/**
	 * @param $elem
	 */
	public function push($elem) {
		$this->stack[] = $elem;
	}


	/**
	 * @return null
	 */
	public function pop() {
		if (!$this->isEmpty()) {
			$last_index = count($this->stack) - 1;
			$elem = $this->stack[$last_index];
			unset($this->stack[$last_index]);
			$this->stack = array_values($this->stack); // re-index
			return $elem;
		}

		return NULL;
	}


	/**
	 * @return null
	 */
	public function top() {
		if (!$this->isEmpty()) {
			return $this->stack[count($this->stack) - 1];
		}

		return NULL;
	}


	/**
	 * @return bool
	 */
	public function isEmpty() {
		return !(bool)count($this->stack);
	}


	public function reset() {
		$this->stack = array();
	}


	/**
	 * @return int
	 */
	public function count() {
		return count($this->stack);
	}


	public function debug() {
		echo "<pre>" . print_r($this->stack, 1) . "</pre>";
	}
}

?>
