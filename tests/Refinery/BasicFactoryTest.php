<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;

require_once('./libs/composer/vendor/autoload.php');

class BasicFactoryTest extends \PHPUnit_Framework_TestCase
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

		$this->assertInstanceOf(To\Group::class, $group);
	}

	public function testCreateFromGroup()
	{
		$group = $this->basicFactory->in();

		$this->assertInstanceOf(In\Group::class, $group);
	}
}
