<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Predicates;

/**
 * An atom in a predicate that acts like a value.
 */
class ValueLike {
	/**
	 * @var	\ILIAS\TMS\Filter\PredicateFactory
	 */
	protected $factory;

	protected function setFactory(\ILIAS\TMS\Filter\PredicateFactory $factory) {
		$this->factory = $factory;
	}

	protected function fluent_factory(\Closure $continue) {
		return new FluentPredicateAtomFactory($continue, $this->factory);
	}

	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function EQ(ValueLike $other = null) {
		if ($other !== null) {
			return $this->factory->EQ($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(ValueLike $value) use ($self) {
			return $self->EQ($value);
		});
	}

	public function NEQ(ValueLike $other = null) {
		if ($other !== null) {
			return $this->factory->NEQ($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(ValueLike $value) use ($self) {
			return $self->NEQ($value);
		});
	}

	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function LT(ValueLike $other = null) {
		if ($other !== null) {
			return $this->factory->LT($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(ValueLike $value) use ($self) {
			return $self->LT($value);
		});
	}

	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function LE(ValueLike $other = null) {
		if ($other !== null) {
			return $this->factory->LE($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(ValueLike $value) use ($self) {
			return $self->LE($value);
		});
	}

	public function GE(ValueLike $other = null) {
		if ($other !== null) {
			return $this->factory->GE($this, $other);
		}
		$self = $this;
		return $this->fluent_factory(function(ValueLike $value) use ($self) {
			return $self->GE($value);
		});
	}

	public function GT(ValueLike $other = null) {
		if ($other !== null) {
			return $this->factory->GT($this, $other);
		}
		$self = $this;
		return $this->fluent_factory(function(ValueLike $value) use ($self) {
			return $self->GT($value);
		});
	}

	public function IN(ValueList $list =  null) {
		if ($list !== null) {
			return $this->factory->IN($this, $list);
		}	
		$self = $this;
		return $this->fluent_factory(function(ValueList $list) use ($self) {
			return $self->IN($list);
		});
	}

	public function IS_NULL() {
		return $this->factory->IS_NULL($this);
	}

	public function LIKE(ValueLike $val = null) {
		if($val !== null) {
			return $this->factory->LIKE($this,$val);
		}
		$self = $this;
		return $this->fluent_factory(
			function(ValueLike $value) use ($self) {
				return $self->LIKE($value);
			}
		);
	}
}
