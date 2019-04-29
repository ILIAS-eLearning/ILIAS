<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Transformation\Factory as TransformationFactory;
use ILIAS\Validation\Factory as ValidationFactory;
use ILIAS\UI\Implementation\Component\ComponentHelper;


/**
 * This describes the interval-field.
 */
class DateTimeInterval extends Group implements C\Input\Field\DateTimeInterval
{
	const OPTIONS_TIME = [
		'S' => 'Seconds',
		'M' => 'Minutes',
		'H' => 'Hours'
	];

	const OPTIONS = [
		'd' => 'Days',
		'w' => 'Weeks',
		'm' => 'Months',
		'y' => 'Years'
	];

	const INTERVAL_PARTS = [
		's' => 60,
		'i' => 60,
		'h' => 24,
		'd' => 30, //this is not correct, but then the duration w/o a point in time is weak anyway.
		'm' => 12,
		'y' => 1
	];

	public function __construct(
		DataFactory $data_factory,
		ValidationFactory $validation_factory,
		TransformationFactory $transformation_factory,
		Factory $field_factory,
		$label,
		$byline
	) {

		$options = array_merge(self::OPTIONS, self::OPTIONS_TIME);
		$inputs = [
			$field_factory->numeric('',''),
			$field_factory->select('', $options)
		];
		parent::__construct($data_factory, $validation_factory, $transformation_factory, $inputs, $label, $byline);
		$this->addTransformation($transformation_factory);
	}

	protected function addTransformation(TransformationFactory $transformation_factory)
	{
		$interval = $transformation_factory->custom(function($v) {
			list($val, $unit) = $v;
			if(! $unit) {
				return;
			}
			$interval_string = 'P';

			$is_negative = false;
			if($val < 0) {
				$is_negative = true;
				$val = $val * -1;
			}

			if(! array_key_exists($unit, self::OPTIONS_TIME) ) {
				$interval_string .= $val .strtoupper($unit);
			} else {
				$interval_string .= 'T' .$val .$unit;
			}
			$interval = new \DateInterval($interval_string);

			if($is_negative) {
				$interval->invert = 1;
			}
			return $interval;
		});
		$this->setAdditionalTransformation($interval);
	}

	public function withValue($value) {
		if(! is_null($value)) {
			if(! $value instanceof \DateInterval) {
				throw new \InvalidArgumentException("$value is not a DateInterval", 1);
			}

			$smallest_unit = '';
			$totalled_value = '';
			foreach (self::INTERVAL_PARTS as $key => $factor) {
				if($smallest_unit == '' && $value->$key > 0) {
					$smallest_unit = $key;
					$current_factor = 1;
				}
				$totalled_value += $value->$key * $current_factor;
				$current_factor = $current_factor  * $factor;
			}

			switch ($smallest_unit) {
				case 'i':
					$smallest_unit = 'M';
					break;
				case 'h':
				case 's':
					$smallest_unit = strtoupper($smallest_unit);
					break;
			}


			$value = [(string)$totalled_value, $smallest_unit];
			return $this->withGroupValues($value);
		}
		return $this;
	}

}
