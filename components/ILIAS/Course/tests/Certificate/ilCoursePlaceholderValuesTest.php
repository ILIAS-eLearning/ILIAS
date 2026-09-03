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
use ilCertificateDateHelper;
use ilCertificateUtilHelper;
use ilCertificateObjectHelper;
use ilDefaultPlaceholderValues;
use PHPUnit\Framework\TestCase;
use ILIAS\ILIASObject\Properties\Translations\Language as ilObjectTranslationLanguage;
use ILIAS\ILIASObject\Properties\Translations\Translations as ilObjectTranslations;
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
        $customUserFieldsPlaceholderValues = $this->createStub(ilObjectCustomUserFieldsPlaceholderValues::class);

        $customUserFieldsPlaceholderValues->method('getPlaceholderValues')
            ->willReturn([]);

        $defaultPlaceholderValues = $this->createStub(ilDefaultPlaceholderValues::class);

        $defaultPlaceholderValues->method('getPlaceholderValues')
            ->willReturn([]);

        $language = $this->createStub(ilLanguage::class);

        $language->method('txt')
            ->willReturn('Something');

        $objectMock = $this->createStub(ilObjCourse::class);

        $objectMock->method('getTitle')
            ->willReturn('Some Title');

        $obj_translation = $this->createStub(ilObjectTranslations::class);

        $german = $this->createStub(ilObjectTranslationLanguage::class);
        $german->method('getLanguageCode')
            ->willReturn('de');

        $english = $this->createStub(ilObjectTranslationLanguage::class);
        $english->method('getLanguageCode')
            ->willReturn('en');

        $obj_translation->method('getLanguages')
            ->willReturn([
                $german,
                $english
            ]);

        $objectMock->method('getObjectTranslation')
            ->willReturn($obj_translation);

        $user_object = $this->createStub(ilObjUser::class);

        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);
        $objectHelper->method('getInstanceByObjId')
            ->willReturnMap(
                [
                    [200, $objectMock],
                    [100, $user_object]
                ]
            );

        $participantsHelper = $this->createStub(CertificateParticipantsHelper::class);

        $participantsHelper->method('getDateTimeOfPassed')
            ->willReturn('2018-09-10');

        $ilUtilHelper = $this->createStub(ilCertificateUtilHelper::class);

        $ilUtilHelper->method('prepareFormOutput')
            ->willReturn('Some Title');

        $ilDateHelper = $this->createStub(ilCertificateDateHelper::class);

        $ilDateHelper->method('formatDate')
            ->willReturn('2018-09-10');

        $ilDateHelper->method('formatDateTime')
            ->willReturn('2018-09-10 10:32:00');

        $database = $this->createStub(ilDBInterface::class);

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
        $customUserFieldsPlaceholderValues = $this->createStub(ilObjectCustomUserFieldsPlaceholderValues::class);

        $customUserFieldsPlaceholderValues->method('getPlaceholderValuesForPreview')
            ->willReturn(
                [
                    'SOME_PLACEHOLDER' => 'ANYTHING',
                    'SOME_OTHER_PLACEHOLDER' => '2018-09-10',
                ]
            );

        $defaultPlaceholderValues = $this->createStub(ilDefaultPlaceholderValues::class);

        $defaultPlaceholderValues->method('getPlaceholderValuesForPreview')
            ->willReturn(
                [
                    'SOME_PLACEHOLDER' => 'ANYTHING',
                    'SOME_OTHER_PLACEHOLDER' => '2018-09-10',
                ]
            );

        $language = $this->createStub(ilLanguage::class);

        $language->method('txt')
            ->willReturn('Something');

        $objectMock = $this->createStub(ilObjCourse::class);

        $objectMock->method('getTitle')
            ->willReturn('SomeTitle');

        $obj_translation = $this->createStub(ilObjectTranslations::class);

        $german = $this->createStub(ilObjectTranslationLanguage::class);
        $german->method('getLanguageCode')
            ->willReturn('de');

        $english = $this->createStub(ilObjectTranslationLanguage::class);
        $english->method('getLanguageCode')
            ->willReturn('en');

        $obj_translation->method('getLanguages')
            ->willReturn([
                $german,
                $english
            ]);

        $objectMock->method('getObjectTranslation')
            ->willReturn($obj_translation);

        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $participantsHelper = $this->createStub(CertificateParticipantsHelper::class);

        $utilHelper = $this->createStub(ilCertificateUtilHelper::class);

        $utilHelper->method('prepareFormOutput')
            ->willReturnCallback(function ($input) {
                return $input;
            });

        $database = $this->createStub(ilDBInterface::class);

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
