<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\Password;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Password\Constraint\HasLowerChars;
use ILIAS\Refinery\Password\Constraint\HasMinLength;
use ILIAS\Refinery\Password\Constraint\HasNumbers;
use ILIAS\Refinery\Password\Constraint\HasSpecialChars;
use ILIAS\Refinery\Password\Constraint\HasUpperChars;
use ILIAS\Refinery\Password\Group;
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

	public function testHasMinLength()
	{
		$instance = $this->group->hasMinLength(4);
		$this->assertInstanceOf(HasMinLength::class, $instance);
	}

	public function testHasLowerChars()
	{
		$instance = $this->group->hasLowerChars();
		$this->assertInstanceOf(HasLowerChars::class, $instance);
	}

	public function testHasNumbers()
	{
		$instance = $this->group->hasNumbers();
		$this->assertInstanceOf(HasNumbers::class, $instance);
	}

	public function testHasSpecialChars()
	{
		$instance = $this->group->hasSpecialChars();
		$this->assertInstanceOf(HasSpecialChars::class, $instance);
	}

	public function testHasUpperChars()
	{
		$instance = $this->group->hasUpperChars();
		$this->assertInstanceOf(HasUpperChars::class, $instance);
	}
}
