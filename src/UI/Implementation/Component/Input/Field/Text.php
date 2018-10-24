<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * This implements the text input.
 */
class Text extends Input implements C\Input\Field\Text {
	use JavaScriptBindable;
	use Triggerer;

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

	/**
	 * @inheritdoc
	 */
	public function getUpdateOnLoadCode(): \Closure
	{
		return function ($id) {
			$code = "$('#$id').on('input', function(event) {
				il.UI.filter.handleChange(event, '$id', $('#$id').val());
			});";
			return $code;
		};
	}
}
