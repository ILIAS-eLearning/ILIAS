<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilDefaultPlaceholderValuesTest extends PHPUnit_Framework_TestCase
{
	public function testGetPlaceholderValues()
	{
		$objectMock = $this->getMockBuilder('ilObjUser')
			->disableOriginalConstructor()
			->setMethods(
				array(
					'getLogin',
					'getFullname',
					'getFirstname',
					'getLastname',
					'getUTitle',
					'getGender',
					'getBirthday',
					'getInstitution',
					'getDepartment',
					'getStreet',
					'getCity',
					'getZipcode',
					'getCountry',
					'getMatriculation'
				)
			)
			->getMock();

		$objectMock->expects($this->once())
			->method('getLogin')
			->willReturn('a_login');

		$objectMock->expects($this->once())
			->method('getFullname')
			->willReturn('Niels Theen');

		$objectMock->expects($this->once())
			->method('getFirstname')
			->willReturn('Niels');

		$objectMock->expects($this->once())
			->method('getLastname')
			->willReturn('Theen');

		$objectMock->expects($this->once())
			->method('getUTitle')
			->willReturn('');

		$objectMock->expects($this->once())
			->method('getGender')
			->willReturn('m');

		$objectMock->expects($this->once())
			->method('getBirthday')
			->willReturn('2018-10-10');

		$objectMock->expects($this->once())
			->method('getInstitution')
			->willReturn('');

		$objectMock->expects($this->once())
			->method('getDepartment')
			->willReturn('');

		$objectMock->expects($this->once())
			->method('getStreet')
			->willReturn('');

		$objectMock->expects($this->once())
			->method('getCity')
			->willReturn('');

		$objectMock->expects($this->once())
			->method('getZipcode')
			->willReturn('');

		$objectMock->expects($this->once())
			->method('getCountry')
			->willReturn('');

		$objectMock->expects($this->once())
			->method('getMatriculation')
			->willReturn('');

		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->getMock();

		$objectHelper->expects($this->once())
			->method('getInstanceByObjId')
			->with(100)
			->willReturn($objectMock);

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->getMock();

		$dateHelper->expects($this->exactly(2))
			->method('formatDate')
			->willReturn('2018-09-10');

		$dateHelper->expects($this->once())
			->method('formatDateTime')
			->willReturn('2018-09-10 12:01:33');

		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$language->method('txt')
			->willReturn('Something');

		$utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
			->getMock();

		$utilHelper->method('prepareFormOutput')
			->willReturnCallback(function ($input) {
				return $input;
			});

		$userDefinePlaceholderMock = $this->getMockBuilder('ilUserDefinedFieldsPlaceholderValues')
			->disableOriginalConstructor()
			->getMock();

		$userDefinePlaceholderMock->method('getPlaceholderValues')
			->willReturn(array());

		$userDefinePlaceholderMock->method('getPlaceholderValuesForPreview')
			->willReturn(array());

		$placeHolderObject = new ilDefaultPlaceholderValues(
			$objectHelper,
			$dateHelper,
			1,
			$language,
			$utilHelper,
			$userDefinePlaceholderMock,
			1
		);

		$result = $placeHolderObject->getPlaceholderValues(100, 200);

		$this->assertEquals(
			array(
				'USER_LOGIN'         => 'a_login',
				'USER_FULLNAME'      => 'Niels Theen',
				'USER_FIRSTNAME'     => 'Niels',
				'USER_LASTNAME'      => 'Theen',
				'USER_TITLE'         => '',
				'USER_SALUTATION'    => 'Something',
				'USER_BIRTHDAY'      => '2018-09-10',
				'USER_INSTITUTION'   => '',
				'USER_DEPARTMENT'    => '',
				'USER_STREET'        => '',
				'USER_CITY'          => '',
				'USER_ZIPCODE'       => '',
				'USER_COUNTRY'       => '',
				'USER_MATRICULATION' => '',
				'DATE'               => '',
				'DATETIME'           => '',
				'DATE_COMPLETED'     => '',
				'DATETIME_COMPLETED' => '',
				'DATE'               => '2018-09-10',
				'DATETIME'           => '2018-09-10 12:01:33'
			),
			$result
		);
	}

	public function testGetPlaceholderValuesForPreview()
	{
		$objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
			->getMock();

		$dateHelper = $this->getMockBuilder('ilCertificateDateHelper')
			->getMock();

		$dateHelper->method('formatDate')
			->willReturn('2018-09-09');

		$dateHelper->method('formatDateTime')
			->willReturn('2018-09-09 14:00:30');

		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$language->method('txt')
			->willReturn('Something');

		$utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
			->getMock();

		$utilHelper->method('prepareFormOutput')
			->willReturnCallback(function ($input) {
				return $input;
			});

		$userDefinePlaceholderMock = $this->getMockBuilder('ilUserDefinedFieldsPlaceholderValues')
			->disableOriginalConstructor()
			->getMock();

		$userDefinePlaceholderMock->method('getPlaceholderValues')
			->willReturn(array());

		$userDefinePlaceholderMock->method('getPlaceholderValuesForPreview')
			->willReturn(array());

		$placeHolderObject = new ilDefaultPlaceholderValues(
			$objectHelper,
			$dateHelper,
			1,
			$language,
			$utilHelper,
			$userDefinePlaceholderMock,
			1
		);

		$result = $placeHolderObject->getPlaceholderValuesForPreview(
			100,
			10,
			2
		);

		$this->assertEquals(
			array(
				'USER_LOGIN'         => 'Something',
				'USER_FULLNAME'      => 'Something',
				'USER_FIRSTNAME'     => 'Something',
				'USER_LASTNAME'      => 'Something',
				'USER_TITLE'         => 'Something',
				'USER_SALUTATION'    => 'Something',
				'USER_BIRTHDAY'      => '2018-09-09',
				'USER_INSTITUTION'   => 'Something',
				'USER_DEPARTMENT'    => 'Something',
				'USER_STREET'        => 'Something',
				'USER_CITY'          => 'Something',
				'USER_ZIPCODE'       => 'Something',
				'USER_COUNTRY'       => 'Something',
				'USER_MATRICULATION' => 'Something',
				'DATE'               => '2018-09-09',
				'DATETIME'           => '2018-09-09 14:00:30',
				'DATE_COMPLETED'     => '2018-09-09',
				'DATETIME_COMPLETED' => '2018-09-09 14:00:30'
			),
			$result
		);
	}
}
