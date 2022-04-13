<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */
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
                                    ->willReturn(array(
                                        '+SOMETHING' => 'SOMEWHAT',
                                        '+SOMETHING_ELSE' => 'ANYTHING'
                                    ));

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
                array(
                    'SOMETHING' => 'SOMEWHAT',
                    'SOMETHING_ELSE' => 'ANYTHING'
                )
            );

        $customUserPlaceholderObject = $this->getMockBuilder(ilObjectCustomUserFieldsPlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customUserPlaceholderObject->method('getPlaceholderDescriptions')
            ->willReturn(
                array(
                    '+SOMETHING' => 'SOMEWHAT',
                    '+SOMETHING_ELSE' => 'ANYTHING'
                )
            );

        $placeholderDescriptionObject = new ilCoursePlaceholderDescription(200, $defaultPlaceholder, $languageMock, null, $customUserPlaceholderObject);

        $placeHolders = $placeholderDescriptionObject->getPlaceholderDescriptions();

        $this->assertEquals(
            array(
                'COURSE_TITLE' => 'Something translated',
                'SOMETHING' => 'SOMEWHAT',
                'SOMETHING_ELSE' => 'ANYTHING',
                '+SOMETHING' => 'SOMEWHAT',
                '+SOMETHING_ELSE' => 'ANYTHING',
                'DATE_COMPLETED' => 'Something translated',
                'DATETIME_COMPLETED' => 'Something translated'
            ),
            $placeHolders
        );
    }
}
