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

    /**
     * isAllowedToPerform() must be a pure permission check: it must never
     * touch the Setting collaborator (and therefore never read/write
     * "lang_translate_*") - unlike perform(), which is the only place
     * allowed to have side effects.
     */
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

    /**
     * get() must be called with the correct per-language key
     * ("lang_translate_" . language_key) and the documented default '0' -
     * a mutant that drops the default, or concatenates the key in the wrong
     * order/without the separator, must be caught here.
     */
    public function testPerformReadsTheCorrectPerLanguageSettingKeyWithDefaultZero(): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->once())
            ->method('isPageTranslationEnabled')->with('fr')->willReturn(false);

        $this->createActivity(repository: $repository)->perform(['language_key' => 'fr', 'enabled' => false]);
    }

    /**
     * language_key must be trimmed before being used to build the setting
     * key and before being returned - mirroring toNonEmptyString()'s
     * behaviour used by normalizeParameters(), but exercised here directly
     * through perform() since perform() re-implements its own trim().
     */
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

    /**
     * Permission check regression: a denied "write" access must both yield
     * a Result\Error *and* leave the Setting collaborator completely
     * untouched (no read, no write) - a permission check that merely
     * rejected the outcome without preventing the read/write would not be a
     * real gate.
     */
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

    /**
     * A Throwable raised from within perform() (e.g. the Setting
     * collaborator failing to write) must be turned into a Result\Error
     * rather than propagating - this is the whole point of maybePerformAs()
     * wrapping perform() in a try/catch.
     */
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

    /**
     * A completely missing 'language_key' key is caught by grind() itself
     * (see GrindsFormInput): the required Text field receives a blank raw
     * value and fails its own required-field constraint before
     * normalizeParameters()/isAllowedToPerform() are ever reached - so the
     * rejection surfaces as a Result\Error carrying a field-attributed
     * InvalidInputException (see GrindsFormInput::describeInputError()), not
     * an InvalidArgumentException. The rbac system must never be touched
     * either way (validation happens first).
     */
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

    /**
     * The 'enabled' Checkbox field is not required, so a completely
     * missing 'enabled' key is no longer rejected by grind() - it defaults
     * to false, exactly like an unchecked, and therefore never submitted,
     * HTML checkbox would (same behaviour as
     * SetLanguageDetectionEnabledTest's equivalent regression test).
     * Unlike a rejection at grind level, this reaches isAllowedToPerform()/
     * perform() and can still write the setting if that changes the
     * currently stored value.
     */
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

    /**
     * A whitespace-only language_key value satisfies the Text field's own
     * required-check (min length 1 on the raw string, before trimming), so
     * grind() accepts it and the request DOES reach isAllowedToPerform() -
     * unlike the fully-missing-key case above. It is perform() itself
     * (via its own trim() check, see
     * testPerformRejectsInvalidOrIncompleteParametersWithoutTouchingSettings)
     * that ultimately rejects it.
     */
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

    /**
     * Grinding tolerance (see GrindsFormInput::normalizeCheckboxRawValue()):
     * unlike perform() itself (which still requires a strict bool),
     * maybePerformAs() now accepts the common primitive representations of
     * "true" a generic (non-HTML) caller might reasonably send.
     */
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

    /**
     * Same tolerance for the common representations of "false".
     */
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

    /**
     * Regression test for Punkt 4 of this component's grinding task: unlike
     * before, an unknown/not-installed language_key is now rejected by
     * perform() itself, for direct perform()/isAllowedToPerform() callers
     * too - not only via maybePerformAs().
     */
    public function testPerformRejectsUnknownLanguageKeyWithoutTouchingSettings(): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('isPageTranslationEnabled');
        $repository->expects($this->never())->method('setPageTranslationEnabled');

        $this->expectException(InvalidInputException::class);

        $this->createActivity(repository: $repository)
            ->perform(['language_key' => 'xx', 'enabled' => true]);
    }

    /**
     * Regression test: the "Unknown language key" InvalidInputException
     * message embeds the offending language_key RAW/unescaped - escaping
     * HTML-significant characters for safe display is deliberately no
     * longer this domain layer's job (see perform()'s own comment above the
     * throw), only \ILIAS\Language\RendersActivityErrors::
     * activityErrorMessage()'s. If this message were pre-escaped here, it
     * would end up double-escaped once it reaches that single seam.
     */
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

    /**
     * The same rejection, exercised end-to-end through maybePerformAs():
     * the unknown language_key passes grind() (any non-blank string
     * satisfies the Text field's own required-check) and the permission
     * check (isAllowedToPerform() does not know about installed languages
     * either), so it is perform() itself that rejects it - and the Setting
     * collaborator must never be touched.
     */
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

    /**
     * normalizeParameters()'s toNonEmptyString() must trim language_key
     * before it reaches perform() - exercised end-to-end through
     * maybePerformAs() so a caller submitting e.g. a copy-pasted language
     * key with surrounding whitespace still resolves to the same setting
     * key as perform() itself would (see
     * testPerformTrimsLanguageKeyForBothTheSettingKeyAndTheReturnedValue).
     */
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

    /**
     * Uses a real DescriptionFactory (like AddLanguageEntryTest's own
     * output-description test) rather than a mocked one, so that
     * getFields()'s *actual* iteration order is asserted - a mocked
     * object()->with([...]) call would accept the three fields in any
     * order (PHPUnit's array comparison is order-independent), silently
     * missing a mutation that swapped e.g. 'enabled' and 'changed'.
     */
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
