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
	 * @param \ILIAS\Data\Factory $dataFactory
	 */
	public function __construct(\ILIAS\Data\Factory $dataFactory)
	{
		$this->dataFactory = $dataFactory;
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
		return new KindlyTo\Group($this->dataFactory);
	}
}
