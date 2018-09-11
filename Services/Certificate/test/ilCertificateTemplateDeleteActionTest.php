<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateDeleteActionTest extends \PHPUnit_Framework_TestCase
{
	public function testDeleteTemplateAndUseOldThumbnail()
	{
		$filesystemMock = $this->getMockBuilder('\ILIAS\Filesystem\Filesystem')
			->getMock();

		$filesystemMock->expects($this->atLeastOnce())->method('copy');

		$templateRepositoryMock = $this->getMockBuilder('ilCertificateTemplateRepository')
			->disableOriginalConstructor()
			->getMock();

		$templateRepositoryMock->method('deleteTemplate')->with(100, 2000);
		$templateRepositoryMock->method('activatePreviousCertificate')
			->with(2000)
			->willReturn(new ilCertificateTemplate(
				2000,
				'crs',
				'something',
				md5('something'),
				'[]',
				'1',
				'v5.4.0',
				1234567890,
				true,
				'samples/background.jpg'
			));

		$action = new ilCertificateTemplateDeleteAction(
			$templateRepositoryMock,
			$filesystemMock,
			__DIR__
		);

		$action->delete(100, 2000);
	}

	public function testDeleteTemplateButNoThumbnailWillBeCopiedFromOldCertificate()
	{
		$filesystemMock = $this->getMockBuilder('\ILIAS\Filesystem\Filesystem')
			->getMock();

		$filesystemMock->expects($this->never())
			->method('copy');

		$templateRepositoryMock = $this->getMockBuilder('ilCertificateTemplateRepository')
			->disableOriginalConstructor()
			->getMock();

		$templateRepositoryMock->method('deleteTemplate')->with(100, 2000);
		$templateRepositoryMock->method('activatePreviousCertificate')
			->with(2000)
			->willReturn(new ilCertificateTemplate(
				2000,
				'crs',
				'something',
				md5('something'),
				'[]',
				'1',
				'v5.4.0',
				1234567890,
				true
			));

		$action = new ilCertificateTemplateDeleteAction(
			$templateRepositoryMock,
			$filesystemMock,
			__DIR__
		);

		$action->delete(100, 2000);
	}
}
