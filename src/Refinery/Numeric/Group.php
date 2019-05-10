<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Numeric;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Numeric\Constraint\IsNumeric;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
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

	public function isNumeric() : IsNumeric
	{
		return new IsNumeric($this->dataFactory, $this->language);
	}
}
