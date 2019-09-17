<?php

/* Copyright (c) 2017 Jesús lópez <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\Data\Factory as DataFactory;

/**
 * This implements the textarea input.
 */
class Textarea extends Input implements C\Input\Field\Textarea {
	use JavaScriptBindable;

	protected $max_limit;
	protected $min_limit;

	/**
	 * @inheritdoc
	 */
	public function __construct(
		DataFactory $data_factory,
		\ILIAS\Refinery\Factory $refinery,
		$label,
		$byline
	) {
		parent::__construct($data_factory, $refinery, $label, $byline);
		$this->setAdditionalTransformation(
			$refinery->string()->stripTags()
		);
	}

	/**
	 * set maximum number of characters
	 * @param $max_limit
	 * @return Textarea
	 */
	public function withMaxLimit($max_limit)
	{
		$clone = $this->withAdditionalTransformation(
			$this->refinery->string()->hasMaxLength($max_limit)
		);
		$clone->max_limit = $max_limit;
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
		$clone = $this->withAdditionalTransformation(
			$this->refinery->string()->hasMinLength($min_limit)
		);
		$clone->min_limit = $min_limit;
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
			return $this->refinery->string()->hasMinLength($this->min_limit);
		}
		return $this->refinery->string()->hasMinLength(1);
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

	/**
	 * @inheritdoc
	 */
	public function getUpdateOnLoadCode(): \Closure
	{
		return function ($id) {
			$code = "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
			return $code;
		};
	}
}
