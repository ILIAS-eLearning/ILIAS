<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderDescriptionTest extends ilCertificateBaseTestCase
{
    public function testPlaceholderGetHtmlDescription()
    {
        $languageMock = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->onlyMethods(['txt', 'loadLanguageModule'])
            ->getMock();

        $templateMock = $this->getMockBuilder('ilTemplate')
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->method('get')
            ->willReturn('');

        $userDefinePlaceholderMock = $this->getMockBuilder('ilUserDefinedFieldsPlaceholderDescription')
            ->disableOriginalConstructor()
            ->getMock();

        $userDefinePlaceholderMock->method('createPlaceholderHtmlDescription')
            ->willReturn('');

        $userDefinePlaceholderMock->method('getPlaceholderDescriptions')
            ->willReturn([]);

        $customUserPlaceholderObject = $this->getMockBuilder("ilObjectCustomUserFieldsPlaceholderDescription")
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

        $this->assertEquals('', $html);
    }

    public function testPlaceholderDescriptions()
    {
        $languageMock = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->onlyMethods(['txt'])
            ->getMock();

        $languageMock->expects($this->exactly(3))
                     ->method('txt')
                     ->willReturn('Something translated');

        $defaultPlaceholder = $this->getMockBuilder('ilDefaultPlaceholderDescription')
            ->disableOriginalConstructor()
            ->getMock();

        $defaultPlaceholder->method('getPlaceholderDescriptions')
            ->willReturn(
                array(
                    'SOMETHING' => 'SOMEWHAT',
                    'SOMETHING_ELSE' => 'ANYTHING'
                )
            );

        $customUserPlaceholderObject = $this->getMockBuilder('ilObjectCustomUserFieldsPlaceholderDescription')
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
