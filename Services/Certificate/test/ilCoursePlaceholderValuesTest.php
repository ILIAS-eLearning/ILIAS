<?php

declare(strict_types=1);

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
class ilCoursePlaceholderValuesTest extends ilCertificateBaseTestCase
{
    public function testGetPlaceholderValues(): void
    {
        $customUserFieldsPlaceholderValues = $this->getMockBuilder(ilObjectCustomUserFieldsPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customUserFieldsPlaceholderValues->method('getPlaceholderValues')
                                 ->willReturn([]);

        $defaultPlaceholderValues = $this->getMockBuilder(ilDefaultPlaceholderValues::class)
             ->disableOriginalConstructor()
             ->getMock();

        $defaultPlaceholderValues->method('getPlaceholderValues')
             ->willReturn([]);

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturn('Something');

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('Some Title');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $participantsHelper = $this->getMockBuilder(ilCertificateParticipantsHelper::class)
            ->getMock();

        $participantsHelper->method('getDateTimeOfPassed')
            ->willReturn('2018-09-10');

        $ilUtilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $ilUtilHelper->method('prepareFormOutput')
            ->willReturn('Some Title');

        $ilDateHelper = $this->getMockBuilder(ilCertificateDateHelper::class)
            ->getMock();

        $ilDateHelper->method('formatDate')
            ->willReturn('2018-09-10');

        $ilDateHelper->method('formatDateTime')
            ->willReturn('2018-09-10 10:32:00');

        $valuesObject = new ilCoursePlaceholderValues(
            $customUserFieldsPlaceholderValues,
            $defaultPlaceholderValues,
            $language,
            $objectHelper,
            $participantsHelper,
            $ilUtilHelper,
            $ilDateHelper
        );

        $placeholderValues = $valuesObject->getPlaceholderValues(100, 200);

        $this->assertEquals(
            [
                'COURSE_TITLE' => 'Some Title',
                'DATE_COMPLETED' => '2018-09-10',
                'DATETIME_COMPLETED' => '2018-09-10 10:32:00'
            ],
            $placeholderValues
        );
    }

    public function testGetPreviewPlaceholderValues(): void
    {
        $customUserFieldsPlaceholderValues = $this->getMockBuilder(ilObjectCustomUserFieldsPlaceholderValues::class)
              ->disableOriginalConstructor()
              ->getMock();

        $customUserFieldsPlaceholderValues->method('getPlaceholderValuesForPreview')
             ->willReturn(
                 [
                     'SOME_PLACEHOLDER' => 'ANYTHING',
                     'SOME_OTHER_PLACEHOLDER' => '2018-09-10',
                 ]
             );

        $defaultPlaceholderValues = $this->getMockBuilder(ilDefaultPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultPlaceholderValues->method('getPlaceholderValuesForPreview')
            ->willReturn(
                [
                    'SOME_PLACEHOLDER' => 'ANYTHING',
                    'SOME_OTHER_PLACEHOLDER' => '2018-09-10',
                ]
            );

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturn('Something');

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('SomeTitle');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $participantsHelper = $this->getMockBuilder(ilCertificateParticipantsHelper::class)
            ->getMock();

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper->method('prepareFormOutput')
            ->willReturnCallback(function ($input) {
                return $input;
            });

        $valuesObject = new ilCoursePlaceholderValues(
            $customUserFieldsPlaceholderValues,
            $defaultPlaceholderValues,
            $language,
            $objectHelper,
            $participantsHelper,
            $utilHelper
        );

        $placeholderValues = $valuesObject->getPlaceholderValuesForPreview(100, 10);

        $this->assertSame(
            [
                'SOME_PLACEHOLDER' => 'ANYTHING',
                'SOME_OTHER_PLACEHOLDER' => '2018-09-10',
                'COURSE_TITLE' => 'SomeTitle'
            ],
            $placeholderValues
        );
    }
}
