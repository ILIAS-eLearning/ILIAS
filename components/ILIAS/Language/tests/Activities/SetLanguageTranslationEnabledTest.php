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

use ILIAS\Language\Tests\Activities\ActivityContractTestCase;
use ILIAS\Language\Activities\InvalidInputException;
use ILIAS\Language\Activities\SetLanguageTranslationEnabled;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Text;
use ILIAS\UI\Component\Input\Field\Checkbox;
use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\Data\Description\Factory as DescriptionFactory;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unlike SetLanguageDetectionEnabled it is parameterised by a language_key, and perform() itself validates
 * $parameters and writes only if the value changed (the "lang_translate_<key>" setting lives behind RepositoryProvider).
 * A denied "write" permission must prevent both the outcome and the Settings access.
 */
class SetLanguageTranslationEnabledTest extends ActivityContractTestCase
{
    protected function createDefaultActivity(): SetLanguageTranslationEnabled
    {
        return $this->createActivity();
    }

    public function testIsAllowedToPerformDelegatesToRbacSystemWithWriteAndTheConfiguredRefId(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())
            ->method('checkAccessOfUser')
            ->with(6, 'write', 42)
            ->willReturn(true);

        $activity = $this->createActivity(rbac_system: $rbac, language_folder_ref_id: 42);

