<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Signal;

/**
 * This implements the text input.
 */
class Text extends Input implements C\Input\Field\Text {
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
		$this->setAdditionalTransformation($refinery->custom()->transformation(function($v) {
			return strip_tags($v);
		}));
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
		return $this->refinery->string()->hasMinLength(1);
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
