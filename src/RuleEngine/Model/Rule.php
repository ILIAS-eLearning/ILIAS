<?php

namespace ILIAS\RuleEngine\Model;

use ILIAS\Visitor\Element;


use ILIAS\RuleEngine\Context\Context;


class Model implements Element
{
	/**
	 * Get the expression.
	 *
	 * @return  \Hoa\Ruler\Model\Operator
	 */
	public function getExpression()
	{
		return $this->_root;
	}


	/**
	 * Returns a list of accessed variables.
	 *
	 * @return \Hoa\Ruler\Model\Bag\Context[]
	 */
	public function getAccesses()
	{
		$visitor = new Visitor\AccessCollectorVisitor();

		return $visitor->visit($this);
	}

	/**
	 * Returns a list of used operators.
	 *
	 * @return \Hoa\Ruler\Model\Operator[]
	 */
	public function getOperators()
	{
		$visitor = new Visitor\OperatorCollectorVisitor();

		return $visitor->visit($this);
	}

	/**
	 * Returns a list of used parameters.
	 *
	 * @return \RulerZ\Model\Parameter[]
	 */
	public function getParameters()
	{
		$visitor = new Visitor\ParameterCollectorVisitor();

		return $visitor->visit($this);
	}

	/**
	 * Root.
	 *
	 * @var \Hoa\Ruler\Model\Operator
	 */
	protected $_root            = null;

	/**
	 * Compiler.
	 *
	 * @var \Hoa\Ruler\Visitor\Compiler
	 */
	protected static $_compiler = null;



	/**
	 * Set the expression with $name = 'expression'.
	 *
	 * @param   string  $name     Name.
	 * @param   mixed   $value    Value.
	 * @return  void
	 */
	public function __set($name, $value)
	{
		if ('expression' !== $name) {
			return $this->$name = $value;
		}

		if (is_scalar($value)) {
			$value = new Bag\Scalar($value);
		} elseif (is_array($value)) {
			$value = new Bag\RulerArray($value);
		}

		$this->_root = $value;

		return;
	}



	/**
	 * Declare a function.
	 *
	 * @param   string  $name         Name.
	 * @param   mixed   …
	 * @return  \Hoa\Ruler\Model\Operator
	 */
	public function func()
	{
		$arguments = func_get_args();
		$name      = array_shift($arguments);

		return $this->_operator($name, $arguments, true);
	}

	/**
	 * Declare an operation.
	 *
	 * @param   string  $name         Name.
	 * @param   array   $arguments    Arguments.
	 * @return  \Hoa\Ruler\Model\Operator
	 */
	public function operation($name, array $arguments)
	{
		return $this->_operator($name, $arguments, false);
	}

	/**
	 * Create an operator object.
	 *
	 * @param   string  $name          Name.
	 * @param   array   $arguments     Arguments.
	 * @param   bool    $isFunction    Whether it is a function or not.
	 * @return  \Hoa\Ruler\Model\Operator
	 */
	public function _operator($name, array $arguments, $isFunction)
	{
		return new Operator(mb_strtolower($name), $arguments, $isFunction);
	}

	/**
	 * Declare an operation.
	 *
	 * @param   string  $name         Name.
	 * @param   array   $arguments    Arguments.
	 * @return  \Hoa\Ruler\Model\Operator
	 */
	public function __call($name, array $arguments)
	{
		return $this->operation($name, $arguments);
	}

	/**
	 * Declare a variable.
	 *
	 * @parma   string  $id    ID.
	 * @return  \Hoa\Ruler\Model\Bag\Context
	 */
	public function variable($id)
	{
		return new Bag\Context($id);
	}

	/**
	 * Accept a visitor.
	 *
	 * @param   \Hoa\Visitor\Visit  $visitor    Visitor.
	 * @param   mixed               &$handle    Handle (reference).
	 * @param   mixed               $eldnah     Handle (no reference).
	 * @return  mixed
	 */
	public function accept(
		Visitor\Visit $visitor,
		&$handle = null,
		$eldnah  = null
	) {
		return $visitor->visit($this, $handle, $eldnah);
	}

	/**
	 * Transform the object as a string.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		if (null === static::$_compiler) {
			static::$_compiler = new Ruler\Visitor\Compiler();
		}

		return static::$_compiler->visit($this);
	}
}
