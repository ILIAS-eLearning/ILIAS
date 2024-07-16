<?php

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

declare(strict_types=1);

namespace ILIAS\Test\Certificate;

use PHPUnit\Framework\TestCase;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class TestPlaceholderValuesTest extends TestCase
{
    public function testA(): void
    {
        $default_placeholder_values = $this->getMockBuilder(\ilDefaultPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder(\ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturn('Some Translation');

        $object_helper = $this->getMockBuilder(\ilCertificateObjectHelper::class)
            ->getMock();

        $test_object = $this->getMockBuilder(\ilObjTest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $test_object->method('getActiveIdOfUser')
            ->willReturn(999);

        $test_object->method('getTestResult')
            ->willReturn(
                [
                    'test' => [
                        'passed' => true,
                        'total_max_points' => 70,
                        'total_reached_points' => 50
                    ]
                ]
            );

        $test_object->method('getTestResult')
            ->willReturn([]);

        $test_object->method('getTitle')
            ->willReturn(' Some Title');

        $mark_schema = $this->getMockBuilder(\ILIAS\Test\Scoring\Marks\MarkSchema::class)
            ->disableOriginalConstructor()
            ->getMock();

        $matching_mark = $this->getMockBuilder(\ILIAS\Test\Scoring\Marks\Mark::class)
            ->getMock();

        $matching_mark->method('getShortName')
            ->willReturn('aaa');

        $matching_mark->method('getOfficialName')
            ->willReturn('bbb');

        $mark_schema->method('getMatchingMark')
            ->willReturn($matching_mark);

        $test_object->method('getMarkSchema')
            ->willReturn($mark_schema);

        $object_helper->method('getInstanceByObjId')
            ->willReturn($test_object);

        $test_object_helper = $this->getMockBuilder(CertificateTestObjectHelper::class)
            ->getMock();

        $user_object_helper = $this->getMockBuilder(\ilCertificateUserObjectHelper::class)
            ->getMock();

        $user_object_helper->method('lookupFields')
            ->willReturn(['usr_id' => 10]);

        $lp_status_helper = $this->getMockBuilder(\ilCertificateLPStatusHelper::class)
            ->getMock();

        $lp_status_helper->method('lookupStatusChanged')
            ->willReturn('2018-01-12');

        $util_helper = $this->getMockBuilder(\ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $util_helper->method('prepareFormOutput')
            ->willReturn('Formatted Output');

        $date_helper = $this->getMockBuilder(\ilCertificateDateHelper::class)
            ->getMock();

        $date_helper->method('formatDate')
            ->willReturn('2018-01-12');

        $date_helper->method('formatDateTime')
            ->willReturn('2018-01-12 10:32:01');

        $placeholder_values = new TestPlaceholderValues(
            $default_placeholder_values,
            $language,
            $object_helper,
            $test_object_helper,
            $user_object_helper,
            $lp_status_helper,
            $util_helper,
            $date_helper
        );

        $result = $placeholder_values->getPlaceholderValues(10, 200);

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
        $default_placeholder_values = $this->getMockBuilder(\ilDefaultPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $default_placeholder_values->method('getPlaceholderValuesForPreview')
            ->willReturn(
                [
                    'SOME_PLACEHOLDER' => 'something',
                    'SOME_OTHER_PLACEHOLDER' => 'something else',
                ]
            );

        $language = $this->getMockBuilder(\ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturn('Something');

        $object_mock = $this->getMockBuilder(\ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object_mock->method('getTitle')
            ->willReturn('SomeTitle');

        $object_helper = $this->getMockBuilder(\ilCertificateObjectHelper::class)
            ->getMock();

        $object_helper->method('getInstanceByObjId')
            ->willReturn($object_mock);

        $test_object_helper = $this->getMockBuilder(CertificateTestObjectHelper::class)
            ->getMock();

        $user_object_helper = $this->getMockBuilder(\ilCertificateUserObjectHelper::class)
            ->getMock();

        $lp_status_helper = $this->getMockBuilder(\ilCertificateLPStatusHelper::class)
            ->getMock();

        $util_helper = $this->getMockBuilder(\ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $util_helper->method('prepareFormOutput')
            ->willReturnCallback(function ($input) {
                return $input;
            });

        $date_helper = $this->getMockBuilder(\ilCertificateDateHelper::class)
            ->getMock();

        $placeholder_values = new TestPlaceholderValues(
            $default_placeholder_values,
            $language,
            $object_helper,
            $test_object_helper,
            $user_object_helper,
            $lp_status_helper,
            $util_helper,
            $date_helper
        );

        $result = $placeholder_values->getPlaceholderValuesForPreview(100, 10);

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
