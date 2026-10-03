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
use ILIAS\Language\Activities\InvalidInputException;
use ILIAS\Language\Activities\UpdateLanguage;
use ILIAS\UI\Component\Input\Field\Text;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use ilSetupLanguage;

class UpdateLanguageTest extends ActivityWithPerformResultContractTestCase
{
    protected function createDefaultActivity(): UpdateLanguage
    {
        $setup_language = $this->createSetupLanguageMock([], [], ['de']);
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);

        return $this->createActivity($setup_language);
    }

    protected function validPerformParameters(): array
    {
        return ['language_keys' => 'de'];
    }

    public function testSingleAlreadyInstalledLanguageIsRefreshed(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], ['de']);
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('de')
            ->willReturn(true);
        $setup_language->expects($this->once())->method('flushLanguageForInstallation')->with('de');
        $setup_language->expects($this->once())->method('insertLanguageForInstallation')->with('de');
        $setup_language->expects($this->once())->method('registerInstalledLanguage')->with(
            'de',
            [],
            []
        );

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ' de ',
        ]);

        $this->assertSame(['de'], $result['updated_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
    }

    /**
     * A language that is not installed at all is a complete no-op - not even
     * checkLanguageForInstallation may run for it, since there is nothing
     * installed yet to refresh. Use InstallLanguage to install it first.
     */
    public function testSingleNotInstalledLanguageIsCompleteNoOp(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], []);
        $setup_language->expects($this->never())->method('checkLanguageForInstallation');
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->never())->method('registerInstalledLanguage');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
        ]);

        $this->assertSame([], $result['updated_language_keys']);
        $this->assertSame(['de'], $result['not_installed_language_keys']);
    }

    /**
     * A single request can carry both already-installed and not-installed
     * languages at once; each must land in its own bucket, and only the
     * installed one may trigger any write method.
     */
    public function testMixedListPartitionsEachKeyIntoTheCorrectBucket(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], ['de']);
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('de')
            ->willReturn(true);
        $setup_language->expects($this->once())->method('flushLanguageForInstallation')->with('de');
        $setup_language->expects($this->once())->method('insertLanguageForInstallation')->with('de');
        $setup_language->expects($this->once())->method('registerInstalledLanguage')->with('de', [], []);

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ['de', 'fr'],
        ]);

        $this->assertSame(['de'], $result['updated_language_keys']);
        $this->assertSame(['fr'], $result['not_installed_language_keys']);
    }

    /**
     * getAvailableLanguagesForInstallation() (the $known_languages/$db_languages
     * argument) and getLocalLanguages() ($local_language_keys) must be passed
     * to registerInstalledLanguage() in exactly this order - both empty (or
     * equal) values would make a swap of the two arguments invisible, so
     * this test deliberately uses distinguishable shapes for each.
     */
    public function testRegisterInstalledLanguageReceivesKnownLanguagesAndLocalLanguageKeysInTheCorrectOrder(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            ['en' => ['obj_id' => 1, 'status' => 'installed']], // $db_languages
            ['fr'], // $local_language_keys
            ['de'] // installed
        );
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);
        $setup_language->expects($this->once())
            ->method('registerInstalledLanguage')
            ->with(
                'de',
                ['en' => ['obj_id' => 1, 'status' => 'installed']],
                ['fr']
            );

        $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
        ]);
    }

    public function testInvalidLanguageFileOfAnAlreadyInstalledLanguagePreventsRefreshAndThrows(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], ['de']);
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('de')
            ->willReturn(false);
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->never())->method('registerInstalledLanguage');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid language files: de');

        $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
        ]);
    }

    /**
     * All invalid keys among the ones actually being refreshed must be
     * collected into a single error, not just the first one found - and the
     * not-installed key must never even be validated, since it is a no-op.
     */
    public function testMultipleInvalidLanguageFilesAreAllReportedTogether(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], ['de', 'xx']);
        $setup_language->expects($this->exactly(2))
            ->method('checkLanguageForInstallation')
            ->willReturnMap([
                ['de', false],
                ['xx', false],
            ]);
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->never())->method('registerInstalledLanguage');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid language files: de, xx');

        $this->createActivity($setup_language)->perform([
            'language_keys' => ['de', 'xx', 'fr'], // 'fr' is not installed - untouched
        ]);
    }

    /**
     * When every requested language turns out to be not installed (a
     * complete no-op), perform() must not even read the available/local
     * languages - there is nothing left to do that would need them.
     */
    public function testAllRequestedLanguagesNotInstalledSkipsLanguageLookupsEntirely(): void
    {
        $setup_language = $this->createMock(ilSetupLanguage::class);
        $setup_language->method('getInstalledLanguages')->willReturn([]);
        $setup_language->expects($this->never())->method('getAvailableLanguagesForInstallation');
        $setup_language->expects($this->never())->method('getLocalLanguages');
        $setup_language->expects($this->never())->method('checkLanguageForInstallation');
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->never())->method('registerInstalledLanguage');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ['de', 'en'],
        ]);

        $this->assertSame([], $result['updated_language_keys']);
        $this->assertSame(['de', 'en'], $result['not_installed_language_keys']);
    }

    /**
     * UpdateLanguage never deals with customizing/local files as a distinct
     * concept the way InstallLanguage does (mode "install_local") - it must
     * never even look at whether a local file is invalid.
     */
    public function testNeverInspectsInvalidLocalLanguageFiles(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], ['de']);
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);
        $setup_language->expects($this->never())->method('getInvalidLocalLanguageFiles');

        $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
        ]);
    }

    public function testDuplicateLanguageKeysAcrossStringAndArrayAreDeduplicated(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], ['de']);
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('de')
            ->willReturn(true);
        $setup_language->expects($this->once())->method('flushLanguageForInstallation')->with('de');
        $setup_language->expects($this->once())->method('insertLanguageForInstallation')->with('de');
        $setup_language->expects($this->once())->method('registerInstalledLanguage');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => [' de, de ', 'de'],
        ]);

        $this->assertSame(['de'], $result['updated_language_keys']);
    }

    public static function invalidLanguageKeysProvider(): array
    {
        return [
            'missing language_keys key' => [[]],
            'empty (only whitespace/commas)' => [['language_keys' => ' , ']],
            'nested array value' => [['language_keys' => ['de', ['fr']]]],
            // Regression coverage for ParsesLanguageKeyList::toLanguageKeyList()'s
            // format validation (exactly two lowercase ASCII letters).
            'three letters' => [['language_keys' => 'deu']],
            'one letter' => [['language_keys' => 'd']],
            'uppercase' => [['language_keys' => 'DE']],
            'contains a digit' => [['language_keys' => 'de1']],
            'contains a hyphen' => [['language_keys' => 'de-at']],
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
     * (see UpdateLanguage::perform()): a non-array $parameters must now
     * raise the concrete InvalidInputException - not just the more general
     * \InvalidArgumentException it extends.
     */
    public function testNonArrayParametersAreRejected(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity($this->createSetupLanguageMock([], [], []))->perform('not-an-array');
    }

    public function testInputDescriptionUsesOnlyTheLanguageKeysFieldWithNoModeField(): void
    {
        $text = $this->createMock(Text::class);
        $text->expects($this->once())->method('withRequired')->with(true)->willReturnSelf();
        $text->expects($this->once())
            ->method('withDedicatedName')
            ->with('language_keys')
            ->willReturnSelf();

        $field = $this->createMock(\ILIAS\UI\Component\Input\Field\Factory::class);
        $field->expects($this->once())->method('text')->with(
            'Language keys',
            'Comma-separated list of language keys, e.g. de, fr, it.'
        )->willReturn($text);
        // UpdateLanguage has no mode parameter - the select field must never
        // be built, unlike InstallLanguage's input description.
        $field->expects($this->never())->method('select');

        $group = $this->createMock(Group::class);
        $field->expects($this->once())
            ->method('group')
            ->with(['language_keys' => $text])
            ->willReturn($group);

        $activity = $this->createActivity(
            $this->createSetupLanguageMock([], [], [])
        );

        $this->assertSame($group, $activity->getInputDescription($field));
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

        $setup_language = $this->createSetupLanguageMock([], [], ['de']);
        $setup_language->expects($this->never())->method('getInstalledLanguages');
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $language = $this->createMock(Language::class);
        $language->method('txt')->with('msg_no_perm_write')->willReturn('no write permission');

        $result = $this->createActivity(
            $setup_language,
            $rbac,
            $language
        )->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => 'de']);

        $this->assertTrue($result->isError());
    }

    public function testPermissionGrantedPerformsAndReturnsOkResult(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $setup_language = $this->createSetupLanguageMock([], [], ['de']);
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);

        $result = $this->createActivity(
            $setup_language,
            $rbac
        )->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => 'de']);

        $this->assertFalse($result->isError());
        $this->assertSame(['de'], $result->value()['updated_language_keys']);
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
    public function testMaybePerformAsAcceptsAnArrayOfLanguageKeysAndUpdatesEachOne(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $setup_language = $this->createSetupLanguageMock([], [], ['de', 'fr']);
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);

        $result = $this->createActivity($setup_language, $rbac)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => ['de', 'fr']]);

        $this->assertFalse($result->isError());
        $this->assertSame(['de', 'fr'], $result->value()['updated_language_keys']);
    }

    private function createActivity(
        ilSetupLanguage $setup_language,
        ?\ilRbacSystem $rbac = null,
        ?Language $language = null
    ): UpdateLanguage {
        return new UpdateLanguage(
            $this->createMock(RefineryFactory::class),
            $language ?? $this->createMock(Language::class),
            $rbac ?? $this->createMock(\ilRbacSystem::class),
            $setup_language
        );
    }

    private function createSetupLanguageMock(
        array $available_languages,
        array $local_language_keys,
        array $installed_language_keys
    ): MockObject&ilSetupLanguage {
        $setup_language = $this->createMock(ilSetupLanguage::class);
        $setup_language->method('getAvailableLanguagesForInstallation')->willReturn($available_languages);
        $setup_language->method('getLocalLanguages')->willReturn($local_language_keys);
        $setup_language->method('getInstalledLanguages')->willReturn($installed_language_keys);

        return $setup_language;
    }
}
