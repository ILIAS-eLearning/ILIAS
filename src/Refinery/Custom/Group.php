<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
namespace ILIAS\Refinery\Custom;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Custom\Constraints;
use ILIAS\Refinery\Custom\Transformations;

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
	 * @return Constraints\Custom
	 */
	public function constraint(callable $callable, $error) : Constraints\Custom
	{
		return new Constraints\Custom(
			$callable,
			$error,
			$this->dataFactory,
			$this->language
		);
	}

	/**
	 * @param callable $transform
	 * @return Transformations\Custom
	 */
	public function transformation(callable $transform) : Transformations\Custom
	{
		return new Transformations\Custom($transform, $this->dataFactory);
	}
}
