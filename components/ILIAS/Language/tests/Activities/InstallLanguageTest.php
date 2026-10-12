<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\InvalidInputException;
use ILIAS\UI\Component\Input\Field\Text;
use ILIAS\UI\Component\Input\Field\Select;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use ilSetupLanguage;

class InstallLanguageTest extends ActivityWithPerformResultContractTestCase
{
    use InstallLanguageTestFixture;

    protected function createDefaultActivity(): InstallLanguage
    {
        $setup_language = $this->createSetupLanguageStub(['de'], [], []);
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);

        return $this->createActivity($setup_language);
    }

    protected function validPerformParameters(): array
    {
        return ['language_keys' => 'de', 'mode' => InstallLanguage::MODE_INSTALL];
    }

    public function testMalformedLocalLanguageFileDoesNotPreventStandardInstallation(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], [], ['ilias_de.lang.locl']);
        $setup_language->expects($this->once())
            ->method('getInvalidLocalLanguageFiles')
            ->with(['de'])
            ->willReturn(['ilias_de.lang.locl']);
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('de')
            ->willReturn(true);
        $setup_language->expects($this->once())->method('flushLanguageForInstallation');
        $setup_language->expects($this->once())->method('insertLanguageForInstallation');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame(['de'], $result['installed_language_keys']);
        $this->assertSame(['ilias_de.lang.locl'], $result['invalid_local_language_files']);
    }

    public function testInvalidLanguageFileIsReportedAsActivityError(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], []);
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('xx')
            ->willReturn(false);
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');

        $this->expectException(\RuntimeException::class);
        $this->createActivity($setup_language)->perform([
            'language_keys' => 'xx',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);
    }

    public function testMixedValidAndInvalidLanguagesDoNotMutate(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], []);
        $setup_language->expects($this->exactly(2))
            ->method('checkLanguageForInstallation')
            ->willReturnMap([
                ['de', true],
                ['xx', false],
            ]);
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');

        $this->expectException(\RuntimeException::class);
        $this->createActivity($setup_language)->perform([
            'language_keys' => 'de,xx',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);
    }

    public function testMissingModeParameterIsRejected(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity($this->createSetupLanguageStub([], [], []))->perform([
            'language_keys' => 'de',
        ]);
    }

    public function testInvalidModeValueIsRejected(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity($this->createSetupLanguageStub([], [], []))->perform([
            'language_keys' => 'de',
            'mode' => 'foo',
        ]);
    }

    public function testInputDescriptionUsesNamedLanguageKeysAndModeFields(): void
    {
        $text = $this->createMock(Text::class);
        $text->expects($this->once())->method('withRequired')->with(true)->willReturnSelf();
        $text->expects($this->once())
            ->method('withDedicatedName')
            ->with('language_keys')
            ->willReturnSelf();

        $select = $this->createMock(Select::class);
        $select->expects($this->once())->method('withRequired')->with(true)->willReturnSelf();
        $select->expects($this->once())
            ->method('withDedicatedName')
            ->with('mode')
            ->willReturnSelf();

        $field = $this->createMock(\ILIAS\UI\Component\Input\Field\Factory::class);
        $field->expects($this->once())->method('text')->with(
            'Language keys',
            'Comma-separated list of language keys, e.g. de, fr, it.'
        )->willReturn($text);
        $field->expects($this->once())->method('select')->with(
            'Mode',
            [
                InstallLanguage::MODE_INSTALL => 'Install',
                InstallLanguage::MODE_INSTALL_LOCAL => 'Install local',
            ],
            self::callback(static fn(mixed $value): bool => is_string($value))
        )->willReturn($select);

        $group = $this->createMock(\ILIAS\UI\Component\Input\Field\Section::class);
        $field->expects($this->once())
            ->method('section')
            ->with(['language_keys' => $text, 'mode' => $select], $this->anything())
            ->willReturn($group);

        $activity = $this->createActivity(
            $this->createSetupLanguageStub([], [], [])
        );

        $this->assertSame($group, $activity->getInputDescription($field));
    }

    public static function invalidLanguageKeysProvider(): array
    {
        return [
            'empty (only whitespace/commas)' => [
                ['language_keys' => ' , ', 'mode' => InstallLanguage::MODE_INSTALL],
            ],
            'nested array value' => [
                ['language_keys' => ['de', ['fr']], 'mode' => InstallLanguage::MODE_INSTALL],
            ],
            // format validation of ParsesLanguageKeyList::toLanguageKeyList()
            'three letters' => [
                ['language_keys' => 'deu', 'mode' => InstallLanguage::MODE_INSTALL],
            ],
            'one letter' => [
                ['language_keys' => 'd', 'mode' => InstallLanguage::MODE_INSTALL],
            ],
            'uppercase' => [
                ['language_keys' => 'DE', 'mode' => InstallLanguage::MODE_INSTALL],
            ],
            'contains a digit' => [
                ['language_keys' => 'de1', 'mode' => InstallLanguage::MODE_INSTALL],
            ],
            'contains a hyphen' => [
                ['language_keys' => 'de-at', 'mode' => InstallLanguage::MODE_INSTALL],
            ],
        ];
    }

    #[DataProvider('invalidLanguageKeysProvider')]
    public function testInvalidLanguageKeysAreRejected(array $parameters): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity($this->createSetupLanguageStub([], [], []))->perform($parameters);
    }

    /** Pins InvalidInputException, not just its parent \InvalidArgumentException. */
    public function testNonArrayParametersAreRejected(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity($this->createSetupLanguageStub([], [], []))->perform('not-an-array');
    }

    /** ilObjLanguageFolderGUI sends 'language_keys' as array of strings; the real grind() pipeline must join it. */
    public function testMaybePerformAsAcceptsAnArrayOfLanguageKeysAndInstallsEachOne(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $setup_language = $this->createSetupLanguageMock([], [], []);
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);
        $setup_language->expects($this->exactly(2))->method('flushLanguageForInstallation');
        $setup_language->expects($this->exactly(2))->method('insertLanguageForInstallation');

        $result = $this->createActivity($setup_language, $rbac)->maybePerformAs(
            $this->createRealFieldsUiFactory()->input(),
            6,
            ['language_keys' => ['de', 'fr'], 'mode' => InstallLanguage::MODE_INSTALL]
        );

        $this->assertTrue($result->isOk());
        $this->assertSame(['de', 'fr'], $result->value()['installed_language_keys']);
    }

    public function testPermissionDeniedBeforePerform(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())
            ->method('checkAccessOfUser')
            ->with(
                6,
                'write',
                $this->anything()
            )
            ->willReturn(false);

        $setup_language = $this->createSetupLanguageMock([], [], []);
        $setup_language->expects($this->never())->method('getAvailableLanguagesForInstallation');
        $result = $this->createActivity(
            $setup_language,
            $rbac
        )->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => 'de', 'mode' => InstallLanguage::MODE_INSTALL]);

        $this->assertTrue($result->isError());
    }
}
