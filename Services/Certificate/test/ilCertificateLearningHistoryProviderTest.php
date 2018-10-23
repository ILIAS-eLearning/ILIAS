<?php

class ilCertificateLearningHistoryProviderTest extends PHPUnit_Framework_TestCase
{
	public function testIsActive()
	{
		$provider = $this->createDefaultProvider();

		$this->assertTrue($provider->isActive());
	}

//	public function testGetEntries()
//	{
//		$provider = $this->createDefaultProvider();
//
//		$expectedEntries = array();
//
//		$this->assertEquals($provider->getEntries(123456789, 987654321));
//	}

	/**
	 * @return ilCertificateLearningHistoryProvider
	 */
	private function createDefaultProvider(): ilCertificateLearningHistoryProvider
	{
		$learningHistoryFactory = $this->getMockBuilder('ilLearningHistoryFactory')
			->disableOriginalConstructor()
			->getMock();

		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$dic = $this->getMockBuilder('\ILIAS\DI\Container')
			->disableOriginalConstructor()
			->getMock();

		$userCertificateRepository = $this->getMockBuilder('ilUserCertificateRepository')
			->disableOriginalConstructor()
			->getMock();

		$result =
			array(
				new ilUserCertificatePresentation(
					new ilUserCertificate(
						10,
						200,
						'crs',
						3000,
						'ilyas',
						123456789,
						'someContent',
						'',
						null,
						'1',
						'v5.4.0',
						true,
						'some/path/background.jpg',
						'some/path/thumbnail.svg',
						500
					),
					'someTitle',
					'someDescription'
				),
				new ilUserCertificatePresentation(
					new ilUserCertificate(
						20,
						222,
						'crs',
						3333,
						'ilyas',
						123456789,
						'someContent',
						'',
						null,
						'1',
						'v5.4.0',
						true,
						'some/path/background2.jpg',
						'some/path/thumbnail2.svg',
						500
					),
					'someTitle',
					'someDescription'
				)
			);

		$userCertificateRepository->method('fetchActiveCertificatesInIntervalForPresentation')
			->willReturn($result);

		$controller = $this->getMockBuilder('ilCtrl')
			->disableOriginalConstructor()
			->getMock();

		$certificateSettings = $this->getMockBuilder('ilSetting')
			->disableOriginalConstructor()
			->getMock();

		$certificateSettings->method('get')
			->willReturn(true);

		$uiFactory = $this->getMockBuilder('ILIAS\UI\Factory')
			->disableOriginalConstructor()
			->getMock();

		$uiRenderer = $this->getMockBuilder('ILIAS\UI\Renderer')
			->disableOriginalConstructor()
			->getMock();

		$provider = new ilCertificateLearningHistoryProvider(
			10,
			$learningHistoryFactory,
			$language,
			$dic,
			$userCertificateRepository,
			$controller,
			$certificateSettings,
			$uiFactory,
			$uiRenderer
		);
		return $provider;
	}
}
