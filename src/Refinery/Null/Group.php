<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Null;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Null\Constraint\IsNull;

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

	public function isNull()
	{
		return new IsNull($this->dataFactory, $this->language);
	}
}
