<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\String;

use ILIAS\Data\Factory;
use ILIAS\Refinery\String\Group;
use ILIAS\Refinery\String\Constraint\HasMinLength;
use ILIAS\Refinery\String\Constraint\HasMaxLength;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class GroupTest extends TestCase
{
	/**
	 * @var Group
	 */
	private $group;

	public function setUp() : void
	{
		$dataFactory = new Factory();
		$language    = $this->getMockBuilder('\ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$this->group = new Group($dataFactory, $language);
	}

	public function testGreaterThanInstance()
	{
		$instance = $this->group->hasMaxLength(42);
		$this->assertInstanceOf(HasMaxLength::class, $instance);
	}
	public function testLowerThanInstance()
	{
		$instance = $this->group->hasMinLength(42);
		$this->assertInstanceOf(HasMinLength::class, $instance);
	}
}
