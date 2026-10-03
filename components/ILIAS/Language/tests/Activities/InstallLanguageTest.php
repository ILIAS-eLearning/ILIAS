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
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as RefineryFactory;
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
        // Even a language that was not installed before must be classified
        // by "has a customizing/local file", not "was newly installed" - a
        // fresh language that already ships a local file is still more
        // accurately described as "installed with custom file" than as a
        // plain install.
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

    /**
     * This is the central bugfix regression test: mode "install" used to
     * unconditionally (re-)install and (re-)apply the customizing/local file
     * of an already installed language on every call. It must now be a
     * complete no-op instead - not even checkLanguageForInstallation may run
     * for it, since (re-)applying a customizing file is exclusively mode
     * "install_local"'s job now.
     */
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
        // The object_data bookkeeping itself is delegated to
        // ilSetupLanguage::registerInstalledLanguage() (shared with
        // installLanguages()) - see ilSetupLanguageTest for coverage of the
        // actual INSERT/UPDATE SQL it builds.
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

    /**
     * "install_local" still runs its write methods for an already installed
     * language even when no customizing/local file actually exists for it
     * (there is simply nothing for them to write) - but the *result* must
     * report this as "already installed", not as "installed with local
     * file", since nothing observable actually changed.
     */
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

    /**
     * "install_local" on a language that is not installed at all must be a
     * complete no-op - there is nothing installed yet to apply local
     * changes on top of.
     */
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

    /**
     * When every requested language turns out to be a no-op (given the
     * chosen mode and each language's install status), perform() must not
     * even read the available/local languages or scan for invalid local
     * files - there is nothing left to do that would need them.
     */
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

    /**
     * A single request can carry languages in every possible state at once;
     * each must land in its own bucket, and only the ones actually affected
     * may trigger any write method - under mode "install".
     */
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
            // Only the two languages actually being (fully) installed are
            // relevant - the already-installed 'de' is untouched.
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

    /**
     * The same idea, but under mode "install_local": no key may ever trigger
     * checkLanguageForInstallation/flushLanguageForInstallation/
     * insertLanguageForInstallation - only insertLanguageForApplyingLocalChanges,
     * and only for the already-installed keys.
     */
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
            // Only the two already-installed languages are affected - the
            // not-installed 'fr' is left untouched.
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

        $group = $this->createMock(Group::class);
        $field->expects($this->once())
            ->method('group')
            ->with(['language_keys' => $text, 'mode' => $select])
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
            // Regression coverage for ParsesLanguageKeyList::toLanguageKeyList()'s
            // format validation (exactly two lowercase ASCII letters).
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

    /**
     * Regression test for the perform()-parameter-type-check unification
     * (see InstallLanguage::perform()): a non-array $parameters must now
     * raise the concrete InvalidInputException - not just the more general
     * \InvalidArgumentException it extends.
     */
    public function testNonArrayParametersAreRejected(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity($this->createSetupLanguageMock([], [], []))->perform('not-an-array');
    }

    /**
     * Contract test: a real GUI caller (class.ilObjLanguageFolderGUI.php)
     * always builds 'language_keys' as a PHP array of strings, never a
     * comma-separated string - and GrindsFormInput::grind() (via its
     * private joinListOfStringsRawValue() helper - see that method's own
     * docblock) must join that array into the same shape a real HTML text
     * input would carry BEFORE
     * it reaches the declared Text field, rather than rejecting it. This
     * is exercised through the REAL getInputDescription()/grind() pipeline
     * (createRealFieldsUiFactory(), not a mocked FormInput) via
     * maybePerformAs() - the previously blocking regression this test
     * guards against.
     */
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
        $language = $this->createMock(Language::class);
        $language->method('txt')->with('msg_no_perm_write')->willReturn('no write permission');

        $result = $this->createActivity(
            $setup_language,
            $rbac,
            $language
        )->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => 'de', 'mode' => InstallLanguage::MODE_INSTALL]);

        $this->assertTrue($result->isError());
    }

    /**
     * Regression test for LanguageActivity::maybePerformAs()'s generic
     * catch(\Throwable) block (InstallLanguage itself does not override
     * maybePerformAs() - this exercises the base class implementation):
     * a \Throwable that is NOT an \Exception (here a \TypeError, the other
     * half of the \Throwable hierarchy) raised from within perform() must
     * still come back as a Result\Error carrying an \Exception instance
     * (here a \RuntimeException wrapping the original \TypeError as its
     * "previous"), per the Activity::maybePerformAs() contract ("Wraps the
     * result and possible errors in the Result type"). Before the fix,
     * `new Result\Error($e)` itself raised an uncaught
     * \InvalidArgumentException for such a $e (Result\Error::__construct()
     * only accepts string|\Exception), so maybePerformAs() aborted instead
     * of returning a Result at all.
     */
    public function testAThrowableThatIsNotAnExceptionFromWithinPerformIsWrappedInARuntimeExceptionResultError(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $setup_language = $this->createSetupLanguageMock([], [], []);
        // checkLanguageForInstallation() is called from within perform()'s
        // own loop, well after isAllowedToPerform() succeeded - a realistic
        // place for an unexpected \TypeError to surface (e.g. a
        // misconfigured/incompatible collaborator), simulated here directly.
        $setup_language->method('checkLanguageForInstallation')->willReturnCallback(
            static function (): bool {
                throw new \TypeError('simulated TypeError, not an \Exception');
            }
        );

        $result = $this->createActivity($setup_language, $rbac)->maybePerformAs(
            $this->createRealFieldsUiFactory()->input(),
            6,
            ['language_keys' => ['de'], 'mode' => InstallLanguage::MODE_INSTALL]
        );

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(\RuntimeException::class, $error);
        $this->assertNotInstanceOf(\TypeError::class, $error);
        $this->assertSame('simulated TypeError, not an \Exception', $error->getMessage());
        $this->assertInstanceOf(\TypeError::class, $error->getPrevious());
    }

    private function createActivity(
        ilSetupLanguage $setup_language,
        ?\ilRbacSystem $rbac = null,
        ?Language $language = null
    ): InstallLanguage {
        return new InstallLanguage(
            $this->createMock(RefineryFactory::class),
            $language ?? $this->createMock(Language::class),
            $rbac ?? $this->createMock(\ilRbacSystem::class),
            $setup_language
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
