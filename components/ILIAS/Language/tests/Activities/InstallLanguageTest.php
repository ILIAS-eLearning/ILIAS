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
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Tests\Activities\ActivityWithPerformResultContractTestCase;
use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\InvalidInputException;
use ILIAS\UI\Component\Input\Field\Text;
use ILIAS\UI\Component\Input\Field\Select;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use ilSetupLanguage;

class InstallLanguageTest extends ActivityWithPerformResultContractTestCase
{
    protected function createDefaultActivity(): InstallLanguage
    {
        $setup_language = $this->createSetupLanguageMock(['de'], [], []);
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);

        return $this->createActivity($setup_language);
    }

    protected function validPerformParameters(): array
    {
        return ['language_keys' => 'de', 'mode' => InstallLanguage::MODE_INSTALL];
    }

    public function testSingleNewLanguageIsInstalled(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], []);
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('de')
            ->willReturn(true);
        $setup_language->expects($this->once())->method('flushLanguageForInstallation');
        $setup_language->expects($this->once())->method('insertLanguageForInstallation');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ' de ',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame(['de'], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame([], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame([], $result['invalid_local_language_files']);
    }

    public function testMultipleLanguagesSeparatesNotInstalledFromAlreadyInstalledUnderInstallMode(): void
    {
        // Mode "install": 'de' is already installed and must be left
        // completely alone (no validation, no write at all) - only the
        // not-yet-installed 'fr' is actually installed.
        $setup_language = $this->createSetupLanguageMock(
            [
                'de' => ['obj_id' => 1, 'status' => 'installed'],
            ],
            [],
            ['de']
        );
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('fr')
            ->willReturn(true);
        $setup_language->expects($this->once())->method('flushLanguageForInstallation')->with('fr');
        $setup_language->expects($this->once())->method('insertLanguageForInstallation')->with('fr');

        $result = $this->createActivity($setup_language)->perform([
            // Duplicate 'de' across the comma-separated string and the array
            // must still be deduplicated exactly like before.
            'language_keys' => [' de, fr ', 'de'],
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame(['fr'], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame(['de'], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame([], $result['invalid_local_language_files']);
    }

    public function testNewLanguageWithCustomFileIsReportedAsInstalledWithLocalFile(): void
    {
        // classified by "has a customizing/local file", not "was newly installed"
        $setup_language = $this->createSetupLanguageMock(
            [],
            ['de'],
            []
        );
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('de')
            ->willReturn(true);
        $setup_language->expects($this->once())->method('flushLanguageForInstallation')->with('de');
        $setup_language->expects($this->once())->method('insertLanguageForInstallation')->with('de');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame(['de'], $result['installed_with_local_language_keys']);
        $this->assertSame([], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
    }

    /** Mode "install" on an installed language is a complete no-op: re-applying the local file is "install_local"'s job. */
    public function testInstallModeIsNoOpForAlreadyInstalledLanguageEvenWithACustomFile(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            ['de' => ['obj_id' => 1, 'status' => 'installed']],
            ['de'],
            ['de']
        );
        $setup_language->expects($this->never())->method('checkLanguageForInstallation');
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForApplyingLocalChanges');
        $setup_language->expects($this->never())->method('registerInstalledLanguage');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame(['de'], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame([], $result['invalid_local_language_files']);
    }

    public function testInstallLocalModeAppliesCustomFileToAlreadyInstalledLanguage(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            ['de' => ['obj_id' => 1, 'status' => 'installed']],
            ['de'],
            ['de']
        );
        $setup_language->expects($this->never())->method('checkLanguageForInstallation');
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->once())
            ->method('insertLanguageForApplyingLocalChanges')
            ->with('de');
        // object_data bookkeeping is delegated to ilSetupLanguage::registerInstalledLanguage() (see ilSetupLanguageTest)
        $setup_language->expects($this->once())
            ->method('registerInstalledLanguage')
            ->with(
                'de',
                ['de' => ['obj_id' => 1, 'status' => 'installed']],
                ['de']
            );

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL_LOCAL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame(['de'], $result['installed_with_local_language_keys']);
        $this->assertSame([], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame([], $result['invalid_local_language_files']);
    }

    /** "install_local" without a local file is reported as "already installed": nothing changed. */
    public function testInstallLocalModeOnAlreadyInstalledLanguageWithoutCustomFileIsReportedAsAlreadyInstalled(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            ['de' => ['obj_id' => 1, 'status' => 'installed']],
            [], // no customizing/local file exists for 'de'
            ['de']
        );
        $setup_language->expects($this->never())->method('checkLanguageForInstallation');
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->once())
            ->method('insertLanguageForApplyingLocalChanges')
            ->with('de');
        $setup_language->expects($this->once())->method('registerInstalledLanguage');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL_LOCAL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame(['de'], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
    }

    /** "install_local" on a language that is not installed is a no-op. */
    public function testInstallLocalModeOnNotInstalledLanguageIsCompleteNoOp(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], []);
        $setup_language->expects($this->never())->method('checkLanguageForInstallation');
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForApplyingLocalChanges');
        $setup_language->expects($this->never())->method('registerInstalledLanguage');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL_LOCAL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame([], $result['already_installed_language_keys']);
        $this->assertSame(['de'], $result['not_installed_language_keys']);
    }

    /** If every language is a no-op, perform() must not read the available/local languages or scan local files. */
    public function testAllRequestedLanguagesBeingNoOpsSkipsLanguageAndFileLookupsEntirely(): void
    {
        $setup_language = $this->createMock(ilSetupLanguage::class);
        $setup_language->method('getInstalledLanguages')->willReturn(['de', 'en']);
        $setup_language->expects($this->never())->method('getAvailableLanguagesForInstallation');
        $setup_language->expects($this->never())->method('getLocalLanguages');
        $setup_language->expects($this->never())->method('getInvalidLocalLanguageFiles');
        $setup_language->expects($this->never())->method('checkLanguageForInstallation');
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForApplyingLocalChanges');
        $setup_language->expects($this->never())->method('registerInstalledLanguage');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ['de', 'en'],
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame(['de', 'en'], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame([], $result['invalid_local_language_files']);
    }

    /** Every state in one request lands in its own bucket; only affected ones trigger a write (mode "install"). */
    public function testBulkRequestUnderInstallModePartitionsEachKeyIntoTheCorrectBucket(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            ['de' => ['obj_id' => 1, 'status' => 'installed']],
            ['fr'], // only 'fr' has a pending customizing/local file
            ['de']
        );
        $setup_language->expects($this->exactly(2))
            ->method('checkLanguageForInstallation')
            ->willReturnMap([
                ['en', true],
                ['fr', true],
            ]);
        $setup_language->expects($this->exactly(2))->method('flushLanguageForInstallation');
        $setup_language->expects($this->exactly(2))->method('insertLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForApplyingLocalChanges');
        $setup_language->expects($this->exactly(2))->method('registerInstalledLanguage');
        $setup_language->expects($this->once())
            ->method('getInvalidLocalLanguageFiles')
            // the already-installed 'de' is untouched
            ->with(['en', 'fr']);

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ['de', 'en', 'fr'],
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame(['en'], $result['installed_language_keys']);
        $this->assertSame(['fr'], $result['installed_with_local_language_keys']);
        $this->assertSame(['de'], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
    }

    /** Mode "install_local": only insertLanguageForApplyingLocalChanges, only for already-installed keys. */
    public function testBulkRequestUnderInstallLocalModePartitionsEachKeyIntoTheCorrectBucket(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            [
                'de' => ['obj_id' => 1, 'status' => 'installed'],
                'en' => ['obj_id' => 2, 'status' => 'installed'],
            ],
            ['de'], // only 'de' has a pending customizing/local file
            ['de', 'en']
        );
        $setup_language->expects($this->never())->method('checkLanguageForInstallation');
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->exactly(2))
            ->method('insertLanguageForApplyingLocalChanges')
            ->with($this->logicalOr('de', 'en'));
        $setup_language->expects($this->exactly(2))->method('registerInstalledLanguage');
        $setup_language->expects($this->once())
            ->method('getInvalidLocalLanguageFiles')
            // the not-installed 'fr' is left untouched
            ->with(['de', 'en']);

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ['de', 'en', 'fr'],
            'mode' => InstallLanguage::MODE_INSTALL_LOCAL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame(['de'], $result['installed_with_local_language_keys']);
        $this->assertSame(['en'], $result['already_installed_language_keys']);
        $this->assertSame(['fr'], $result['not_installed_language_keys']);
    }

    public function testInstallingNewLanguageDoesNotUpdateAlreadyInstalledLanguages(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            [
                'en' => ['obj_id' => 1, 'status' => 'installed'],
            ],
            [],
            ['en']
        );
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('de')
            ->willReturn(true);
        $setup_language->expects($this->once())->method('flushLanguageForInstallation');
        $setup_language->expects($this->once())->method('insertLanguageForInstallation');
        $setup_language->expects($this->once())
            ->method('registerInstalledLanguage')
            ->with(
                'de',
                ['en' => ['obj_id' => 1, 'status' => 'installed']],
                []
            );

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame(['de'], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame([], $result['already_installed_language_keys']);
        $this->assertSame([], $result['invalid_local_language_files']);
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

        $this->createActivity($this->createSetupLanguageMock([], [], []))->perform([
            'language_keys' => 'de',
        ]);
    }

    public function testInvalidModeValueIsRejected(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity($this->createSetupLanguageMock([], [], []))->perform([
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
            $this->createSetupLanguageMock([], [], [])
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

        $this->createActivity($this->createSetupLanguageMock([], [], []))->perform($parameters);
    }

    /** Pins InvalidInputException, not just its parent \InvalidArgumentException. */
    public function testNonArrayParametersAreRejected(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity($this->createSetupLanguageMock([], [], []))->perform('not-an-array');
    }

    /** ilObjLanguageFolderGUI sends 'language_keys' as array of strings; the real grind() pipeline must join it. */
    public function testMaybePerformAsAcceptsAnArrayOfLanguageKeysAndInstallsEachOne(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
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

    private function createActivity(
        ilSetupLanguage $setup_language,
        ?\ilRbacSystem $rbac = null,
    ): InstallLanguage {
        return new InstallLanguage(
            $this->rbacOf($rbac ?? $this->createMock(\ilRbacSystem::class)),
            $setup_language,
            $this->languageFolder()
        );
    }

    private function createSetupLanguageMock(
        array $available_languages,
        array $local_language_keys,
        array $installed_language_keys,
        array $invalid_local_language_files = []
    ): MockObject&ilSetupLanguage {
        $setup_language = $this->createMock(ilSetupLanguage::class);
        $setup_language->method('getAvailableLanguagesForInstallation')->willReturn($available_languages);
        $setup_language->method('getLocalLanguages')->willReturn($local_language_keys);
        $setup_language->method('getInstalledLanguages')->willReturn($installed_language_keys);
        $setup_language->method('getInvalidLocalLanguageFiles')->willReturn($invalid_local_language_files);

        return $setup_language;
    }
}
