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
use ILIAS\Language\Activities\SafeToDisplayActivityError;
use ILIAS\Language\Activities\SetLanguageTranslationEnabled;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Text;
use ILIAS\UI\Component\Input\Field\Checkbox;
use ILIAS\Administration\Setting;
use ILIAS\Data\Description\Factory as DescriptionFactory;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Refinery\String\Group as StringGroup;
use ILIAS\Refinery\String\MarkdownFormattingToHTML;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * SetLanguageTranslationEnabled is, like its sibling
 * SetLanguageDetectionEnabled, backed by a single \ILIAS\Administration\Setting
 * collaborator - a plain interface, mockable directly, never a real
 * \ilSetting/database connection. Unlike SetLanguageDetectionEnabled, this
 * Activity is parameterised by a language_key (the setting written/read is
 * "lang_translate_<key>", not a single fixed key), and perform() itself
 * (not just normalizeParameters()) validates its $parameters array and
 * computes a `changed` flag by comparing the requested value against the
 * *currently stored* one (read via a falsy (bool) cast, exactly mirroring
 * `ilObjLanguageAccess::_checkTranslate()` - see `$currently_enabled = (bool)
 * ($this->settings)()->get($translate_key, '0')` in perform()). That
 * comparison, and the "write only if changed" behaviour it drives, is the
 * most important regression surface in this file: a mutant that always
 * writes (or that inverts the comparison) must be caught here.
 *
 * The second most important regression this file guards is the same one as
 * SetLanguageDetectionEnabledTest: the enforced "write" RBAC check in
 * isAllowedToPerform()/maybePerformAs() must actually prevent both the
 * outcome *and* the side effect (no write to Settings) when denied.
 */
class SetLanguageTranslationEnabledTest extends ActivityContractTestCase
{
    protected function createDefaultActivity(): SetLanguageTranslationEnabled
    {
        return $this->createActivity();
    }

    // -----------------------------------------------------------------
    // isAllowedToPerform()
    // -----------------------------------------------------------------

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
        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('get');
        $settings->expects($this->never())->method('set');

        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $activity = $this->createActivity(rbac_system: $rbac, settings: $settings);

