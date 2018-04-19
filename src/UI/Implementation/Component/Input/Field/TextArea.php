<?php

/* Copyright (c) 2017 Jesús lópez <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;

/**
 * This implements the textarea input.
 */
class TextArea extends Input implements C\Input\Field\TextArea {

	protected $max_limit;
	protected $min_limit;

	/**
	 * set maximum number of characters
	 * @param $max_limit
	 */
	public function withMaxLimit($max_limit)
	{
		$this->setAdditionalConstraint($this->validation_factory->hasMaxLength($max_limit));
		$this->max_limit = $max_limit;
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
	 */
	public function withMinLimit($min_limit)
	{
		$this->setAdditionalConstraint($this->validation_factory->hasMinLength($min_limit));
		$this->min_limit = $min_limit;
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
		return $this->validation_factory->hasMinLength(1);
	}
}
