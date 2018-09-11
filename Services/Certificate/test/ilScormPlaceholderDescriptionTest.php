<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilScormPlaceholderDescriptionTest extends \PHPUnit_Framework_TestCase
{
	public function testPlaceholderGetHtmlDescription()
	{
		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->setMethods(array('txt'))
			->getMock();

		$languageMock = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->setMethods(array('txt'))
			->getMock();

		$templateMock = $this->getMockBuilder('ilTemplate')
			->disableOriginalConstructor()
			->getMock();

		$collectionInstance = $this->getMockBuilder('ilLPCollection')
			->disableOriginalConstructor()
			->setMethods(array('getPossibleItems'))
			->getMock();

		$learningProgressMock = $this->getMockBuilder('ilObjectLP')
			->disableOriginalConstructor()
			->setMethods(array('getCollectionInstance'))
			->getMock();

		$collectionInstance->method('getPossibleItems')
			->willReturn(array(0 => array('title' => 'Some SCORM Title')));

		$learningProgressMock->method('getCollectionInstance')
			->willReturn($collectionInstance);

		$placeholderDescriptionObject = new ilScormPlaceholderDescription(
			$objectMock,
			null,
			$languageMock,
			$learningProgressMock
		);

		$html = $placeholderDescriptionObject->createPlaceholderHtmlDescription($templateMock);

		$this->assertEquals(null, $html);
	}

	public function testPlaceholderDescriptions()
	{
		$objectMock = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->setMethods(array('txt'))
			->getMock();

		$languageMock = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->setMethods(array('txt'))
			->getMock();

		$languageMock->expects($this->exactly(19))
			->method('txt')
			->willReturn('Something translated');

		$learningProgressMock = $this->getMockBuilder('ilObjectLP')
			->disableOriginalConstructor()
			->setMethods(array('getCollectionInstance'))
			->getMock();

		$placeholderDescriptionObject = new ilScormPlaceholderDescription(
			$objectMock,
			null,
			$languageMock,
			$learningProgressMock
		);

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
				'SCORM_TITLE'        => 'Something translated',
				'SCORM_POINTS'       => 'Something translated',
				'SCORM_POINTS_MAX'   => 'Something translated'

			),
			$placeHolders
		);
	}
}
