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
			\DateTime $period_min = null, \DateTime $period_max = null) {
		assert('is_string($label)');
		assert('is_string($description)');

		$this->setFactory($factory);
		$this->setLabel($label);
		$this->setDescription($description);

		if ($default_begin === null) {
			
		}
		$this->default_begin = $default_begin;

		if ($default_end === null) {
			
		}
		$this->default_end = $default_end;

		if ($period_min === null) {
			
		}
		$this->period_min = $period_min;

		if ($period_max === null) {
			
		}
		$this->period_max = $period_max;
	}

	/**
	 * @inheritdocs
	 */
	public function content_type() {
		return array("\\DateTime", "\\DateTime");
	}

	/**
	 * @inheritdocs
	 */
	public function input_type() {
		return $this->content_type();
	}

	/**
	 * @inheritdocs
	 */
	public function content(/*...$inputs*/) {
		$inputs = func_get_args();
		assert('count($inputs) == 2');
		assert('$inputs[0] instanceof \\DateTime');
		assert('$inputs[1] instanceof \\DateTime');

		return $inputs;
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

		return new DatePeriod($this->factory, $this->label(), $this->description(),
						$dt, $this->default_end, $this->period_min, $this->period_max);
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

		return new DatePeriod($this->factory, $this->label(), $this->description(),
						$this->default_begin, $dt, $this->period_min, $this->period_max);
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

		return new DatePeriod($this->factory, $this->label(), $this->description(),
						$this->default_begin, $this->default_end, $dt, $this->period_max);
	}

	/**
	 * Set the maximum value for the end of the filtered period.
	 *
	 * @param	\DateTime
	 * @return	DatePeriod
	 */
	public function period_max(\DateTime $dt = null) {
		if ($dt === null) {
			return $this->period_max;
		}

		return new DatePeriod($this->factory, $this->label(), $this->description(),
						$this->default_begin, $this->default_end, $this->period_min, $dt);
	}
}