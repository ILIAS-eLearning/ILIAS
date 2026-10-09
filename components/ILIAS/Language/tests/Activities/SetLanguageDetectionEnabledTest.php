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
use ILIAS\Language\Activities\SetLanguageDetectionEnabled;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Checkbox;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\Data\Description\Description;
use ILIAS\Administration\Setting;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * SetLanguageDetectionEnabled has no ilSetupLanguage collaborator and no
 * $lng_objects/$obj_language_factory closures (unlike UninstallLanguage/
 * RemoveLocalLanguageChanges) - instead it wraps a single
 * \ILIAS\Administration\Setting collaborator ("lang_detection") which is
 * a plain interface and can therefore be mocked directly, unlike
 * \ilObjLanguage on the sibling Activities.
 *
 * The most important regression this file guards is the *new* enforced
 * "write" RBAC check in isAllowedToPerform()/maybePerformAs(): before this
 * extraction, neither enableLanguageDetectionObject() nor
 * disableLanguageDetectionObject() performed any permission check of their
 * own. A test here must prove that a denied permission both yields a
 * Result\Error *and* leaves the Setting collaborator untouched (no side
 * effect on denial).
 */
class SetLanguageDetectionEnabledTest extends ActivityContractTestCase
{
    protected function createDefaultActivity(): SetLanguageDetectionEnabled
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

