<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;

interface Factory
{
	/**
	 * Combined validations and transformations for primitive data types that
	 * establish a baseline for further constraints and more complex transformations
	 *
	 * @return To\Group
	 */
	public function to() : To\Group;

	/**
	 * Creates a factory object to create a transformation object, that
	 * can be used to execute other transformation objects in a desired
	 * order.
	 *
	 * @return In\Group
	 */
	public function in() : In\Group;

	/**
	 * Combined validations and transformations for primitive data types that
	 * establish a baseline for further constraints and more complex transformations
	 *
	 * Offers the same transformations like **to** but will be more
	 * forgiving regarding the input
	 *s
	 * @return KindlyTo\Group
	 */
	public function kindlyTo() : KindlyTo\Group;
}
