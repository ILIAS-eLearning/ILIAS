<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\DateFormat as DateFormat;
use ILIAS\Refinery\Transformation\Factory as TransformationFactory;
use ILIAS\Refinery\Validation\Factory as ValidationFactory;
use ILIAS\UI\Component\JavaScriptBindable as JSBindabale;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;


/**
 * This implements the date input.
 */
class DateTime extends Input implements C\Input\Field\DateTime, JSBindabale {

	use ComponentHelper;
	use JavaScriptBindable;

	const TIME_FORMAT = 'HH:mm';

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
	 * @var array<string,mixed>
	 */
	protected $additional_picker_config = [];

	/**
	 * @var TransformationFactory
	 */
	protected $transformation_factory;

	/**
	 * @var string
	 */
	protected $timezone;

	/**
	 * @param DataFactory $data_factory
	 * @param ValidationFactory $validation_factory
	 * @param TransformationFactory $transformation_factory
	 * @param Component\Input\Field\Factory $field_factory
	 * @param string $label
	 * @param string $byline
	 */
	public function __construct(
		DataFactory $data_factory,
		ValidationFactory $validation_factory,
		TransformationFactory $transformation_factory,
		\ILIAS\Refinery\Factory $refinery,
		$label,
		$byline
	) {
		parent::__construct($data_factory, $validation_factory, $transformation_factory, $refinery, $label, $byline);

		$this->transformation_factory = $transformation_factory;
		$trafo = $transformation_factory->toDateTime();
		$this->setAdditionalTransformation($trafo);
		$this->format = $data_factory->dateFormat()->standard();
	}

	/**
	 * @inheritdoc
	 */
	public function withFormat(DateFormat\DateFormat $format): C\Input\Field\DateTime
	{
		$clone = clone $this;
		$clone->format = $format;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getFormat(): DateFormat\DateFormat
	{
		return $this->format;
	}

	/**
	 * @inheritdoc
	 */
	public function withTimezone(string $tz): C\Input\Field\DateTime
	{
		$trafo = $this->transformation_factory->toDateTimeWithTimezone($tz);
		$clone = clone $this;
		$clone->timezone = $tz;
		return $clone->withAdditionalTransformation($trafo);
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
	public function withMinValue(\DateTimeImmutable $date) : C\Input\Field\DateTime
	{
		$clone = clone $this;
		$clone->min_date = $date;
		return $clone;
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
	public function withMaxValue(\DateTimeImmutable $date) : C\Input\Field\DateTime
	{
		$clone = clone $this;
		$clone->max_date = $date;
		return $clone;
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
	public function withUseTime(bool $with_time): C\Input\Field\DateTime
	{
		$clone = clone $this;
		$clone->with_time = $with_time;
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
	 * @inheritdoc
	 */
	public function withTimeOnly(bool $with_time_only): C\Input\Field\DateTime
	{
		$clone = clone $this;
		$clone->with_time_only = $with_time_only;
		return $clone;
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
	protected function isClientSideValueOk($value)
	{
		return is_string($value);
	}

	/**
	 * @inheritdoc
	 */
	protected function getConstraintForRequirement()
	{
		return $this->validation_factory->hasMinLength(1);
	}

	/**
	 * Get config to be passed to the bootstrap picker.
	 * @return array <string => mixed>
	 */
	public function getAdditionalPickerconfig(): array
	{
		return $this->additional_picker_config;
	}

	/**
	 * The bootstrap picker can be configured, e.g. with a minimum date.
	 * @param array <string => mixed> $config
	 * @return DateTime
	 */
	public function withAdditionalPickerconfig(array $config): DateTime
	{
		$clone = clone $this;
		$clone->additional_picker_config = array_merge($clone->additional_picker_config, $config);
		return $clone;
	}

}
