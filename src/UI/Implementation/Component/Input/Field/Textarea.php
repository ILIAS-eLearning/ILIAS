<?php

/* Copyright (c) 2017 Jesús lópez <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

/**
 * This implements the textarea input.
 */
class Textarea extends Input implements C\Input\Field\Textarea {

	use JavaScriptBindable;

	protected $max_limit;
	protected $min_limit;

	/**
	 * set maximum number of characters
	 * @param $max_limit
	 * @return Textarea
	 */
	public function withMaxLimit($max_limit)
	{
		$clone = clone $this;
		$clone->max_limit = $max_limit;
		$clone->setAdditionalConstraint($this->validation_factory->hasMaxLength($max_limit));
		return $clone;
	}

	/**
	 * get maximum limit of characters
	 * @return mixed
	 */
	public function getMaxLimit()
	{
		return $this->max_limit;
	}

	/**
	 * set minimum number of characters
	 * @param $min_limit
	 * @return Textarea
	 */
	public function withMinLimit($min_limit)
	{
		$clone = clone $this;
		$clone->min_limit = $min_limit;
		$clone->setAdditionalConstraint($this->validation_factory->hasMinLength($min_limit));
		return $clone;
	}

	/**
	 * get minimum limit of characters
	 * @return mixed
	 */
	public function getMinLimit()
	{
		return $this->min_limit;
	}

	/**
	 * @inheritdoc
	 */
	protected function isClientSideValueOk($value) {
		return is_string($value);
	}


	/**
	 * @inheritdoc
	 */
	protected function getConstraintForRequirement() {
		if($this->min_limit) {
			return $this->validation_factory->hasMinLength($this->min_limit);
		}
		return $this->validation_factory->hasMinLength(1);
	}

	/**
	 * @inheritdoc
	 */
	public function isLimited()
	{
		if($this->min_limit || $this->max_limit)
		{
			return true;
		}
		return false;
	}
}
