<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateScormTemplateDeleteActionTest extends \PHPUnit_Framework_TestCase
{
	public function testDeleteScormTemplateAndSettings()
	{
		$deleteMock = $this->getMockBuilder('ilCertificateTemplateDeleteAction')
			->disableOriginalConstructor()
			->getMock();

		$deleteMock->expects($this->atLeastOnce())
			->method('delete')
			->with(10, 200);

		$settingMock = $this->getMockBuilder('ilSetting')
			->disableOriginalConstructor()
			->getMock();

		$settingMock->expects($this->atLeastOnce())
			->method('delete')
			->with('certificate_200');

		$action = new ilCertificateScormTemplateDeleteAction($deleteMock, $settingMock);

		$action->delete(10, 200);
	}
}
