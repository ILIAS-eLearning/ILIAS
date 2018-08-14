<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Transformation\Factory as TransformationFactory;
use ILIAS\Validation\Factory as ValidationFactory;
use ILIAS\UI\Implementation\Component\ComponentHelper;


/**
 * This implements the duration input group.
 */
class Duration extends Group implements C\Input\Field\Duration {

	use ComponentHelper;

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
		Factory $field_factory,
		$label,
		$byline
	) {

		$inputs = [
			$field_factory->date('start'),
			$field_factory->date('end')
		];

		parent::__construct($data_factory, $validation_factory, $transformation_factory, $inputs, $label, $byline);

		$duration = $transformation_factory->custom(function($v) {
			list($from, $until) = $v;
			return ['start'=>$from, 'end'=>$until, 'interval'=>$from->diff($until)];
		});

		$from_before_until = $validation_factory->custom(function($v) {
			return $v['start'] < $v['end'];
		}, "'from' must be before 'until'");

		$this->setAdditionalTransformation($duration);
		$this->setAdditionalConstraint($from_before_until);
	}

	/**
	 * @inheritdoc
	 */
	public function withFormat(string $format) : C\Input\Field\Duration {
		$this->checkStringArg('format', $format); //2do: check on date-format
		$clone = clone $this;
		$clone->format = $format;
		$clone->applyFormat();
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getFormat() : string {
		return $this->format;
	}

	/**
	 * apply format to inputs
	 */
	protected function applyFormat() {
		$this->inputs = array_map(
			function($inpt) {
				return $inpt->withFormat($this->getFormat());
			},
			$this->inputs
		);
	}

	/**
	 * @inheritdoc
	 */
	public function withMinDate(\DateTime $date) : C\Input\Field\Duration {
		$clone = clone $this;
		$clone->min_date = $date;
		$clone->applyMinDate();
		return $clone;
	}

	/**
	 * apply format to inputs
	 */
	protected function applyMinDate() {
		$this->inputs = array_map(
			function($inpt) {
				return $inpt->withMinDate($this->getMinDate());
			},
			$this->inputs
		);
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
	public function withMaxDate(\DateTime $date) : C\Input\Field\Duration {
		$clone = clone $this;
		$clone->max_date = $date;
		$clone->applyMaxDate();
		return $clone;
	}

	/**
	 * apply format to inputs
	 */
	protected function applyMaxDate() {
		$this->inputs = array_map(
			function($inpt) {
				return $inpt->withMaxDate($this->getMaxDate());
			},
			$this->inputs
		);
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
	public function withTimeGlyph(bool $use_time_glyph) : C\Input\Field\Duration {
		$clone = clone $this;
		$clone->use_time_glyph = $use_time_glyph;
		$clone->applyTimeGlyph();
		return $clone;
	}

	/**
	 * apply format to inputs
	 */
	protected function applyTimeGlyph() {
		$this->inputs = array_map(
			function($inpt) {
				return $inpt->withTimeGlyph($this->getTimeGlyph());
			},
			$this->inputs
		);
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
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function getConstraintForRequirement() {
		return true;
	}
}
