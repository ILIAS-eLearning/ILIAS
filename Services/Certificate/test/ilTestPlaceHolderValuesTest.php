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
class ilTestPlaceholderValuesTest extends ilCertificateBaseTestCase
{
    public function testA(): void
    {
        $defaultPlaceholderValues = $this->getMockBuilder(ilDefaultPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturn('Some Translation');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $testObject = $this->getMockBuilder(ilObjTest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testObject->method('getActiveIdOfUser')
            ->willReturn(999);

        $testObject->method('getTestResult')
            ->willReturn(
                [
                    'test' => [
                        'passed' => true,
                        'total_max_points' => 70,
                        'total_reached_points' => 50
                    ]
                ]
            );

        $testObject->method('getTestResult')
            ->willReturn([]);


        $testObject->method('getTitle')
            ->willReturn(' Some Title');

        $markSchema = $this->getMockBuilder('ASS_MarkSchema')
            ->disableOriginalConstructor()
            ->getMock();

        $matchingMark = $this->getMockBuilder('ASS_Mark')
            ->getMock();

        $matchingMark->method('getShortName')
            ->willReturn('aaa');

        $matchingMark->method('getOfficialName')
            ->willReturn('bbb');

        $markSchema->method('getMatchingMark')
            ->willReturn($matchingMark);

        $testObject->method('getMarkSchema')
            ->willReturn($markSchema);

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($testObject);

        $testObjectHelper = $this->getMockBuilder(ilCertificateTestObjectHelper::class)
            ->getMock();

        $userObjectHelper = $this->getMockBuilder(ilCertificateUserObjectHelper::class)
            ->getMock();

        $userObjectHelper->method('lookupFields')
            ->willReturn(['usr_id' => 10]);

        $lpStatusHelper = $this->getMockBuilder(ilCertificateLPStatusHelper::class)
            ->getMock();

        $lpStatusHelper->method('lookupStatusChanged')
            ->willReturn('2018-01-12');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper->method('prepareFormOutput')
            ->willReturn('Formatted Output');

        $dateHelper = $this->getMockBuilder(ilCertificateDateHelper::class)
            ->getMock();

        $dateHelper->method('formatDate')
            ->willReturn('2018-01-12');

        $dateHelper->method('formatDateTime')
            ->willReturn('2018-01-12 10:32:01');

        $placeholdervalues = new ilTestPlaceholderValues(
            $defaultPlaceholderValues,
            $language,
            $objectHelper,
            $testObjectHelper,
            $userObjectHelper,
            $lpStatusHelper,
            $utilHelper,
            $dateHelper
        );

        $result = $placeholdervalues->getPlaceholderValues(10, 200);

        $this->assertSame([
            'RESULT_PASSED' => 'Formatted Output',
            'RESULT_POINTS' => 'Formatted Output',
            'RESULT_PERCENT' => '71.43%',
            'MAX_POINTS' => 'Formatted Output',
            'RESULT_MARK_SHORT' => 'Formatted Output',
            'RESULT_MARK_LONG' => 'Formatted Output',
            'TEST_TITLE' => 'Formatted Output',
            'DATE_COMPLETED' => '2018-01-12',
            'DATETIME_COMPLETED' => '2018-01-12 10:32:01'

        ], $result);
    }

    public function testGetPlaceholderValuesForPreview(): void
    {
        $defaultPlaceholderValues = $this->getMockBuilder(ilDefaultPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultPlaceholderValues->method('getPlaceholderValuesForPreview')
            ->willReturn(
                [
                    'SOME_PLACEHOLDER' => 'something',
                    'SOME_OTHER_PLACEHOLDER' => 'something else',
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

        $testObjectHelper = $this->getMockBuilder(ilCertificateTestObjectHelper::class)
            ->getMock();

        $userObjectHelper = $this->getMockBuilder(ilCertificateUserObjectHelper::class)
            ->getMock();

        $lpStatusHelper = $this->getMockBuilder(ilCertificateLPStatusHelper::class)
            ->getMock();

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper->method('prepareFormOutput')
            ->willReturnCallback(function ($input) {
                return $input;
            });

        $dateHelper = $this->getMockBuilder(ilCertificateDateHelper::class)
            ->getMock();

        $placeholdervalues = new ilTestPlaceholderValues(
            $defaultPlaceholderValues,
            $language,
            $objectHelper,
            $testObjectHelper,
            $userObjectHelper,
            $lpStatusHelper,
            $utilHelper,
            $dateHelper
        );

        $result = $placeholdervalues->getPlaceholderValuesForPreview(100, 10);

        $this->assertSame(
            [
                'SOME_PLACEHOLDER' => 'something',
                'SOME_OTHER_PLACEHOLDER' => 'something else',
                'RESULT_PASSED' => 'Something',
                'RESULT_POINTS' => 'Something',
                'RESULT_PERCENT' => 'Something',
                'MAX_POINTS' => 'Something',
                'RESULT_MARK_SHORT' => 'Something',
                'RESULT_MARK_LONG' => 'Something',
                'TEST_TITLE' => 'SomeTitle'
            ],
            $result
        );
    }
}
