<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\DateFormat;

/**
 * A Date Format provides a format definition akin to PHP's date formatting options,
 * but stores the single elements/options as array to ease conversion into other formats.
 */
class DateFormat
{

	const DOT = '.';
	const COMMA = ',';
	const DASH = '-';
	const SLASH = '/';
	const SPACE = ' ';
	const DAY = 'd';
	const DAY_ORDINAL = 'jS';
	const WEEKDAY = 'l';
	const WEEKDAY_SHORT = 'D';
	const WEEK = 'W';
	const MONTH = 'm';
	const MONTH_SPELLED = 'F';
	const MONTH_SPELLED_SHORT = 'M';
	const YEAR = 'Y';
	const YEAR_TWO_DIG = 'y';

	const TOKENS = [
		self::DOT,
		self::COMMA,
		self::DASH,
		self::SLASH,
		self::SPACE,
		self::DAY,
		self::DAY_ORDINAL,
		self::WEEKDAY,
		self::WEEKDAY_SHORT,
		self::WEEK,
		self::MONTH,
		self::MONTH_SPELLED,
		self::MONTH_SPELLED_SHORT,
		self::YEAR,
		self::YEAR_TWO_DIG
	];

	/**
	 * @var array
	 */
	protected $format = [];

	public function __construct(array $format)
	{
		$this->validateFormatElelements($format);
		$this->format = $format;
	}

	public function validateFormatElelements(array $format)
	{
		foreach ($format as $entry) {
			if(! in_array($entry, self::TOKENS)) {
				throw new \InvalidArgumentException("not a valid token for date-format", 1);
			}
		}
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
