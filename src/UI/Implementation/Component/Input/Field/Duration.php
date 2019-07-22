<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\DateFormat as DateFormat;
use ILIAS\Transformation\Factory as TransformationFactory;
use ILIAS\Validation\Factory as ValidationFactory;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\JavaScriptBindable as JSBindabale;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Input\Field\DateTime as DTField;

/**
 * This implements the duration input group.
 */
class Duration extends Group implements C\Input\Field\Duration, JSBindabale {

	use ComponentHelper;
	use JavaScriptBindable;

	/**
	 * @var DateFormat
	 */
	protected $format;

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
	protected $with_time = false;

	/**
	 * @var bool
	 */
	protected $with_time_only = false;

	/**
	 * @var string
	 */
	protected $timezone;

	/**
	 * @var TransformationFactory
	 */
	protected $transformation_factory;

	public function __construct(
		DataFactory $data_factory,
		ValidationFactory $validation_factory,
		TransformationFactory $transformation_factory,
		Factory $field_factory,
		$label,
		$byline
	) {

		$inputs = [
			$field_factory->dateTime('start'),
			$field_factory->dateTime('end')
		];

		parent::__construct($data_factory, $validation_factory, $transformation_factory, $inputs, $label, $byline);
		$this->addTransformation($transformation_factory);
		$this->addValidation($validation_factory);
		$this->validation_factory = $validation_factory;
		$this->transformation_factory = $transformation_factory;
	}


	protected function addTransformation(TransformationFactory $transformation_factory)
	{
		$duration = $transformation_factory->custom(function($v) {
			list($from, $until) = $v;
			if($from && $until) {
				return ['start'=>$from, 'end'=>$until, 'interval'=>$from->diff($until)];
			}
			return null;
		});
		$this->setAdditionalTransformation($duration);
	}

	protected function addValidation(ValidationFactory $validation_factory)
	{
		$txt_id = 'duration_end_must_not_be_earlier_than_start';
		$error = function (callable $txt, $value) use ($txt_id) {
			return $txt($txt_id, $value);
		};
		$is_ok = function($v) {
			if(is_null($v)) {
				return true;
			}
			return $v['start'] < $v['end'];
		};

		$from_before_until = $validation_factory->custom($is_ok, $error);
		$this->setAdditionalConstraint($from_before_until);
	}

	/**
	 * @inheritdoc
	 */
	public function withFormat(DateFormat\DateFormat $format): C\Input\Field\Duration
	{
		$clone = clone $this;
		$clone->format = $format;
		$clone->applyFormat();
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getFormat() : DateFormat\DateFormat
	{
		return $this->format;
	}

	/**
	 * @inheritdoc
	 */
	public function getTransformedFormat(): string
	{
		$mapping = DTField::FORMAT_MAPPING;
		$ret = '';
		foreach ($this->format->toArray() as $element) {
			if(array_key_exists($element, $mapping)) {
				$ret .= $mapping[$element];
			} else {
				$ret .= $element;
			}
		}
		return $ret;
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
	public function withMinValue(\DateTime $date) : C\Input\Field\Duration
	{
		$clone = clone $this;
		$clone->min_date = $date;
		$clone->applyMinValue();
		return $clone;
	}

	/**
	 * apply format to inputs
	 */
	protected function applyMinValue()
	{
		$this->inputs = array_map(
			function($inpt) {
				return $inpt->withMinValue($this->getMinValue());
			},
			$this->inputs
		);
	}

	/**
	 * @inheritdoc
	 */
	public function getMinValue()
	{
		return $this->min_date;
	}

	/**
	 * @inheritdoc
	 */
	public function withMaxValue(\DateTime $date) : C\Input\Field\Duration
	{
		$clone = clone $this;
		$clone->max_date = $date;
		$clone->applyMaxValue();
		return $clone;
	}

	/**
	 * apply format to inputs
	 */
	protected function applyMaxValue()
	{
		$this->inputs = array_map(
			function($inpt) {
				return $inpt->withMaxValue($this->getMaxValue());
			},
			$this->inputs
		);
	}

	/**
	 * @inheritdoc
	 */
	public function getMaxValue()
	{
		return $this->max_date;
	}

	/**
	 * @inheritdoc
	 */
	public function withTimeOnly(bool $with_time_only): C\Input\Field\Duration
	{
		$clone = clone $this;
		$clone->with_time_only = $with_time_only;
		$clone->applyWithTimeOnly();
		return $clone;
	}

	/**
	 * apply format to inputs
	 */
	protected function applyWithTimeOnly()
	{
		$this->inputs = array_map(
			function($inpt) {
				return $inpt->withTimeOnly($this->getTimeOnly());
			},
			$this->inputs
		);
	}

	/**
	 * @inheritdoc
	 */
	public function getTimeOnly(): bool
	{
		return $this->with_time_only;
	}

	/**
	 * @inheritdoc
	 */
	public function withTime(bool $with_time): C\Input\Field\Duration
	{
		$clone = clone $this;
		$clone->with_time = $with_time;
		$clone->applyWithTime();
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getUseTime(): bool
	{
		return $this->with_time;
	}

	/**
	 * apply format to inputs
	 */
	protected function applyWithTime() {
		$this->inputs = array_map(
			function($inpt) {
				return $inpt->withTime($this->getUseTime());
			},
			$this->inputs
		);
	}

	/**
	 * @inheritdoc
	 */
	public function withTimezone(string $tz): C\Input\Field\Duration
	{
		$trafo = $this->transformation_factory->toTZDate($tz);
		$clone = clone $this;
		$clone->timezone = $tz;

		$clone->inputs = array_map(
			function($inpt) use ($trafo) {
				return $inpt->withAdditionalTransformation($trafo);
			},
			$clone->inputs
		);
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getTimezone()
	{
		return $this->timezone;
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
		return null;
	}

}
