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
class TestPlaceholderDescriptionTest extends TestCase
{
    public function testPlaceholderGetHtmlDescription(): void
    {
        $language_mock = $this->getMockBuilder(\ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt', 'loadLanguageModule'])
            ->getMock();

        $template_mock = $this->getMockBuilder(\ilTemplate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $template_mock->method('get')
            ->willReturn('');

        $user_defined_placeholder_mock = $this->getMockBuilder(\ilUserDefinedFieldsPlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user_defined_placeholder_mock->method('createPlaceholderHtmlDescription')
            ->willReturn('Something');

        $user_defined_placeholder_mock->method('getPlaceholderDescriptions')
            ->willReturn([]);

        $placeholder_description_object = new TestPlaceholderDescription(
            null,
            $language_mock,
            $user_defined_placeholder_mock
        );

        $html = $placeholder_description_object->createPlaceholderHtmlDescription($template_mock);

        $this->assertSame('', $html);
    }

    public function testPlaceholderDescriptions(): void
    {
        $language_mock = $this->getMockBuilder(\ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt', 'loadLanguageModule'])
            ->getMock();

        $language_mock->expects($this->exactly(26))
            ->method('txt')
            ->willReturn('Something translated');

        $user_defined_placeholder_mock = $this->getMockBuilder(\ilUserDefinedFieldsPlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user_defined_placeholder_mock->method('createPlaceholderHtmlDescription')
            ->willReturn('Something');

        $user_defined_placeholder_mock->method('getPlaceholderDescriptions')
            ->willReturn([]);

        $placeholder_description_object = new TestPlaceholderDescription(
            null,
            $language_mock,
            $user_defined_placeholder_mock
        );

        $placeholders = $placeholder_description_object->getPlaceholderDescriptions();

        $this->assertSame(
            [
                'CERTIFICATE_ID' => 'Something translated',
                'USER_LOGIN' => 'Something translated',
                'USER_FULLNAME' => 'Something translated',
                'USER_FIRSTNAME' => 'Something translated',
                'USER_LASTNAME' => 'Something translated',
                'USER_TITLE' => 'Something translated',
                'USER_SALUTATION' => 'Something translated',
                'USER_BIRTHDAY' => 'Something translated',
                'USER_INSTITUTION' => 'Something translated',
                'USER_DEPARTMENT' => 'Something translated',
                'USER_STREET' => 'Something translated',
                'USER_CITY' => 'Something translated',
                'USER_ZIPCODE' => 'Something translated',
                'USER_COUNTRY' => 'Something translated',
                'USER_MATRICULATION' => 'Something translated',
                'DATE' => 'Something translated',
                'DATETIME' => 'Something translated',
                'RESULT_PASSED' => 'Something translated',
                'RESULT_POINTS' => 'Something translated',
                'RESULT_PERCENT' => 'Something translated',
                'MAX_POINTS' => 'Something translated',
                'RESULT_MARK_SHORT' => 'Something translated',
                'RESULT_MARK_LONG' => 'Something translated',
                'TEST_TITLE' => 'Something translated',
                'DATE_COMPLETED' => 'Something translated',
                'DATETIME_COMPLETED' => 'Something translated'
            ],
            $placeholders
        );
    }
}
