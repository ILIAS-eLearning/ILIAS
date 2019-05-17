<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
namespace ILIAS\Refinery\Custom;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Custom;
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
	 * @return Custom\Constraint
	 */
	public function constraint(callable $callable, $error) : Custom\Constraint
	{
		return new Custom\Constraint(
			$callable,
			$error,
			$this->dataFactory,
			$this->language
		);
	}

	/**
	 * @param callable $transform
	 * @return Transformations\Transformation
	 */
	public function transformation(callable $transform) : Transformations\Transformation
	{
		return new Transformations\Transformation($transform, $this->dataFactory);
	}
}
