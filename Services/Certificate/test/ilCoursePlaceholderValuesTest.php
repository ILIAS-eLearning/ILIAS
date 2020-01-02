<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderValuesTest extends PHPUnit_Framework_TestCase
{
    public function testGetPlaceholderValues()
    {
        $defaultPlaceholderValues = $this->getMockBuilder('ilDefaultPlaceholderValues')
             ->disableOriginalConstructor()
             ->getMock();

        $defaultPlaceholderValues->method('getPlaceholderValues')
             ->willReturn(array());

        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturn('Something');

        $objectMock = $this->getMockBuilder('ilObject')
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('Some Title');

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $participantsHelper = $this->getMockBuilder('ilCertificateParticipantsHelper')
            ->getMock();

        $participantsHelper->method('getDateTimeOfPassed')
            ->willReturn('2018-09-10');

        $ilUtilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $ilUtilHelper->method('prepareFormOutput')
            ->willReturn('Some Title');

        $ilDateHelper = $this->getMockBuilder('ilCertificateDateHelper')
            ->getMock();

        $ilDateHelper->method('formatDate')
            ->willReturn('2018-09-10');

        $ilDateHelper->method('formatDateTime')
            ->willReturn('2018-09-10 10:32:00');

        $valuesObject = new ilCoursePlaceholderValues(
            $defaultPlaceholderValues,
            $language,
            $objectHelper,
            $participantsHelper,
            $ilUtilHelper,
            $ilDateHelper
        );

        $placeholderValues = $valuesObject->getPlaceholderValues(100, 200);

        $this->assertEquals(
            array(
                'COURSE_TITLE'       => 'Some Title',
                'DATE_COMPLETED'     => '2018-09-10',
                'DATETIME_COMPLETED' => '2018-09-10 10:32:00'
            ),
            $placeholderValues
        );
    }

    public function testGetPreviewPlaceholderValues()
    {
        $defaultPlaceholderValues = $this->getMockBuilder('ilDefaultPlaceholderValues')
            ->disableOriginalConstructor()
            ->getMock();

        $defaultPlaceholderValues->method('getPlaceholderValuesForPreview')
            ->willReturn(
                array(
                    'SOME_PLACEHOLDER'       => 'ANYTHING',
                    'SOME_OTHER_PLACEHOLDER'  => '2018-09-10',
                )
            );

        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturn('Something');

        $objectMock = $this->getMockBuilder('ilObject')
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('SomeTitle');

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $participantsHelper = $this->getMockBuilder('ilCertificateParticipantsHelper')
            ->getMock();

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $utilHelper->method('prepareFormOutput')
            ->willReturnCallback(function ($input) {
                return $input;
            });

        $valuesObject = new ilCoursePlaceholderValues(
            $defaultPlaceholderValues,
            $language,
            $objectHelper,
            $participantsHelper,
            $utilHelper
        );

        $placeholderValues = $valuesObject->getPlaceholderValuesForPreview(100, 10);

        $this->assertEquals(
            array(
                'SOME_PLACEHOLDER'        => 'ANYTHING',
                'SOME_OTHER_PLACEHOLDER'  => '2018-09-10',
                'COURSE_TITLE'            => 'SomeTitle'
            ),
            $placeholderValues
        );
    }
}
