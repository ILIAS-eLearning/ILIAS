<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateFactoryTest extends ilCertificateBaseTestCase
{
	/**
	 * 
	 */
	public function testTypeIsNotSupportedAndWillThrowAnException()
	{
		$this->expectException(\ilException::class);

		$object = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$object->method('getType')
			->willReturn('something');

		$factory = new ilCertificateFactory();

		$factory->create($object);

		$this->fail('Should never happen');
	}
}
