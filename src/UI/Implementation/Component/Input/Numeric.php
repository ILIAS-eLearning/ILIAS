<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;
use ILIAS\Validation\Factory as ValidationFactory;

/**
 * This implements commonalities between inputs.
 */
class Numeric extends Input implements C\Input\Numeric {

	/**
	 * Numeric constructor.
	 * @param DataFactory $data_factory
	 * @param $label
	 * @param $byline
	 */
	public function __construct(DataFactory $data_factory, $label, $byline) {

		parent::__construct($data_factory, $label, $byline);

		$validation_factory = new ValidationFactory($this->data_factory);

		//TODO: Is there a better way to do this? Note, that "withConstraint" is not
		// usable here (clone).
		$this->operations[] = $validation_factory->isNumeric();
	}


	/**
	 * @inheritdoc
	 */
	protected function isClientSideValueOk($value) {
		return is_string($value);
	}
}
