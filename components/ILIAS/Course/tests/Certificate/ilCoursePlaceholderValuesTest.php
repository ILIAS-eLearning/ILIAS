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

namespace ILIAS\Course\Certificate;

use ilObjUser;
use ilLanguage;
use ilObjCourse;
use ilDBInterface;
use ILIAS\DI\Container;
use ilObjectTranslation;
use ilCertificateDateHelper;
use ilCertificateUtilHelper;
use ilCertificateObjectHelper;
use ilDefaultPlaceholderValues;
use PHPUnit\Framework\TestCase;
use ilObjectTranslationLanguage;
use ilObjectCustomUserFieldsPlaceholderValues;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderValuesTest extends TestCase
{
    protected ?Container $dic;

    protected function setUp(): void
    {
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        global $DIC;
        $this->dic = is_object($DIC) ? clone $DIC : $DIC;
        $DIC = new Container();
        parent::setUp();
    }

    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($name) {
            return $GLOBALS[$name];
        };
    }

    public function testGetPlaceholderValues(): void
    {
        $customUserFieldsPlaceholderValues = $this->getMockBuilder(ilObjectCustomUserFieldsPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customUserFieldsPlaceholderValues->method('getPlaceholderValues')
            ->willReturn([]);

        $defaultPlaceholderValues = $this->getMockBuilder(ilDefaultPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultPlaceholderValues->method('getPlaceholderValues')
            ->willReturn([]);

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturn('Something');

        $objectMock = $this->getMockBuilder(ilObjCourse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('Some Title');

        $obj_translation = $this->getMockBuilder(ilObjectTranslation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $german = $this->createMock(ilObjectTranslationLanguage::class);
        $german->method('getLanguageCode')
            ->willReturn('de');

        $english = $this->createMock(ilObjectTranslationLanguage::class);
        $english->method('getLanguageCode')
            ->willReturn('en');

        $obj_translation->method('getLanguages')
            ->willReturn([
                $german,
                $english
            ]);

        $objectMock->method('getObjectTranslation')
            ->willReturn($obj_translation);

        $user_object = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();
        $objectHelper->method('getInstanceByObjId')
            ->willReturnMap(
                [
                    [200, $objectMock],
                    [100, $user_object]
                ]
            );

        $participantsHelper = $this->getMockBuilder(CertificateParticipantsHelper::class)
            ->getMock();

        $participantsHelper->method('getDateTimeOfPassed')
            ->willReturn('2018-09-10');

        $ilUtilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ilUtilHelper->method('prepareFormOutput')
            ->willReturn('Some Title');

        $ilDateHelper = $this->getMockBuilder(ilCertificateDateHelper::class)
            ->getMock();

        $ilDateHelper->method('formatDate')
            ->willReturn('2018-09-10');

        $ilDateHelper->method('formatDateTime')
            ->willReturn('2018-09-10 10:32:00');

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $this->setGlobalVariable('ilDB', $database);
        $this->setGlobalVariable('lng', $language);

        $valuesObject = new CoursePlaceholderValues(
            $customUserFieldsPlaceholderValues,
            $defaultPlaceholderValues,
            $language,
            $objectHelper,
            $participantsHelper,
            $ilDateHelper,
        );

        $placeholderValues = $valuesObject->getPlaceholderValues(100, 200);

        $this->assertEquals(
            [
                'COURSE_TITLE' => 'Some Title',
                'DATE_COMPLETED' => '2018-09-10',
                'DATETIME_COMPLETED' => '2018-09-10 10:32:00'
            ],
            $placeholderValues
        );
    }

    public function testGetPreviewPlaceholderValues(): void
    {
        $customUserFieldsPlaceholderValues = $this->getMockBuilder(ilObjectCustomUserFieldsPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customUserFieldsPlaceholderValues->method('getPlaceholderValuesForPreview')
            ->willReturn(
                [
                    'SOME_PLACEHOLDER' => 'ANYTHING',
                    'SOME_OTHER_PLACEHOLDER' => '2018-09-10',
                ]
            );

        $defaultPlaceholderValues = $this->getMockBuilder(ilDefaultPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultPlaceholderValues->method('getPlaceholderValuesForPreview')
            ->willReturn(
                [
                    'SOME_PLACEHOLDER' => 'ANYTHING',
                    'SOME_OTHER_PLACEHOLDER' => '2018-09-10',
                ]
            );

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturn('Something');

        $objectMock = $this->getMockBuilder(ilObjCourse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('SomeTitle');

        $obj_translation = $this->getMockBuilder(ilObjectTranslation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $german = $this->createMock(ilObjectTranslationLanguage::class);
        $german->method('getLanguageCode')
            ->willReturn('de');

        $english = $this->createMock(ilObjectTranslationLanguage::class);
        $english->method('getLanguageCode')
            ->willReturn('en');

        $obj_translation->method('getLanguages')
            ->willReturn([
                $german,
                $english
            ]);

        $objectMock->method('getObjectTranslation')
            ->willReturn($obj_translation);

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $participantsHelper = $this->getMockBuilder(CertificateParticipantsHelper::class)
            ->getMock();

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $utilHelper->method('prepareFormOutput')
            ->willReturnCallback(function ($input) {
                return $input;
            });

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();

        $this->setGlobalVariable('ilDB', $database);
        $this->setGlobalVariable('lng', $language);

        $valuesObject = new CoursePlaceholderValues(
            $customUserFieldsPlaceholderValues,
            $defaultPlaceholderValues,
            $language,
            $objectHelper,
            $participantsHelper,
        );

        $placeholderValues = $valuesObject->getPlaceholderValuesForPreview(100, 10);

        $this->assertSame(
            [
                'SOME_PLACEHOLDER' => 'ANYTHING',
                'SOME_OTHER_PLACEHOLDER' => '2018-09-10',
                'COURSE_TITLE' => 'SomeTitle'
            ],
            $placeholderValues
        );
    }
}
