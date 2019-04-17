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
		return new DateFormat(
			$this->builder->year()->dash()->month()->dash()->day()
		);
	}

	/**
	 * Get a "blank" DateFormat to be manually defined via the
	 * FormatBuilder retrieved by CustomFormat::withFormat()
	 * @return DateFormat
	 */
	public function custom(): DateFormat
	{
		return new CustomFormat($this->format_builder);
	}

	/**
	 * @return DateFormat
	 */
	public function germanShort(): DateFormat
	{
		return new DateFormat(
			$this->builder->day()->dot()->month()->dot()->year()
		);
	}

	/**
	 * @return DateFormat
	 */
	public function germanLong(): DateFormat
	{
		return new DateFormat(
			$this->builder->weekday()->comma()->space()
				->day()->dot()->month()->dot()->year()
		);
	}
}
