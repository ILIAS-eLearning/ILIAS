<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
namespace ILIAS\Refinery\Custom;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Custom\Constraint;
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
	 * @return Constraint\Custom
	 */
	public function constraint(callable $callable, $error) : Constraint\Custom
	{
		return new Constraint\Custom(
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
