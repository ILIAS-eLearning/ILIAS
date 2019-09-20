<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\DateFormat as DateFormat;
use ILIAS\Refinery\Transformation\Factory as TransformationFactory;
use ILIAS\Refinery\Validation\Factory as ValidationFactory;
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
	 * @var \DateTimeImmutable
	 */
	protected $min_date;

	/**
	 * @var \DateTimeImmutable
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

	/**
	 * @param DataFactory $data_factory
	 * @param Refinery\Factory $field_factory
	 * @param string $label
	 * @param string $byline
	 */
	public function __construct(
		DataFactory $data_factory,
		\ILIAS\Refinery\Factory $refinery,
		Factory $field_factory,
		$label,
		$byline
	) {
		$inputs = [
			$field_factory->dateTime('start'),
			$field_factory->dateTime('end')
		];

		parent::__construct($data_factory, $refinery, $inputs, $label, $byline);

		$this->addTransformation();
		$this->addValidation();
	}

	/**
	 * Return-value of Duration is an assoc array with start, end and interval.
	 * If one or the other of start/end is omitted, there is no possible calculation
	 * of a duration - in this case, null is being returned.
	 *
	 */
	protected function addTransformation()
	{
		$duration = $this->refinery->custom()->transformation(function($v) {
			list($from, $until) = $v;
			if($from && $until) {
				return ['start'=>$from, 'end'=>$until, 'interval'=>$from->diff($until)];
			}
			return null;
		});
		$this->setAdditionalTransformation($duration);
	}

	/**
	 * Input is valid, if start is before end.
	 */
	protected function addValidation()
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

		$from_before_until = $this->refinery->custom()->constraint($is_ok, $error);
		$this->setAdditionalTransformation($from_before_until);
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
	public function withMinValue(\DateTimeImmutable $date) : C\Input\Field\Duration
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
	public function withMaxValue(\DateTimeImmutable $date) : C\Input\Field\Duration
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
	public function withUseTime(bool $with_time): C\Input\Field\Duration
	{
		$clone = clone $this;
		$clone->with_time = $with_time;
		$clone->applyWithUseTime();
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
	protected function applyWithUseTime() {
		$this->inputs = array_map(
			function($inpt) {
				return $inpt->withUseTime($this->getUseTime());
			},
			$this->inputs
		);
	}

	/**
	 * @inheritdoc
	 */
	public function withTimezone(string $tz): C\Input\Field\Duration
	{
		$trafo = $this->refinery->dateTime()->changeTimezone($tz);
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

	/**
	 * @inheritdoc
	 */
	public function getUpdateOnLoadCode(): \Closure
	{
		return function ($id) {
			$code = "var combinedDuration = function() {
				var options = [];
				$('#$id').find('input').each(function() {
					options.push($(this).val());
				});
				return options.join(' - ');
			}
			$('#$id').on('input dp.change', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', combinedDuration());
			});
			il.UI.input.onFieldUpdate(event, '$id', combinedDuration());";
			return $code;
		};
	}

}
