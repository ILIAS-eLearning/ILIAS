<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\DateFormat;

/**
 * Factory for Date Formats
 */
class Factory
{
	/**
	 * @var FormatBuilder
	 */
	protected $builder;

	public function __construct(FormatBuilder $builder)
	{
		$this->builder = $builder;
	}

	/**
	 * Get the ISO 8601 date format (YYYY-MM-DD)
	 * @return DateFormat
	 */
	public function standard(): DateFormat
	{
		return $this->builder->year()->dash()->month()->dash()->day()->get();
	}

	/**
	 * Get the builder to define a custom DateFormat
	 * @return FormatBuilder
	 */
	public function custom(): FormatBuilder
	{
		return $this->builder;
	}

	/**
	 * @return DateFormat
	 */
	public function germanShort(): DateFormat
	{
		return $this->builder->day()->dot()->month()->dot()->year()->get();
	}

	/**
	 * @return DateFormat
	 */
	public function germanLong(): DateFormat
	{
		return $this->builder->weekday()->comma()->space()
			->day()->dot()->month()->dot()->year()->get();
	}
}
