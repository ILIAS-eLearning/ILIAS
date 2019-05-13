<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\Numeric;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Integer\Constraint\GreaterThan;
use ILIAS\Refinery\Integer\Constraint\LessThan;
use ILIAS\Refinery\Numeric\Constraint\IsNumeric;
use ILIAS\Refinery\Numeric\Group;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class GroupTest extends TestCase
{
	/**
	 * @var Group
	 */
	private $group;

	/**
	 * @var Factory
	 */
	private $dataFactory;

	/**
	 * @var \ilLanguage
	 */
	private $language;

	public function setUp() : void
	{
		$this->dataFactory = new Factory();
		$this->language    = $this->getMockBuilder('\ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$this->group = new Group($this->dataFactory, $this->language);
	}

	public function testIsNumericGroup()
	{
		$instance = $this->group->isNumeric();
		$this->assertInstanceOf(IsNumeric::class, $instance);
	}
}
