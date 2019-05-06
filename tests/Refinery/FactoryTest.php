<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery;

use ILIAS\Refinery\Factory;

require_once('./libs/composer/vendor/autoload.php');
require_once('./tests/Refinery/TestCase.php');

class FactoryTest extends TestCase
{
	/**
	 * @var Factory
	 */
	private $basicFactory;

	public function setUp() : void
	{
		$this->basicFactory = new Factory();
	}

	public function testCreateToGroup()
	{
		$group = $this->basicFactory->to();

		$this->assertInstanceOf(\ILIAS\Refinery\To\Group::class, $group);
	}

	public function testCreateInGroup()
	{
		$group = $this->basicFactory->in();

		$this->assertInstanceOf(\ILIAS\Refinery\In\Group::class, $group);
	}
}
