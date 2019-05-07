<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
namespace ILIAS\Refinery\Integer;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Integer\Constraints\GreaterThan;
use ILIAS\Refinery\Integer\Constraints\LessThan;

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

	public function isGreaterThan(int $minimum) : GreaterThan
	{
		return new GreaterThan($minimum, $this->dataFactory, $this->language);
	}

	public function isLessThan(int $maximum) : LessThan
	{
		return new LessThan($maximum, $this->dataFactory, $this->language);
	}
}
