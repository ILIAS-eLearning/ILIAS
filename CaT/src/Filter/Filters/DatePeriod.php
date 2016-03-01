<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class DatePeriod extends Filter {
	/**
	 * @var	\DateTime
	 */
	private $default_begin;

	/**
	 * @var	\DateTime
	 */
	private $default_end;

	/**
	 * @var	\DateTime
	 */
	private $period_min;

	/**
	 * @var	\DateTime
	 */
	private $period_max;

	public function __construct(\CaT\Filter\FilterFactory $factory, $label, $description,
			\DateTime $default_begin = null, \DateTime $default_end = null,
			\DateTime $period_min = null, \DateTime $period_max = null,
			array $mappings = array(), array $mapping_result_types = array()) {
		assert('is_string($label)');
		assert('is_string($description)');

		$this->setFactory($factory);
		$this->setLabel($label);
		$this->setDescription($description);
		$this->setMappings($mappings, $mapping_result_types);

		if ($default_begin === null) {
			$this->default_begin = new \DateTime(date("Y")."-01-01");
		}
		else {
			$this->default_begin = $default_begin;
		}

		if ($default_end === null) {
			$this->default_end = new \DateTime(date("Y")."-12-31");
		}
		else {
			$this->default_end = $default_end;
		}

		if ($period_min === null) {
			$this->period_min = new \DateTime("1900-01-01");
		}
		else {
			$this->period_min = $period_min;
		}

		if ($period_max === null) {
			$this->period_max = new \DateTime("2100-12-31");
		}
		else {
			$this->period_max = $period_max;
		}
	}

	/**
	 * @inheritdocs
	 */
	public function original_content_type() {
		$tf = $this->factory->type_factory();
		return $tf->tuple($tf->cls("\\DateTime"), $tf->cls("\\DateTime"));
	}

	/**
	 * @inheritdocs
	 */
	public function input_type() {
		return $this->original_content_type();
	}

	/**
	 * @inheritdocs
	 */
	protected function raw_content($input) {
		return $input;
	}

	/**
	 * Set the default for the beginning of this filter.
	 *
	 * @param	\DateTime
	 * @return	DatePeriod
	 */
	public function default_begin(\DateTime $dt = null) {
		if ($dt === null) {
			return $this->default_begin;
		}

		list($ms, $mrts) = $this->getMappings();
		return new DatePeriod($this->factory, $this->label(), $this->description(),
						$dt, $this->default_end, $this->period_min, $this->period_max,
						$ms, $mrts);
	}

	/**
	 * Set the default for the end of this filter.
	 *
	 * @param	\DateTime
	 * @return	DatePeriod
	 */
	public function default_end(\DateTime $dt = null) {
		if ($dt === null) {
			return $this->default_end;
		}

		list($ms, $mrts) = $this->getMappings();
		return new DatePeriod($this->factory, $this->label(), $this->description(),
						$this->default_begin, $dt, $this->period_min, $this->period_max,
						$ms, $mrts);
	}

	/**
	 * Set the minimum value for the begin of the filtered period.
	 *
	 * @param	\DateTime
	 * @return	DatePeriod
	 */
	public function period_min(\DateTime $dt = null) {
		if ($dt === null) {
			return $this->period_min;
		}

		list($ms, $mrts) = $this->getMappings();
		return new DatePeriod($this->factory, $this->label(), $this->description(),
						$this->default_begin, $this->default_end, $dt, $this->period_max,
						$ms, $mrts);
	}

	/**
	 * Set the maximum value for the end of the filtered period.
	 *
	 * @param	\DateTime
	 * @return	DatePeriod
	 */
	public function period_max(\DateTime $dt = null) {
		throw new \Exception("Not implemented");
		if ($dt === null) {
			return $this->period_max;
		}

		list($ms, $mrts) = $this->getMappings();
		return new DatePeriod($this->factory, $this->label(), $this->description(),
						$this->default_begin, $this->default_end, $this->period_min,
						$dt, $ms, $mrts);
	}

	/**
	 * @inheritdocs
	 */
	protected function clone_with_new_mappings($mappings, $mapping_result_types) {
		return new DatePeriod($this->factory, $this->label(), $this->description(),
						$this->default_begin, $this->default_end, $this->period_min,
						$this->period_max, $mappings, $mapping_result_types);

	}
}