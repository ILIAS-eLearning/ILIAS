<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\DateFormat;

/**
 * A Date Format provides a format definition akin to PHP's date formatting options,
 * but stores the single elements/options as array to ease conversion into other formats.
 */
class DateFormat
{
	/**
	 * @var array
	 */
	protected $format = [];

	public function __construct(FormatBuilder $builder)
	{
		$this->format = $builder->get();
	}

	/**
	 * Get the elements of the format as array.
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->format;
	}

	/**
	 * Get the format as string.
	 * @return array
	 */
	public function toString(): string
	{
		return implode('', $this->format);
	}
}
