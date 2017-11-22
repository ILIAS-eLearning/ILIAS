<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Predicates;

/**
 * A predicate is some abstract function from some record (like a dictionary,
 * a row in a table) to true or false.
 */
abstract class Predicate {
	/**
	 * ToDo: This should be private and the other factory-members as well.
	 *
	 * @var	\ILIAS\TMS\Filter\PredicateFactory
	 */
	protected $factory;
	protected $fields;

	protected function setFactory(\ILIAS\TMS\Filter\PredicateFactory $factory) {
		$this->factory = $factory;
	}

	protected function fluent_factory(\Closure $continue) {
		return new FluentPredicateFactory($continue, $this->factory);
	}

	/**
	 * Get all fields that are used in this predicate.
	 *
	 * @return	ilField[]
	 */
	abstract public function fields();

	protected function addPossibleFieldsToFields ( array $poss_fields, array $fields) {
		foreach ($poss_fields as $poss_field) {
			if($poss_field instanceof \ILIAS\TMS\Filter\Predicates\Field) {
				$fields = $this->addFieldToFields($poss_field, $fields);
			}
		}
		return $fields;
	} 

	/**
	* Check, whether @var val is field and is not contained in @var fields allready and add it to @return fields.
	*/
	protected function addFieldToFields ( Field $field, array $fields) {
		if(!$this->fieldInFieldList($field,$fields)) {
			$fields[] = $field;
		}
		return $fields;
	}

	/**
	* @return (bool)is @var field contained in @var $fields, which is an array of fields? 
	*/

	protected function fieldInFieldList(Field $field ,array $fields) {
		$field_name = $field->name();
		foreach($fields as $field_el) {
			if($field_name === $field_el->name()) {
				return true;
			}
		}
		return false;
	}


	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function _OR(Predicate $other = null) {
		if ($other !== null) {
			return $this->factory->_OR($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(Predicate $pred) use ($self) {
			return $self->_OR($pred);
		});
	}

	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function _AND(Predicate $other = null) {
		if ($other !== null) {
			return $this->factory->_AND($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(Predicate $pred) use ($self) {
			return $self->_AND($pred);
		});
	}

	public function _NOT() {
		return $this->factory->_NOT($this);
	}
}
