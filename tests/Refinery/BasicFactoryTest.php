<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery;

use ILIAS\Refinery\BasicFactory;

require_once('./libs/composer/vendor/autoload.php');
require_once('./tests/Refinery/TestCase.php');

class BasicFactoryTest extends TestCase
{
	/**
	 * @var BasicFactory
	 */
	private $basicFactory;

	public function setUp()
	{
		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$dataFactory = new \ILIAS\Data\Factory();

		$validationFactory = new \ILIAS\Refinery\Validation\Factory($dataFactory, $language);
		$this->basicFactory = new BasicFactory($validationFactory);
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

	public function testCreateKindlyToGroup()
	{
		$group = $this->basicFactory->kindlyTo();

		$this->assertInstanceOf(\ILIAS\Refinery\KindlyTo\Group::class, $group);
	}
}
