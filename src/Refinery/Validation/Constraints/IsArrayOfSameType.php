<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\Validation\Constraints;

use ILIAS\Refinery\Validation\Constraint;
use ILIAS\Data;

/**
 * @package ILIAS\Refinery\Validation\Constraints
 */
class IsArrayOfSameType extends Custom implements Constraint
{
	/**
	 * @param array $values
	 * @param Data\Factory $data_factory
	 * @param \ilLanguage $lng
	 */
	public function __construct(Data\Factory $data_factory, \ilLanguage $lng)
	{
		parent::__construct(
			function (array $values) {
				$oldType = '';
				foreach ($values as $key => $value) {
					$currentType = gettype($value);

					if ('' !== $oldType && $oldType !== $currentType) {
						return false;
					}

					$oldType = $currentType;
				}

				return true;
			},
			function ($txt, $value) {
				return $txt('array_values_not_of_same_type', '');
			},
			$data_factory,
			$lng
		);
	}
}
