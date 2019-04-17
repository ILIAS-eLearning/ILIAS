<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\DateFormat;

/**
 * Builds a Date Format with split up elements to ease conversion.
 * Internal constants are based on options for php date format.
 */
class FormatBuilder
{
	protected $format = [];
	protected $format_instance;

	/**
	 * Get the configured format.
	 */
	public function get(): array
	{
		return $this->format;
	}

	/**
	 * Bind an instance of DateFormat to the builder;
	 * used in combination with 'apply'.
	 */
	public function withInstance(DateFormat $instance): FormatBuilder
	{
		$clone = clone $this;
		$clone->format_instance = $instance;
		return $clone;
	}

	/**
	 * Apply the format to the instance of DateFormat set by 'withIntance'.
	 */
	public function apply(): DateFormat
	{
		if(is_null($this->format_instance)) {
			throw new \LogicException("no instance to apply to", 1);
		}
		return $this->format_instance->applyFormat($this);
	}

	public function dot(): FormatBuilder
	{
		$this->format[] = '.';
		return $this;
	}
	public function comma(): FormatBuilder
	{
		$this->format[] = ',';
		return $this;
	}
	public function dash(): FormatBuilder
	{
		$this->format[] = '-';
		return $this;
	}
	public function slash(): FormatBuilder
	{
		$this->format[] = '/';
		return $this;
	}
	public function space(): FormatBuilder
	{
		$this->format[] = ' ';
		return $this;
	}

	public function day(): FormatBuilder
	{
		$this->format[] = 'd';
		return $this;
	}
	public function dayOrdinal(): FormatBuilder
	{
		$this->format[] = 'jS';
		return $this;
	}
	public function weekday(): FormatBuilder
	{
		$this->format[] = 'l';
		return $this;
	}
	public function weekdayShort(): FormatBuilder
	{
		$this->format[] = 'D';
		return $this;
	}
	public function week(): FormatBuilder
	{
		$this->format[] = 'W';
		return $this;
	}
	public function month(): FormatBuilder
	{
		$this->format[] = 'm';
		return $this;
	}
	public function monthSpelled(): FormatBuilder
	{
		$this->format[] = 'F';
		return $this;
	}
	public function monthSpelledShort(): FormatBuilder
	{
		$this->format[] = 'M';
		return $this;
	}
	public function year(): FormatBuilder
	{
		$this->format[] = 'Y';
		return $this;
	}
	public function twoDigitYear(): FormatBuilder
	{
		$this->format[] = 'y';
		return $this;
	}

}