        $this->assertTrue($activity->isAllowedToPerform(6, ['enabled' => true]));
    }

    public function testIsAllowedToPerformReturnsFalseWhenRbacDenies(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(false);

        $activity = $this->createActivity(rbac_system: $rbac);

        $this->assertFalse($activity->isAllowedToPerform(6, ['enabled' => true]));
    }

    /**
     * isAllowedToPerform() must be a pure permission check: it must never
     * touch the Setting collaborator (and therefore never write
     * "lang_detection") - unlike perform(), which is the only place
     * allowed to have side effects.
     */
    public function testIsAllowedToPerformNeverTouchesSettings(): void
    {
        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('set');

        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $activity = $this->createActivity(rbac_system: $rbac, settings: $settings);

        $this->assertTrue($activity->isAllowedToPerform(6, ['enabled' => true]));
    }

    // -----------------------------------------------------------------
    // perform()
    // -----------------------------------------------------------------

    public function testPerformWithEnabledTrueWritesOneIntoLangDetectionSettingAndReturnsEnabledTrue(): void
    {
        $settings = $this->createMock(Setting::class);
        $settings->expects($this->once())
            ->method('set')
            ->with('lang_detection', '1');

        $result = $this->createActivity(settings: $settings)->perform(['enabled' => true]);

        $this->assertSame(['enabled' => true], $result);
    }

    public function testPerformWithEnabledFalseWritesZeroIntoLangDetectionSettingAndReturnsEnabledFalse(): void
    {
        $settings = $this->createMock(Setting::class);
        $settings->expects($this->once())
            ->method('set')
            ->with('lang_detection', '0');

        $result = $this->createActivity(settings: $settings)->perform(['enabled' => false]);

        $this->assertSame(['enabled' => false], $result);
    }

    public function testPerformRejectsMissingParametersArray(): void
    {
        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('set');

        $this->expectException(InvalidInputException::class);

        $this->createActivity(settings: $settings)->perform([]);
    }

    public function testPerformRejectsNonArrayParameters(): void
    {
        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('set');

        $this->expectException(InvalidInputException::class);

        $this->createActivity(settings: $settings)->perform('not-an-array');
    }

    /**
     * Boundary/type-juggling regression: perform() must require a strict
     * bool for 'enabled' and must never silently cast a truthy/falsy
     * look-alike (string "1"/"0"/"true", int 1, etc.) - unlike PHP's own
     * loose comparison rules, which would happily accept any of these.
     */
    #[DataProvider('nonBooleanEnabledValuesProvider')]
    public function testPerformRejectsNonStrictBooleanEnabledValues(mixed $value): void
    {
        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('set');

        $this->expectException(InvalidInputException::class);

        $this->createActivity(settings: $settings)->perform(['enabled' => $value]);
    }

    public static function nonBooleanEnabledValuesProvider(): array
    {
        return [
            'string "1"' => ['1'],
            'string "true"' => ['true'],
            'string "0"' => ['0'],
            'string "false"' => ['false'],
            'string ""' => [''],
            'int 1' => [1],
            'int 0' => [0],
            'float 1.0' => [1.0],
            'null' => [null],
            'array' => [[true]],
        ];
    }

    // -----------------------------------------------------------------
    // maybePerformAs()
    // -----------------------------------------------------------------

    public function testMaybePerformAsWithGrantedPermissionPerformsAndReturnsOkResult(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $settings = $this->createMock(Setting::class);
        $settings->expects($this->once())->method('set')->with('lang_detection', '1');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['enabled' => true]);

        $this->assertFalse($result->isError());
        $this->assertSame(['enabled' => true], $result->value());
    }

    /**
     * Core security regression test: before this extraction,
     * enableLanguageDetectionObject()/disableLanguageDetectionObject()
     * performed no RBAC check of their own at all - a request forged
     * directly against the corresponding ilCtrl
     * commands by a user with only "read" access on the language folder
     * would still have flipped "lang_detection". isAllowedToPerform() now
     * enforces a "write" check, and this must actually cause
     * maybePerformAs() to reject the request (Result\Error) *and* leave the
     * Setting collaborator completely untouched - a permission check that
     * merely rejected the outcome without preventing the side effect would
     * not close the gap at all.
     */
    public function testMaybePerformAsWithDeniedPermissionReturnsErrorAndNeverWritesTheSetting(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())
            ->method('checkAccessOfUser')
            ->with(6, 'write', $this->anything())
            ->willReturn(false);

        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('set');

        $language = $this->createMock(Language::class);
        $language->method('txt')->with('msg_no_perm_write')->willReturn('no write permission');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings, language: $language)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['enabled' => true]);

        $this->assertTrue($result->isError());
    }

    /**
     * A Throwable raised from within perform() (or normalizeParameters())
     * must be turned into a Result\Error rather than propagating - this is
     * the whole point of maybePerformAs() wrapping perform() in a
     * try/catch.
     */
    public function testMaybePerformAsTurnsAThrowableFromNormalizeParametersIntoAResultError(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('set');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['enabled' => 'not-a-bool']);

        $this->assertTrue($result->isError());
        // 'not-a-bool' is outside normalizeCheckboxRawValue()'s explicit
        // whitelist - grind()'s dedicated catch block converts the
        // resulting \InvalidArgumentException into an InvalidInputException
        // (a SafeToDisplayActivityError), not a raw \InvalidArgumentException.
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $result->error());
    }

    /**
     * The 'enabled' Checkbox field is not required, so - now that
     * maybePerformAs() actually grinds $raw_parameters through
     * getInputDescription() (see GrindsFormInput) - a completely missing
     * 'enabled' key is no longer rejected: it defaults to false, exactly
     * like an unchecked, and therefore never submitted, HTML checkbox
     * would. This is a deliberate behavioural change from the old
     * (pre-grinding) normalizeParameters(), which used to reject a missing
     * key outright. Unlike before, the request now actually reaches
     * isAllowedToPerform()/perform().
     */
    public function testMaybePerformAsWithMissingEnabledKeyDefaultsToFalseAndStillPerformsTheWrite(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())
            ->method('checkAccessOfUser')
            ->willReturn(true);

        $settings = $this->createMock(Setting::class);
        $settings->expects($this->once())->method('set')->with('lang_detection', '0');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, []);

        $this->assertFalse($result->isError());
        $this->assertSame(['enabled' => false], $result->value());
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
     * unlike perform() itself (which still requires a strict bool, see
     * testPerformRejectsNonStrictBooleanEnabledValues), maybePerformAs()
     * now accepts the common primitive representations of "true" a generic
     * (non-HTML) caller might reasonably send, and writes the setting
     * accordingly.
     */
    #[DataProvider('tolerantlyAcceptedTruthyEnabledValuesProvider')]
    public function testMaybePerformAsToleratesCommonPrimitiveTruthyRepresentationsAndWritesEnabledTrue(mixed $value): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $settings = $this->createMock(Setting::class);
        $settings->expects($this->once())->method('set')->with('lang_detection', '1');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['enabled' => $value]);

        $this->assertFalse($result->isError());
        $this->assertSame(['enabled' => true], $result->value());
    }

    /**
     * Same tolerance as above, but for the common primitive representations
     * of "false".
     */
    #[DataProvider('tolerantlyAcceptedFalsyEnabledValuesProvider')]
    public function testMaybePerformAsToleratesCommonPrimitiveFalsyRepresentationsAndWritesEnabledFalse(mixed $value): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $settings = $this->createMock(Setting::class);
        $settings->expects($this->once())->method('set')->with('lang_detection', '0');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['enabled' => $value]);

        $this->assertFalse($result->isError());
        $this->assertSame(['enabled' => false], $result->value());
    }

    public static function rejectedEnabledValuesProvider(): array
    {
        return [
            'float 1.0' => [1.0],
            'array' => [[true]],
        ];
    }

    /**
     * Only values outside GrindsFormInput::normalizeCheckboxRawValue()'s
     * explicit whitelist are still rejected via maybePerformAs() - "konservativ
     * normalisieren", not "alles akzeptieren".
     */
    #[DataProvider('rejectedEnabledValuesProvider')]
    public function testMaybePerformAsRejectsValuesGrindCannotInterpretAsACheckboxWithoutTouchingSettings(mixed $value): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $settings = $this->createMock(Setting::class);
        $settings->expects($this->never())->method('set');

        $result = $this->createActivity(rbac_system: $rbac, settings: $settings)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['enabled' => $value]);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $result->error());
    }

    // -----------------------------------------------------------------
    // getInputDescription() / getOutputDescription()
    // -----------------------------------------------------------------

    public function testInputDescriptionBuildsAGroupWithASingleEnabledCheckboxField(): void
    {
        $checkbox = $this->createMock(Checkbox::class);
        $checkbox->expects($this->once())
            ->method('withDedicatedName')
            ->with('enabled')
            ->willReturnSelf();

        $field = $this->createMock(\ILIAS\UI\Component\Input\Field\Factory::class);
        $field->expects($this->once())
            ->method('checkbox')
            ->with(
                'Enabled',
                'Whether automatic language detection from the browser should be enabled.'
            )
            ->willReturn($checkbox);

        $group = $this->createMock(Group::class);
        $field->expects($this->once())
            ->method('group')
            ->with(['enabled' => $checkbox])
            ->willReturn($group);

        $activity = $this->createActivity();

        $this->assertSame($group, $activity->getInputDescription($field));
    }

    public function testOutputDescriptionDescribesASingleBooleanEnabledField(): void
    {
        $bool_description = $this->createMock(Description::class);
        $object_description = $this->createMock(Description::class);

        $f = $this->createMock(\ILIAS\Data\Description\Factory::class);
        $f->expects($this->once())->method('bool')->willReturn($bool_description);
        $f->expects($this->once())
            ->method('object')
            ->with($this->anything(), ['enabled' => $bool_description])
            ->willReturn($object_description);

        $this->assertSame($object_description, $this->createActivity()->getOutputDescription($f));
    }

    // -----------------------------------------------------------------
    // helpers
    // -----------------------------------------------------------------

    private function createActivity(
        ?\ilRbacSystem $rbac_system = null,
        ?Setting $settings = null,
        ?Language $language = null,
        int $language_folder_ref_id = 0
    ): SetLanguageDetectionEnabled {
        return new SetLanguageDetectionEnabled(
            $this->createMock(RefineryFactory::class),
            $language ?? $this->createMock(Language::class),
            $rbac_system ?? $this->createMock(\ilRbacSystem::class),
            $settings ?? $this->createMock(Setting::class),
            $language_folder_ref_id
        );
    }
}
