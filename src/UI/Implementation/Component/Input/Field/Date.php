<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Transformation\Factory as TransformationFactory;
use ILIAS\Validation\Factory as ValidationFactory;
use ILIAS\UI\Component\JavaScriptBindable as JSBindabale;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;


/**
 * This implements the date input.
 */
class Date extends Input implements C\Input\Field\Date, JSBindabale {

	use ComponentHelper;
	use JavaScriptBindable;

	/**
	 * @var string
	 */
	protected $format = "MM/DD/YYYY";

	/**
	 * @var \DateTime
	 */
	protected $min_date;

	/**
	 * @var \DateTime
	 */
	protected $max_date;

	/**
	 * @var bool
	 */
	protected $use_time_glyph = false;


	public function __construct(
		DataFactory $data_factory,
		ValidationFactory $validation_factory,
		TransformationFactory $transformation_factory,
		$label,
		$byline
	) {
		parent::__construct($data_factory, $validation_factory, $transformation_factory, $label, $byline);

		$trafo = $transformation_factory->toDate();
		$this->setAdditionalTransformation($trafo);
	}

	/**
	 * @inheritdoc
	 */
	public function withFormat(string $format) : C\Input\Field\Date {
		$this->checkStringArg('format', $format); //2do: check on date-format
		$clone = clone $this;
		$clone->format = $format;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getFormat() : string {
		return $this->format;
	}

	/**
	 * @inheritdoc
	 */
	public function withMinDate(\DateTime $date) : C\Input\Field\Date {
		$clone = clone $this;
		$clone->min_date = $date;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getMinDate() {
		return $this->min_date;
	}

	/**
	 * @inheritdoc
	 */
	public function withMaxDate(\DateTime $date) : C\Input\Field\Date {
		$clone = clone $this;
		$clone->max_date = $date;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getMaxDate() {
		return $this->max_date;
	}

	/**
	 * @inheritdoc
	 */
	public function withTimeGlyph(bool $use_time_glyph) : C\Input\Field\Date {
		$clone = clone $this;
		$clone->use_time_glyph = $use_time_glyph;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getTimeGlyph() : bool {
		return $this->use_time_glyph;
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
