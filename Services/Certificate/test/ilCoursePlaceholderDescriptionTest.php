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
class ilCoursePlaceholderDescriptionTest extends ilCertificateBaseTestCase
{
    public function testPlaceholderGetHtmlDescription() : void
    {
        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt', 'loadLanguageModule'])
            ->getMock();

        $templateMock = $this->getMockBuilder(ilTemplate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->method('get')
            ->willReturn('');

        $userDefinePlaceholderMock = $this->getMockBuilder(ilUserDefinedFieldsPlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userDefinePlaceholderMock->method('createPlaceholderHtmlDescription')
            ->willReturn('');

        $userDefinePlaceholderMock->method('getPlaceholderDescriptions')
            ->willReturn([]);

        $customUserPlaceholderObject = $this->getMockBuilder(ilObjectCustomUserFieldsPlaceholderDescription::class)
                                            ->disableOriginalConstructor()
                                            ->getMock();

        $customUserPlaceholderObject->method("getPlaceholderDescriptions")
                                    ->willReturn([
                                        '+SOMETHING' => 'SOMEWHAT',
                                        '+SOMETHING_ELSE' => 'ANYTHING'
                                    ]);

        $customUserPlaceholderObject->method('createPlaceholderHtmlDescription')
                                  ->willReturn('');

        $placeholderDescriptionObject = new ilCoursePlaceholderDescription(200, null, $languageMock, $userDefinePlaceholderMock, $customUserPlaceholderObject);

        $html = $placeholderDescriptionObject->createPlaceholderHtmlDescription($templateMock);

        $this->assertSame('', $html);
    }

    public function testPlaceholderDescriptions() : void
    {
        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt'])
            ->getMock();

        $languageMock->expects($this->exactly(3))
                     ->method('txt')
                     ->willReturn('Something translated');

        $defaultPlaceholder = $this->getMockBuilder(ilDefaultPlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultPlaceholder->method('getPlaceholderDescriptions')
            ->willReturn(
                [
                    'SOMETHING' => 'SOMEWHAT',
                    'SOMETHING_ELSE' => 'ANYTHING'
                ]
            );

        $customUserPlaceholderObject = $this->getMockBuilder(ilObjectCustomUserFieldsPlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customUserPlaceholderObject->method('getPlaceholderDescriptions')
            ->willReturn(
                [
                    '+SOMETHING' => 'SOMEWHAT',
                    '+SOMETHING_ELSE' => 'ANYTHING'
                ]
            );

        $placeholderDescriptionObject = new ilCoursePlaceholderDescription(200, $defaultPlaceholder, $languageMock, null, $customUserPlaceholderObject);

        $placeHolders = $placeholderDescriptionObject->getPlaceholderDescriptions();

        $this->assertEquals(
            [
                'COURSE_TITLE' => 'Something translated',
                'SOMETHING' => 'SOMEWHAT',
                'SOMETHING_ELSE' => 'ANYTHING',
                '+SOMETHING' => 'SOMEWHAT',
                '+SOMETHING_ELSE' => 'ANYTHING',
                'DATE_COMPLETED' => 'Something translated',
                'DATETIME_COMPLETED' => 'Something translated'
            ],
            $placeHolders
        );
    }
}
