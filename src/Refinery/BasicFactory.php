<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;


use ILIAS\Refinery\Factory;
use ILIAS\Refinery\In;
use ILIAS\Refinery\To;

class BasicFactory implements Factory
{
	/**
	 * @var Validation\Factory
	 */
	private $validationFacory;

	/**
	 * @param \ilLanguage|null $language
	 */
	public function __construct(\ILIAS\Refinery\Validation\Factory $validationFactory)
	{
		$this->validationFacory = $validationFactory;
	}

	/**
	 * Combined validations and transformations for primitive data types that
	 * establish a baseline for further constraints and more complex transformations
	 *
	 * @return To\Group
	 */
	public function to(): To\Group
	{
		return new To\BasicGroup($this->validationFacory->isArrayOfSameType());
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
		return new In\BasicGroup();
	}

	/**
	 * Combined validations and transformations for primitive data types that
	 * establish a baseline for further constraints and more complex transformations
	 *
	 * Offers the same transformations like **to** but will be more
	 * forgiving regarding the input
	 *
	 * @return KindlyTo\Group
	 */
	public function kindlyTo(): KindlyTo\Group
	{
		return new KindlyTo\BasicGroup();
	}
}