        $this->assertTrue($activity->isAllowedToPerform(6, ['language_key' => 'de', 'enabled' => true]));
    }

    // -----------------------------------------------------------------
    // perform() - the changed/unchanged decision
    // -----------------------------------------------------------------

    /**
     * Core business rule: perform() must read the
     * *current* value of "lang_translate_<key>" via a falsy check - exactly
     * the same interpretation ilObjLanguageAccess::_checkTranslate() applies
     * - and only write when the requested value actually differs. Every
     * combination of a realistic stored raw value ("0", "", "1") and a
     * requested boolean is exercised so that both an accidental "always
     * write" and an accidental "never write" mutant are caught, along with
     * an inverted `!==` -> `===` comparison mutant.
     */
    public static function performChangedDecisionProvider(): array
    {
        return [
            'stored "0", request true -> changed, write "1"' => ['0', true, true, '1'],
            'stored "0", request false -> unchanged, no write' => ['0', false, false, null],
            'stored "", request true -> changed, write "1" (falsy like "0")' => ['', true, true, '1'],
            'stored "", request false -> unchanged, no write (falsy like "0")' => ['', false, false, null],
            'stored "1", request true -> unchanged, no write' => ['1', true, false, null],
            'stored "1", request false -> changed, write "0"' => ['1', false, true, '0'],
        ];
    }

    #[DataProvider('performChangedDecisionProvider')]
    public function testPerformComparesRequestedValueAgainstFalsyCastOfCurrentlyStoredValue(
        string $stored_value,
        bool $requested_enabled,
        bool $expected_changed,
        ?string $expected_written_value
    ): void {
        $settings = $this->createMock(Setting::class);
        $settings->method('get')
            ->with('lang_translate_de', '0')
            ->willReturn($stored_value);

        if ($expected_written_value === null) {
            $settings->expects($this->never())->method('set');
        } else {
            $settings->expects($this->once())
                ->method('set')
                ->with('lang_translate_de', $expected_written_value);
        }

        $result = $this->createActivity(settings: $settings)
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
        $settings = $this->createMock(Setting::class);
        $settings->expects($this->once())
            ->method('get')
            ->with('lang_translate_fr', '0')
            ->willReturn('0');

        $this->createActivity(settings: $settings)->perform(['language_key' => 'fr', 'enabled' => false]);
    }

    /**
     * language_key must be trimmed before being used to build the setting
     * key and before being returned - mirroring toNonEmptyString()'s
     * behaviour used by normalizeParameters(), but exercised here directly
     * through perform() since perform() re-implements its own trim().
     */
    public function testPerformTrimsLanguageKeyForBothTheSettingKeyAndTheReturnedValue(): void
    {
        $settings = $this->createMock(Setting::class);
        $settings->method('get')->with('lang_translate_de', '0')->willReturn('0');
        $settings->expects($this->once())->method('set')->with('lang_translate_de', '1');

        $result = $this->createActivity(settings: $settings)
            ->perform(['language_key' => '  de  ', 'enabled' => true]);

        $this->assertSame('de', $result['language_key']);
    }

    // -----------------------------------------------------------------
    // perform() - parameter validation
    // -----------------------------------------------------------------

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
        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('get');
        $settings->expects($this->never())->method('set');

        $this->expectException(InvalidInputException::class);

        $this->createActivity(settings: $settings)->perform($parameters);
    }

    // -----------------------------------------------------------------
    // maybePerformAs()
    // -----------------------------------------------------------------

    public function testMaybePerformAsWithGrantedPermissionPerformsAndReturnsOkResult(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $settings = $this->createMock(Setting::class);
        $settings->method('get')->with('lang_translate_de', '0')->willReturn('0');
        $settings->expects($this->once())->method('set')->with('lang_translate_de', '1');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
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

        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('get');
        $settings->expects($this->never())->method('set');

        $language = $this->createMock(Language::class);
        $language->method('txt')->with('msg_no_perm_write')->willReturn('no write permission');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings, language: $language)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => 'de', 'enabled' => true]);

        $this->assertTrue($result->isError());
        $this->assertSame('no write permission', $result->error());
    }

    /**
     * A Throwable raised from within perform() (e.g. the Setting
     * collaborator failing to write) must be turned into a Result\Error
     * rather than propagating - this is the whole point of maybePerformAs()
     * wrapping perform() in a try/catch.
     */
    public function testThrowableFromWithinPerformIsTurnedIntoAResultError(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $settings = $this->createMock(Setting::class);
        $settings->method('get')->willReturn('0');
        $settings->method('set')->willThrowException(new \RuntimeException('database write failed'));

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => 'de', 'enabled' => true]);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(\RuntimeException::class, $error);
        $this->assertSame('database write failed', $error->getMessage());
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
            // The required 'language_key' Text field receives a blank raw
            // value (missing entirely) and fails its own required-field
            // constraint inside grind() itself - before normalizeParameters()/
            // isAllowedToPerform() are ever reached.
            'missing language_key' => [['enabled' => true]],
        ];
    }

    #[DataProvider('invalidRawParametersRejectedByGrindWithStringErrorProvider')]
    public function testMaybePerformAsRejectsRawParametersAtGrindLevelWithStringErrorWithoutTouchingSettingsOrRbac(
        array $raw_parameters
    ): void {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('get');
        $settings->expects($this->never())->method('set');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, $raw_parameters);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertNotSame('', $result->error()->getMessage());
    }

    public static function invalidRawParametersProvider(): array
    {
        return [
            // A non-string 'language_key' fails the Text field's own
            // isClientSideValueOk() check inside grind() itself, which
            // throws an InvalidArgumentException ("Display value does not
            // match input type."), caught and preserved by grind()'s
            // try/catch.
            'non-string language_key' => [['language_key' => 42, 'enabled' => true]],
            // Outside GrindsFormInput::normalizeCheckboxRawValue()'s
            // explicit whitelist - "konservativ normalisieren", not "alles
            // akzeptieren".
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

        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('get');
        $settings->expects($this->never())->method('set');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, $raw_parameters);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $result->error());
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

        $settings = $this->createMock(Setting::class);
        $settings->method('get')->with('lang_translate_de', '0')->willReturn('1');
        $settings->expects($this->once())->method('set')->with('lang_translate_de', '0');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
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

        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('get');
        $settings->expects($this->never())->method('set');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => '   ', 'enabled' => true]);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $result->error());
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

        $settings = $this->createMock(Setting::class);
        $settings->method('get')->with('lang_translate_de', '0')->willReturn('0');
        $settings->expects($this->once())->method('set')->with('lang_translate_de', '1');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => 'de', 'enabled' => $value]);

        $this->assertFalse($result->isError());
        $this->assertSame(
            ['language_key' => 'de', 'enabled' => true, 'changed' => true],
            $result->value()
        );
    }

    /**
     * Same tolerance as above, but for the common primitive representations
     * of "false".
     */
    #[DataProvider('tolerantlyAcceptedFalsyEnabledValuesProvider')]
    public function testMaybePerformAsToleratesCommonPrimitiveFalsyEnabledRepresentations(mixed $value): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $settings = $this->createMock(Setting::class);
        $settings->method('get')->with('lang_translate_de', '0')->willReturn('1');
        $settings->expects($this->once())->method('set')->with('lang_translate_de', '0');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => 'de', 'enabled' => $value]);

        $this->assertFalse($result->isError());
        $this->assertSame(
            ['language_key' => 'de', 'enabled' => false, 'changed' => true],
            $result->value()
        );
    }

    // -----------------------------------------------------------------
    // language_key must be an installed language (Punkt 4)
    // -----------------------------------------------------------------

    /**
     * Regression test for Punkt 4 of this component's grinding task: unlike
     * before, an unknown/not-installed language_key is now rejected by
     * perform() itself, for direct perform()/isAllowedToPerform() callers
     * too - not only via maybePerformAs().
     */
    public function testPerformRejectsUnknownLanguageKeyWithoutTouchingSettings(): void
    {
        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('get');
        $settings->expects($this->never())->method('set');

        $this->expectException(InvalidInputException::class);

        $this->createActivity(settings: $settings)
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
        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('get');
        $settings->expects($this->never())->method('set');

        $dangerous_key = '<script>alert(1)</script>&"quoted"';

        try {
            $this->createActivity(settings: $settings)
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

        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('get');
        $settings->expects($this->never())->method('set');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => 'xx', 'enabled' => true]);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $result->error());
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

        $settings = $this->createMock(Setting::class);
        $settings->method('get')->with('lang_translate_de', '0')->willReturn('0');
        $settings->expects($this->once())->method('set')->with('lang_translate_de', '1');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_key' => '  de  ', 'enabled' => true]);

        $this->assertFalse($result->isError());
        $this->assertSame('de', $result->value()['language_key']);
    }

    // -----------------------------------------------------------------
    // getInputDescription() / getOutputDescription() / getDescription()
    // -----------------------------------------------------------------

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

        $group = $this->createMock(\ILIAS\UI\Component\Input\Field\Group::class);
        $field->expects($this->once())
            ->method('group')
            ->with(['language_key' => $text, 'enabled' => $checkbox])
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
        $string_group = $this->createMock(StringGroup::class);
        $string_group->method('markdown')->willReturn(
            $this->createMock(MarkdownFormattingToHTML::class)
        );
        $refinery = $this->createMock(RefineryFactory::class);
        $refinery->method('string')->willReturn($string_group);

        $activity = $this->createActivity(refinery: $refinery);

        $description = $activity->getOutputDescription(new DescriptionFactory());

        $field_names = [];
        foreach ($description->getFields() as $field) {
            $field_names[] = $field->getName();
        }

        $this->assertSame(['language_key', 'enabled', 'changed'], $field_names);
    }

    // -----------------------------------------------------------------
    // helpers
    // -----------------------------------------------------------------

    private function createActivity(
        ?\ilRbacSystem $rbac_system = null,
        ?Setting $settings = null,
        ?Language $language = null,
        int $language_folder_ref_id = 0,
        ?RefineryFactory $refinery = null,
        ?InstalledLanguageRepository $installed_language_repository = null,
    ): SetLanguageTranslationEnabled {
        return new SetLanguageTranslationEnabled(
            $refinery ?? $this->createMock(RefineryFactory::class),
            $language ?? $this->createMock(Language::class),
            $rbac_system ?? $this->createMock(\ilRbacSystem::class),
            $settings ?? $this->createMock(Setting::class),
            installed_language_repository: $installed_language_repository
                ?? new FakeInstalledLanguageRepository(static fn(): array => ['de', 'fr']),
            language_folder_ref_id: $language_folder_ref_id,
        );
    }
}
