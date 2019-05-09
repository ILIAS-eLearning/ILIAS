<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;


use ILIAS\Refinery\In;
use ILIAS\Refinery\To;

class Factory
{
	/**
	 * @var \ILIAS\Data\Factory
	 */
	private $dataFactory;

	/**
	 * @var \ilLanguage
	 */
	private $language;

	/**
	 * @param \ILIAS\Data\Factory $dataFactory
	 * @param \ilLanguage $language
	 */
	public function __construct(\ILIAS\Data\Factory $dataFactory, \ilLanguage $language)
	{
		$this->dataFactory = $dataFactory;
		$this->language = $language;
	}

	/**
	 * Combined validations and transformations for primitive data types that
	 * establish a baseline for further constraints and more complex transformations
	 *
	 * @return To\Group
	 */
	public function to(): To\Group
	{
		return new To\Group($this->dataFactory);
	}

	/**
	 * Creates a factory object to create a transformation object, that
	 * can be used to execute other transformation objects in a desired
	 * order.
	 *
	 * @return In\Group
	 */
	public function in(): In\Group
	{
		return new In\Group();
	}

	/**
	 * Contains constraints and transformations on numbers. Each constraint
	 * on an int will attempt to transform to int as well.
	 *
	 * @return Integer\Group
	 */
	public function int(): Integer\Group
	{
		return new Integer\Group($this->dataFactory, $this->language);
	}
}
