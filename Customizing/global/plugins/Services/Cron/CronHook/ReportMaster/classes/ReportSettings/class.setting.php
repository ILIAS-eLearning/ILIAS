<?php

/**
 *	A setting is a bootstrapping values container to be used for rendering of
 *	report setting GUI and database communication. Members id and name
 *	are obligatory and may be not reset after instantiation. Other values 
 *	are supposed to have some default fallback values, may be overwritten though.	
 */
abstract class setting {

	protected $id;
	protected $name;
	protected $default_value;
	protected $to_form;
	protected $from_form;

	public function __construct($id, $name) {
		$this->id = $id;
		$this->name = $name;
		$this->default_value = $this->defaultDefaultValue();
		$this->to_form = $this->defaultToForm();
		$this->from_form = $this->defaultFromForm();
	}

	/**
	 * @return	string|int	default_value for default_value
	 */
	abstract protected function defaultDefaultValue();

	/**
	 * @return	closure	default_to_form for default_value
	 */
	abstract protected function defaultToForm();

	/**
	 * @return	closure	default_from_form for default_value
	 */
	abstract protected function defaultFromForm();

	/**
	 * will be defined during instantiation
	 * @return	string	id	of current setting 
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * will be defined during instantiation
	 * @return	string|int	name	of current setting 
	 */
	public function name() {
		return $this->name;
	}

	/**
	 * @return	string|int	default_value	of current setting may be reset
	 */
	public function defaultValue() {
		return $this->default_value;
	}

	/**
	 * @return	closure	postprocessing	function of current setting may be reset
	 */
	public function toForm() {
		return $this->to_form;
	}

	/**
	 * @return	closure	postprocessing	function of current setting may be reset
	 */
	public function fromForm() {
		return $this->from_form;
	}

	/**
	 * @param	string|int	default_value to be set for current setting 
	 * @return	setting	this
	 */
	public function setDefaultValue($default_value) {
		$this->default_value = $default_value;
		return $this;
	}

	/**
	 * @param	closure	preprocessing to be set for current setting
	 * @return	setting	this
	 */
	public function setToForm(closure $closure) {
		$this->to_form = $closure;
		return $this;
	}

	/**
	 * @param	closure	preprocessing to be set for current setting
	 * @return	setting	this
	 */
	public function setFromForm(closure $closure) {
		$this->from_form = $closure;
		return $this;
	}
}