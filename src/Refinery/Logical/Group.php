<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Logical;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Validation\Constraint;
use ILIAS\Refinery\Validation\Constraints\LogicalOr;
use ILIAS\Refinery\Validation\Constraints\Not;
use ILIAS\Refinery\Validation\Constraints\Parallel;
use ILIAS\Refinery\Validation\Constraints\Sequential;

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

	/**
	 * @param array $other
	 * @return LogicalOr
	 */
	public function logicalOr(array $other) : LogicalOr
	{
		return new LogicalOr($other, $this->dataFactory, $this->language);
	}

	/**
	 * @param Constraint $constraint
	 * @return Not
	 */
	public function not(Constraint $constraint) : Not
	{
		return new Not($constraint, $this->dataFactory, $this->language);
	}

	/**
	 * @param array $constraints
	 * @return Parallel
	 */
	public function parallel(array $constraints) : Parallel
	{
		return new Parallel($constraints, $this->dataFactory, $this->language);
	}

	/**
	 * @param array $constraints
	 * @return Sequential
	 */
	public function sequential(array $constraints) : Sequential
	{
		return new Sequential($constraints, $this->dataFactory, $this->language);
	}
}
