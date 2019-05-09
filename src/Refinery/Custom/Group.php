<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
namespace ILIAS\Refinery\Custom;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Custom\Constraints\Custom;

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
	 * @param callable $callable
	 * @param $error
	 * @return Custom
	 */
	public function custom(callable $callable, $error) : Custom
	{
		return new Custom(
			$callable,
			$error,
			$this->dataFactory,
			$this->language
		);
	}
}
