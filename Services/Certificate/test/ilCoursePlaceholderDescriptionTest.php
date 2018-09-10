<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/Certificate/classes/Placeholder/Description/interface.ilCertificatePlaceholderDescription.php';
require_once 'Services/Certificate/classes/Placeholder/Description/class.ilCoursePlaceholderDescription.php';
require_once 'Services/Certificate/classes/Placeholder/Description/class.ilDefaultPlaceholderDescription.php';
require_once 'Services/UICore/classes/class.ilTemplate.php';

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderDescriptionTest extends \PHPUnit_Framework_TestCase
{
	public function testPlaceholderGetHtmlDescription()
	{
		$languageMock = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->setMethods(array('txt'))
			->getMock();

		$templateMock = $this->getMockBuilder('ilTemplate')
			->disableOriginalConstructor()
			->getMock();

		$placeholderDescriptionObject = new ilCoursePlaceholderDescription(null, $languageMock);

		$html = $placeholderDescriptionObject->createPlaceholderHtmlDescription($templateMock);

		$this->assertEquals(null, $html);
	}

	public function testPlaceholderDescriptions()
	{
		$languageMock = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->setMethods(array('txt'))
			->getMock();

		$languageMock->expects($this->exactly(17))
			->method('txt')
			->willReturn('Something translated');

		$placeholderDescriptionObject = new ilCoursePlaceholderDescription(null, $languageMock);

		$placeHolders = $placeholderDescriptionObject->getPlaceholderDescriptions();

		$this->assertEquals(
			array(
				'USER_LOGIN'         => 'Something translated',
				'USER_FULLNAME'      => 'Something translated',
				'USER_FIRSTNAME'     => 'Something translated',
				'USER_LASTNAME'      => 'Something translated',
				'USER_TITLE'         => 'Something translated',
				'USER_SALUTATION'    => 'Something translated',
				'USER_BIRTHDAY'      => 'Something translated',
				'USER_INSTITUTION'   => 'Something translated',
				'USER_DEPARTMENT'    => 'Something translated',
				'USER_STREET'        => 'Something translated',
				'USER_CITY'          => 'Something translated',
				'USER_ZIPCODE'       => 'Something translated',
				'USER_COUNTRY'       => 'Something translated',
				'USER_MATRICULATION' => 'Something translated',
				'DATE'               => 'Something translated',
				'DATETIME'           => 'Something translated',
				'COURSE_TITLE'       => 'Something translated'
			),
			$placeHolders
		);
	}
}
