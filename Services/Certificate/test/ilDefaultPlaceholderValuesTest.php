<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilDefaultPlaceholderValuesTest extends ilCertificateBaseTestCase
{
    public function testGetPlaceholderValues() : void
    {
        $objectMock = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
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
                ]
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

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $objectHelper->expects($this->once())
            ->method('getInstanceByObjId')
            ->with(100)
            ->willReturn($objectMock);

        $dateHelper = $this->getMockBuilder(ilCertificateDateHelper::class)
            ->getMock();

        $dateHelper->expects($this->exactly(2))
            ->method('formatDate')
            ->willReturn('2018-09-10');

        $dateHelper->expects($this->once())
            ->method('formatDateTime')
            ->willReturn('2018-09-10 12:01:33');

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturn('Something');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper->method('prepareFormOutput')
            ->willReturnCallback(function ($input) {
                return $input;
            });

        $userDefinePlaceholderMock = $this->getMockBuilder(ilUserDefinedFieldsPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userDefinePlaceholderMock->method('getPlaceholderValues')
            ->willReturn([]);

        $userDefinePlaceholderMock->method('getPlaceholderValuesForPreview')
            ->willReturn([]);

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
            [
                'USER_LOGIN' => 'a_login',
                'USER_FULLNAME' => 'Niels Theen',
                'USER_FIRSTNAME' => 'Niels',
                'USER_LASTNAME' => 'Theen',
                'USER_TITLE' => '',
                'USER_SALUTATION' => 'Something',
                'USER_BIRTHDAY' => '2018-09-10',
                'USER_INSTITUTION' => '',
                'USER_DEPARTMENT' => '',
                'USER_STREET' => '',
                'USER_CITY' => '',
                'USER_ZIPCODE' => '',
                'USER_COUNTRY' => '',
                'USER_MATRICULATION' => '',
                'DATE_COMPLETED' => '',
                'DATETIME_COMPLETED' => '',
                'DATE' => '2018-09-10',
                'DATETIME' => '2018-09-10 12:01:33'
            ],
            $result
        );
    }

    public function testGetPlaceholderValuesForPreview() : void
    {
        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $dateHelper = $this->getMockBuilder(ilCertificateDateHelper::class)
            ->getMock();

        $dateHelper->method('formatDate')
            ->willReturn('2018-09-09');

        $dateHelper->method('formatDateTime')
            ->willReturn('2018-09-09 14:00:30');

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturn('Something');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper->method('prepareFormOutput')
            ->willReturnCallback(function ($input) {
                return $input;
            });

        $userDefinePlaceholderMock = $this->getMockBuilder(ilUserDefinedFieldsPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userDefinePlaceholderMock->method('getPlaceholderValues')
            ->willReturn([]);

        $userDefinePlaceholderMock->method('getPlaceholderValuesForPreview')
            ->willReturn([]);

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
            10
        );

        $this->assertSame(
            [
                'USER_LOGIN' => 'Something',
                'USER_FULLNAME' => 'Something',
                'USER_FIRSTNAME' => 'Something',
                'USER_LASTNAME' => 'Something',
                'USER_TITLE' => 'Something',
                'USER_SALUTATION' => 'Something',
                'USER_BIRTHDAY' => '2018-09-09',
                'USER_INSTITUTION' => 'Something',
                'USER_DEPARTMENT' => 'Something',
                'USER_STREET' => 'Something',
                'USER_CITY' => 'Something',
                'USER_ZIPCODE' => 'Something',
                'USER_COUNTRY' => 'Something',
                'USER_MATRICULATION' => 'Something',
                'DATE' => '2018-09-09',
                'DATETIME' => '2018-09-09 14:00:30',
                'DATE_COMPLETED' => '2018-09-09',
                'DATETIME_COMPLETED' => '2018-09-09 14:00:30'
            ],
            $result
        );
    }
}
