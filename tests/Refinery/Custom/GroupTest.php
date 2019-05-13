<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\Custom;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Custom\Group;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');
require_once('./tests/Refinery/TestCase.php');

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

	public function testCustomConstraint()
	{
		$instance = $this->group->constraint(function () {}, 'some error');
		$this->assertInstanceOf(\ILIAS\Refinery\Custom\Constraint\Custom::class, $instance);
	}

	public function testCustomTransformation()
	{
		$instance = $this->group->transformation(function () {});
		$this->assertInstanceOf(\ILIAS\Refinery\Custom\Transformations\Custom::class, $instance);
	}
}
