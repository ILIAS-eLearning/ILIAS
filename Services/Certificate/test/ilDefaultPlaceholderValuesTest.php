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
					'getTitle',
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
			->method('getTitle')
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

		$dateHelper->expects($this->once())
			->method('formatDate')
			->willReturn('2018-09-10');

		$dateHelper->expects($this->once())
			->method('formatDateTime')
			->willReturn('2018-09-10 12:01:33');


		$placeHolderObject = new ilDefaultPlaceholderValues(
			$objectHelper,
			$dateHelper,
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
				'USER_SALUTATION'    => 'm',
				'USER_BIRTHDAY'      => '2018-10-10',
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
				'CLIENT_WEB_DIR'     => '',
				'DATE'               => '2018-09-10',
				'DATETIME'          => '2018-09-10 12:01:33'
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

		$placeHolderObject = new ilDefaultPlaceholderValues(
			$objectHelper,
			$dateHelper,
			1
		);

		$result = $placeHolderObject->getPlaceholderValuesForPreview();

		$this->assertEquals(
			array(
				'USER_LOGIN'         => '',
				'USER_FULLNAME'      => '',
				'USER_FIRSTNAME'     => '',
				'USER_LASTNAME'      => '',
				'USER_TITLE'         => '',
				'USER_SALUTATION'    => '',
				'USER_BIRTHDAY'      => '',
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
				'CLIENT_WEB_DIR'     => ''
			),
			$result
		);
	}
}
