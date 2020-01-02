<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderDescriptionTest extends \PHPUnit_Framework_TestCase
{
    public function testPlaceholderGetHtmlDescription()
    {
        $languageMock = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->setMethods(array('txt'))
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
            ->willReturn(array());

        $userDefinePlaceholderMock->method('getPlaceholderDescriptions')
            ->willReturn(array());

        $placeholderDescriptionObject = new ilCoursePlaceholderDescription(null, $languageMock, $userDefinePlaceholderMock);

        $html = $placeholderDescriptionObject->createPlaceholderHtmlDescription($templateMock);

        $this->assertEquals('', $html);
    }

    public function testPlaceholderDescriptions()
    {
        $languageMock = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->setMethods(array('txt'))
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

        $placeholderDescriptionObject = new ilCoursePlaceholderDescription($defaultPlaceholder, $languageMock);

        $placeHolders = $placeholderDescriptionObject->getPlaceholderDescriptions();

        $this->assertEquals(
            array(
                'COURSE_TITLE'       => 'Something translated',
                'SOMETHING'          => 'SOMEWHAT',
                'SOMETHING_ELSE'     => 'ANYTHING',
                'DATE_COMPLETED'     => 'Something translated',
                'DATETIME_COMPLETED' => 'Something translated'
            ),
            $placeHolders
        );
    }
}
