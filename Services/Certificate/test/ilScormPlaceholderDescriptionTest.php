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
class ilScormPlaceholderDescriptionTest extends ilCertificateBaseTestCase
{
    public function testPlaceholderGetHtmlDescription() : void
    {
        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt', 'loadLanguageModule'])
            ->getMock();

        $templateMock = $this->getMockBuilder(ilTemplate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->method('get')
            ->willReturn('');

        $collectionInstance = $this->getMockBuilder(ilLPCollectionOfSCOs::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPossibleItems'])
            ->getMock();

        $learningProgressMock = $this->getMockBuilder(ilObjectLP::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCollectionInstance'])
            ->getMock();

        $collectionInstance->method('getPossibleItems')
            ->willReturn([0 => ['title' => 'Some SCORM Title']]);

        $learningProgressMock->method('getCollectionInstance')
            ->willReturn($collectionInstance);

        $userDefinePlaceholderMock = $this->getMockBuilder(ilUserDefinedFieldsPlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userDefinePlaceholderMock->method('createPlaceholderHtmlDescription')
            ->willReturn('');

        $userDefinePlaceholderMock->method('getPlaceholderDescriptions')
            ->willReturn([]);

        $placeholderDescriptionObject = new ilScormPlaceholderDescription(
            $objectMock,
            null,
            $languageMock,
            $learningProgressMock,
            $userDefinePlaceholderMock
        );

        $html = $placeholderDescriptionObject->createPlaceholderHtmlDescription($templateMock);

        $this->assertSame('', $html);
    }

    public function testPlaceholderDescriptions() : void
    {
        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt', 'loadLanguageModule'])
            ->getMock();

        $languageMock->expects($this->exactly(21))
            ->method('txt')
            ->willReturn('Something translated');

        $learningProgressMock = $this->getMockBuilder(ilObjectLP::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCollectionInstance'])
            ->getMock();

        $userDefinePlaceholderMock = $this->getMockBuilder(ilUserDefinedFieldsPlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userDefinePlaceholderMock->method('createPlaceholderHtmlDescription')
            ->willReturn('');

        $userDefinePlaceholderMock->method('getPlaceholderDescriptions')
            ->willReturn([]);

        $placeholderDescriptionObject = new ilScormPlaceholderDescription(
            $objectMock,
            null,
            $languageMock,
            $learningProgressMock,
            $userDefinePlaceholderMock
        );

        $placeHolders = $placeholderDescriptionObject->getPlaceholderDescriptions();

        $this->assertSame(
            [
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
                'SCORM_TITLE' => 'Something translated',
                'SCORM_POINTS' => 'Something translated',
                'SCORM_POINTS_MAX' => 'Something translated',
                'DATE_COMPLETED' => 'Something translated',
                'DATETIME_COMPLETED' => 'Something translated'
            ],
            $placeHolders
        );
    }
}
