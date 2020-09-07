<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilTestPlaceholderDescriptionTest extends \PHPUnit_Framework_TestCase
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

        $placeholderDescriptionObject = new ilTestPlaceholderDescription(null, $languageMock, $userDefinePlaceholderMock);

        $html = $placeholderDescriptionObject->createPlaceholderHtmlDescription($templateMock);

        $this->assertEquals('', $html);
    }

    public function testPlaceholderDescriptions()
    {
        $languageMock = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->setMethods(array('txt'))
            ->getMock();

        $languageMock->expects($this->exactly(25))
            ->method('txt')
            ->willReturn('Something translated');

        $userDefinePlaceholderMock = $this->getMockBuilder('ilUserDefinedFieldsPlaceholderDescription')
            ->disableOriginalConstructor()
            ->getMock();

        $userDefinePlaceholderMock->method('createPlaceholderHtmlDescription')
            ->willReturn(array());

        $userDefinePlaceholderMock->method('getPlaceholderDescriptions')
            ->willReturn(array());

        $placeholderDescriptionObject = new ilTestPlaceholderDescription(null, $languageMock, $userDefinePlaceholderMock);

        $placeHolders = $placeholderDescriptionObject->getPlaceholderDescriptions();

        $this->assertEquals(
            array(
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
            ),
            $placeHolders
        );
    }
}
