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

use ilLanguage;
use ilTemplate;
use PHPUnit\Framework\TestCase;
use ilDefaultPlaceholderDescription;
use ilUserDefinedFieldsPlaceholderDescription;
use ilObjectCustomUserFieldsPlaceholderDescription;
use ILIAS\User\Profile\Profile;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderDescriptionTest extends TestCase
{
    public function testPlaceholderGetHtmlDescription(): void
    {
        $languageMock = $this->createStub(ilLanguage::class);

        $templateMock = $this->createStub(ilTemplate::class);

        $templateMock->method('get')
            ->willReturn('');

        $userDefinePlaceholderMock = $this->createStub(ilUserDefinedFieldsPlaceholderDescription::class);

        $userDefinePlaceholderMock->method('createPlaceholderHtmlDescription')
            ->willReturn('');

        $userDefinePlaceholderMock->method('getPlaceholderDescriptions')
            ->willReturn([]);

        $customUserPlaceholderObject = $this->createStub(ilObjectCustomUserFieldsPlaceholderDescription::class);

        $customUserPlaceholderObject->method('getPlaceholderDescriptions')
            ->willReturn([
                '+SOMETHING' => 'SOMEWHAT',
                '+SOMETHING_ELSE' => 'ANYTHING'
            ]);

        $customUserPlaceholderObject->method('createPlaceholderHtmlDescription')
            ->willReturn('');

        $profile = $this->createStub(Profile::class);

        $placeholderDescriptionObject = new CoursePlaceholderDescription(200, null, $languageMock, $userDefinePlaceholderMock, $customUserPlaceholderObject, $profile);

        $html = $placeholderDescriptionObject->createPlaceholderHtmlDescription($templateMock);

        $this->assertSame('', $html);
    }

    public function testPlaceholderDescriptions(): void
    {
        $languageMock = $this->createMock(ilLanguage::class);

        $languageMock->expects($this->exactly(3))
            ->method('txt')
            ->willReturn('Something translated');

        $defaultPlaceholder = $this->createStub(ilDefaultPlaceholderDescription::class);

        $defaultPlaceholder->method('getPlaceholderDescriptions')
            ->willReturn(
                [
                    'SOMETHING' => 'SOMEWHAT',
                    'SOMETHING_ELSE' => 'ANYTHING'
                ]
            );

        $customUserPlaceholderObject = $this->createStub(ilObjectCustomUserFieldsPlaceholderDescription::class);

        $customUserPlaceholderObject->method('getPlaceholderDescriptions')
            ->willReturn(
                [
                    '+SOMETHING' => 'SOMEWHAT',
                    '+SOMETHING_ELSE' => 'ANYTHING'
                ]
            );

        $profile = $this->createStub(Profile::class);

        $placeholderDescriptionObject = new CoursePlaceholderDescription(200, $defaultPlaceholder, $languageMock, null, $customUserPlaceholderObject, $profile);

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