        $this->assertTrue($activity->isAllowedToPerform(6, ['language_key' => 'de', 'enabled' => true]));
    }

    public function testIsAllowedToPerformReturnsFalseWhenRbacDenies(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(false);

        $activity = $this->createActivity(rbac_system: $rbac);

        $this->assertFalse($activity->isAllowedToPerform(6, ['language_key' => 'de', 'enabled' => true]));
    }

    /** isAllowedToPerform() is a pure permission check: no Setting access. */
    public function testIsAllowedToPerformNeverTouchesSettings(): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('isPageTranslationEnabled');
        $repository->expects($this->never())->method('setPageTranslationEnabled');

        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $activity = $this->createActivity(rbac_system: $rbac, repository: $repository);

        $this->assertTrue($activity->isAllowedToPerform(6, ['language_key' => 'de', 'enabled' => true]));
    }

    /** Only a difference between the stored and the requested value is written. */
    public static function performChangedDecisionProvider(): array
    {
        return [
            'stored off, request on -> changed, write on' => [false, true, true, true],
            'stored off, request off -> unchanged, no write' => [false, false, false, null],
            'stored on, request on -> unchanged, no write' => [true, true, false, null],
            'stored on, request off -> changed, write off' => [true, false, true, false],
        ];
    }

    #[DataProvider('performChangedDecisionProvider')]
    public function testPerformWritesOnlyWhenTheRequestedValueDiffersFromTheStoredOne(
        bool $stored_value,
        bool $requested_enabled,
        bool $expected_changed,
        ?bool $expected_written_value
    ): void {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->method('isPageTranslationEnabled')
            ->with('de')
            ->willReturn($stored_value);

        if ($expected_written_value === null) {
            $repository->expects($this->never())->method('setPageTranslationEnabled');
        } else {
            $repository->expects($this->once())
                ->method('setPageTranslationEnabled')
                ->with('de', $expected_written_value);
        }

        $result = $this->createActivity(repository: $repository)
            ->perform(['language_key' => 'de', 'enabled' => $requested_enabled]);

        $this->assertSame(
            ['language_key' => 'de', 'enabled' => $requested_enabled, 'changed' => $expected_changed],
            $result
        );
    }

    /** Pins the key "lang_translate_<language_key>" and the default '0'. */
    public function testPerformReadsTheCorrectPerLanguageSettingKeyWithDefaultZero(): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->once())
            ->method('isPageTranslationEnabled')->with('fr')->willReturn(false);

        $this->createActivity(repository: $repository)->perform(['language_key' => 'fr', 'enabled' => false]);
    }

    /** perform() re-implements its own trim() of language_key. */
    public function testPerformTrimsLanguageKeyForBothTheSettingKeyAndTheReturnedValue(): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->method('isPageTranslationEnabled')->with('de')->willReturn(false);
        $repository->expects($this->once())->method('setPageTranslationEnabled')->with('de', true);

        $result = $this->createActivity(repository: $repository)
            ->perform(['language_key' => '  de  ', 'enabled' => true]);

        $this->assertSame('de', $result['language_key']);
    }

    public static function invalidPerformParametersProvider(): array
    {
        return [
            'non-array parameters' => ['not-an-array'],
            'empty array' => [[]],
            'missing language_key' => [['enabled' => true]],
            'non-string language_key' => [['language_key' => 42, 'enabled' => true]],
            'empty string language_key' => [['language_key' => '', 'enabled' => true]],
            'whitespace-only language_key' => [['language_key' => '   ', 'enabled' => true]],
            'missing enabled' => [['language_key' => 'de']],
            'non-bool enabled: string "1"' => [['language_key' => 'de', 'enabled' => '1']],
            'non-bool enabled: string "true"' => [['language_key' => 'de', 'enabled' => 'true']],
            'non-bool enabled: string ""' => [['language_key' => 'de', 'enabled' => '']],
            'non-bool enabled: int 1' => [['language_key' => 'de', 'enabled' => 1]],
            'non-bool enabled: int 0' => [['language_key' => 'de', 'enabled' => 0]],
            'non-bool enabled: float 1.0' => [['language_key' => 'de', 'enabled' => 1.0]],
            'non-bool enabled: null' => [['language_key' => 'de', 'enabled' => null]],
            'non-bool enabled: array' => [['language_key' => 'de', 'enabled' => [true]]],
        ];
    }

    #[DataProvider('invalidPerformParametersProvider')]
    public function testPerformRejectsInvalidOrIncompleteParametersWithoutTouchingSettings(mixed $parameters): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('isPageTranslationEnabled');
        $repository->expects($this->never())->method('setPageTranslationEnabled');

        $this->expectException(InvalidInputException::class);

        $this->createActivity(repository: $repository)->perform($parameters);
    }

    public function testMaybePerformAsWithGrantedPermissionPerformsAndReturnsOkResult(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->method('isPageTranslationEnabled')->with('de')->willReturn(false);
        $repository->expects($this->once())->method('setPageTranslationEnabled')->with('de', true);

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => 'de', 'enabled' => true]);

        $this->assertFalse($result->isError());
        $this->assertSame(
            ['language_key' => 'de', 'enabled' => true, 'changed' => true],
            $result->value()
        );
    }

    /** A denied "write" yields a Result\Error and leaves the Setting untouched. */
    public function testMaybePerformAsWithDeniedPermissionReturnsErrorAndNeverTouchesSettings(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())
            ->method('checkAccessOfUser')
            ->with(6, 'write', $this->anything())
            ->willReturn(false);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('isPageTranslationEnabled');
        $repository->expects($this->never())->method('setPageTranslationEnabled');

        $language = $this->createMock(Language::class);
        $language->method('txt')->with('msg_no_perm_write')->willReturn('no write permission');

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository, language: $language)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => 'de', 'enabled' => true]);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(\DomainException::class, $result->error());
        $this->assertSame(403, $result->error()->getCode());
    }

    /** A Throwable from perform() becomes a Result\Error. */
    public function testDefectFromWithinPerformIsMaskedAsA500ResultError(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->method('isPageTranslationEnabled')->willReturn(false);
        $repository->method('setPageTranslationEnabled')->willThrowException(new \RuntimeException('database write failed'));

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => 'de', 'enabled' => true]);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertSame(500, $error->getCode());
        $this->assertSame('database write failed', $error->getPrevious()?->getMessage());
    }

    /** A missing 'language_key' fails the required Text field in grind(), before rbac is touched. */
    public function testMaybePerformAsWithMissingRawParametersKeysIsAResultErrorAndNeverChecksPermission(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $result = $this->createActivity(rbac_system: $rbac)->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, []);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertNotSame('', $result->error()->getMessage());
    }

    public static function invalidRawParametersRejectedByGrindWithStringErrorProvider(): array
    {
        return [
            'missing language_key' => [['enabled' => true]],
        ];
    }

    #[DataProvider('invalidRawParametersRejectedByGrindWithStringErrorProvider')]
    public function testMaybePerformAsRejectsRawParametersAtGrindLevelWithStringErrorWithoutTouchingSettingsOrRbac(
        array $raw_parameters
    ): void {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('isPageTranslationEnabled');
        $repository->expects($this->never())->method('setPageTranslationEnabled');

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, $raw_parameters);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertNotSame('', $result->error()->getMessage());
    }

    public static function invalidRawParametersProvider(): array
    {
        return [
            // a non-string 'language_key' fails the Text field's isClientSideValueOk() inside grind()
            'non-string language_key' => [['language_key' => 42, 'enabled' => true]],
            // outside the whitelist of GrindsFormInput::normalizeCheckboxRawValue()
            'non-bool enabled: array' => [['language_key' => 'de', 'enabled' => [true]]],
            'non-bool enabled: float 1.0' => [['language_key' => 'de', 'enabled' => 1.0]],
        ];
    }

    #[DataProvider('invalidRawParametersProvider')]
    public function testMaybePerformAsRejectsInvalidRawParametersAsResultErrorWithoutTouchingSettingsOrRbac(
        array $raw_parameters
    ): void {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('isPageTranslationEnabled');
        $repository->expects($this->never())->method('setPageTranslationEnabled');

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, $raw_parameters);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertSame(400, $result->error()->getCode());
    }

    /** A missing 'enabled' defaults to false like an unchecked HTML checkbox, and still reaches perform(). */
    public function testMaybePerformAsWithMissingEnabledKeyDefaultsToFalseAndStillPerformsTheWrite(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->method('isPageTranslationEnabled')->with('de')->willReturn(true);
        $repository->expects($this->once())->method('setPageTranslationEnabled')->with('de', false);

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => 'de']);

        $this->assertFalse($result->isError());
        $this->assertSame(
            ['language_key' => 'de', 'enabled' => false, 'changed' => true],
            $result->value()
        );
    }

    /** A whitespace-only language_key passes grind()'s required-check, so perform() rejects it after the permission check. */
    public function testMaybePerformAsRejectsWhitespaceOnlyLanguageKeyAfterThePermissionCheck(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('isPageTranslationEnabled');
        $repository->expects($this->never())->method('setPageTranslationEnabled');

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => '   ', 'enabled' => true]);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertSame(400, $result->error()->getCode());
    }

    public static function tolerantlyAcceptedTruthyEnabledValuesProvider(): array
    {
        return [
            'string "1"' => ['1'],
            'string "true"' => ['true'],
            'int 1' => [1],
        ];
    }

    public static function tolerantlyAcceptedFalsyEnabledValuesProvider(): array
    {
        return [
            'string "0"' => ['0'],
            'string "false"' => ['false'],
            'string ""' => [''],
            'int 0' => [0],
            'null' => [null],
        ];
    }

    /** Unlike perform(), maybePerformAs() accepts common primitive representations of "true". */
    #[DataProvider('tolerantlyAcceptedTruthyEnabledValuesProvider')]
    public function testMaybePerformAsToleratesCommonPrimitiveTruthyEnabledRepresentations(mixed $value): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->method('isPageTranslationEnabled')->with('de')->willReturn(false);
        $repository->expects($this->once())->method('setPageTranslationEnabled')->with('de', true);

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => 'de', 'enabled' => $value]);

        $this->assertFalse($result->isError());
        $this->assertSame(
            ['language_key' => 'de', 'enabled' => true, 'changed' => true],
            $result->value()
        );
    }

    /** Same for "false". */
    #[DataProvider('tolerantlyAcceptedFalsyEnabledValuesProvider')]
    public function testMaybePerformAsToleratesCommonPrimitiveFalsyEnabledRepresentations(mixed $value): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->method('isPageTranslationEnabled')->with('de')->willReturn(true);
        $repository->expects($this->once())->method('setPageTranslationEnabled')->with('de', false);

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => 'de', 'enabled' => $value]);

        $this->assertFalse($result->isError());
        $this->assertSame(
            ['language_key' => 'de', 'enabled' => false, 'changed' => true],
            $result->value()
        );
    }

    /** An unknown language_key is rejected by perform() itself, for direct callers too. */
    public function testPerformRejectsUnknownLanguageKeyWithoutTouchingSettings(): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('isPageTranslationEnabled');
        $repository->expects($this->never())->method('setPageTranslationEnabled');

        $this->expectException(InvalidInputException::class);

        $this->createActivity(repository: $repository)
            ->perform(['language_key' => 'xx', 'enabled' => true]);
    }

    /** The message embeds the key raw: escaping is RendersActivityErrors' job. */
    public function testPerformRejectsUnknownLanguageKeyMessageContainsTheRawUnescapedKey(): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('isPageTranslationEnabled');
        $repository->expects($this->never())->method('setPageTranslationEnabled');

        $dangerous_key = '<script>alert(1)</script>&"quoted"';

        try {
            $this->createActivity(repository: $repository)
                ->perform(['language_key' => $dangerous_key, 'enabled' => true]);
            $this->fail('Expected an InvalidInputException to be thrown.');
        } catch (InvalidInputException $e) {
            $this->assertStringContainsString($dangerous_key, $e->getMessage());
            $this->assertStringNotContainsString('&lt;script&gt;', $e->getMessage());
            $this->assertStringNotContainsString('&quot;', $e->getMessage());
        }
    }

    /** Same rejection end-to-end: it passes grind() and the permission check, perform() rejects it. */
    public function testMaybePerformAsRejectsUnknownLanguageKeyAfterPermissionCheckAndNeverTouchesSettings(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('isPageTranslationEnabled');
        $repository->expects($this->never())->method('setPageTranslationEnabled');

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => 'xx', 'enabled' => true]);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertSame(400, $result->error()->getCode());
    }

    /** A language_key with surrounding whitespace resolves to the same setting key as via perform(). */
    public function testMaybePerformAsTrimsLanguageKeyBeforeBuildingTheSettingKey(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->method('isPageTranslationEnabled')->with('de')->willReturn(false);
        $repository->expects($this->once())->method('setPageTranslationEnabled')->with('de', true);

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => '  de  ', 'enabled' => true]);

        $this->assertFalse($result->isError());
        $this->assertSame('de', $result->value()['language_key']);
    }

    public function testInputDescriptionBuildsAGroupWithARequiredLanguageKeyTextFieldAndAnEnabledCheckbox(): void
    {
        $text = $this->createMock(Text::class);
        $text->expects($this->once())->method('withRequired')->with(true)->willReturnSelf();
        $text->expects($this->once())->method('withDedicatedName')->with('language_key')->willReturnSelf();

        $checkbox = $this->createMock(Checkbox::class);
        $checkbox->expects($this->once())
            ->method('withDedicatedName')
            ->with('enabled')
            ->willReturnSelf();

        $field = $this->createMock(\ILIAS\UI\Component\Input\Field\Factory::class);
        $field->expects($this->once())
            ->method('text')
            ->with(
                'Language key',
                'Language key the page translation setting applies to, e.g. de, fr, it.'
            )
            ->willReturn($text);
        $field->expects($this->once())
            ->method('checkbox')
            ->with(
                'Enabled',
                'Whether page translation should be enabled for this language.'
            )
            ->willReturn($checkbox);

        $group = $this->createMock(\ILIAS\UI\Component\Input\Field\Section::class);
        $field->expects($this->once())
            ->method('section')
            ->with(['language_key' => $text, 'enabled' => $checkbox], $this->anything())
            ->willReturn($group);

        $activity = $this->createActivity();

        $this->assertSame($group, $activity->getInputDescription($field));
    }

    /** Real DescriptionFactory: a mocked with([...]) would accept the fields in any order. */
    public function testOutputDescriptionDescribesTheThreeDocumentedFieldsInOrder(): void
    {
        $activity = $this->createActivity();

        $description = $activity->getOutputDescription(new DescriptionFactory());

        $field_names = [];
        foreach ($description->getFields() as $field) {
            $field_names[] = $field->getName();
        }

        $this->assertSame(['language_key', 'enabled', 'changed'], $field_names);
    }

    private function createActivity(
        ?\ilRbacSystem $rbac_system = null,
        ?RepositoryProvider $repository = null,
        ?Language $language = null,
        int $language_folder_ref_id = 0,
        array $installed_languages = ['de', 'fr'],
    ): SetLanguageTranslationEnabled {
        $repository ??= $this->createMock(RepositoryProvider::class);
        $repository->method('getInstalledLanguages')->willReturn($installed_languages);

        return new SetLanguageTranslationEnabled(
            $language ?? $this->createMock(Language::class),
            $this->rbacOf($rbac_system ?? $this->createMock(\ilRbacSystem::class)),
            $this->languageFolder($language_folder_ref_id),
            $repository,
        );
    }
}
