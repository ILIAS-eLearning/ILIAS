<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
namespace ILIAS\Refinery\Integer;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Integer\GreaterThan;
use ILIAS\Refinery\Integer\LessThan;

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
	 * Creates a constraint that can be used to check if an integer value is
	 * greater than the defined lower limit.
	 *
	 * @param int $minimum - lower limit for the new constraint
	 * @return GreaterThan
	 */
	public function isGreaterThan(int $minimum) : GreaterThan
	{
		return new GreaterThan($minimum, $this->dataFactory, $this->language);
	}

	/**
	 * Creates a constraint that can be used to check if an integer value is
	 * less than the defined upper limit.
	 *
	 * @param int $maximum - upper limit for the new constraint
	 * @return LessThan
	 */
	public function isLessThan(int $maximum) : LessThan
	{
		return new LessThan($maximum, $this->dataFactory, $this->language);
	}
}
