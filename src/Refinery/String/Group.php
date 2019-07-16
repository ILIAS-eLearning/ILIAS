<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
namespace ILIAS\Refinery\String;

use ILIAS\Data\Factory;
use ILIAS\Refinery\String\HasMaxLength;
use ILIAS\Refinery\String\HasMinLength;
use ILIAS\Refinery\String\SplitString;

class Group
{
	/**
	 * @var Factory
	 */
	private $dataFactory;

	/**
	 * @var \ilLanguage
	 */
	private $language;

	public function __construct(Factory $dataFactory, \ilLanguage $language)
	{
		$this->dataFactory = $dataFactory;
		$this->language = $language;
	}

	/**
	 * Creates a constraint that can be used to check if a string
	 * has reached a minimum length
	 *
	 * @param int $minimum - minimum length of a string that will be checked
	 *                       with the new constraint
	 * @return HasMinLength
	 */
	public function hasMinLength(int $minimum) : HasMinLength
	{
		return new HasMinLength($minimum, $this->dataFactory, $this->language);
	}

	/**
	 * Creates a constraint that can be used to check if a string
	 * has exceeded a maximum length
	 *
	 * @param int $maximum - maximum length of a strings that will be checked
	 *                       with the new constraint
	 * @return HasMaxLength
	 */
	public function hasMaxLength(int $maximum) : HasMaxLength
	{
		return new HasMaxLength($maximum, $this->dataFactory, $this->language);
	}

	/**
	 * Creates a transformation that can be used to split a given
	 * string by given delimiter.
	 *
	 * @param string $delimiter
	 * @return SplitString
	 */
	public function splitString(string $delimiter) : SplitString
	{
		return new SplitString($delimiter, $this->dataFactory);
	}

	/**
	 * Creates a transformation that strips tags from a string.
	 *
	 * Uses php's strip_tags under the hood.
	 */
	public function stripTags() : StripTags
	{
		return new StripTags();
	}
}
