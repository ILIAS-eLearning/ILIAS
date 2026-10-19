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
use ILIAS\Language\Activities\SetLanguageDetectionEnabled;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Checkbox;
use ILIAS\Data\Description\Description;
use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * SetLanguageDetectionEnabled writes the "lang_detection" setting through the RepositoryProvider, which is a plain
 * interface and can be mocked directly, unlike \ilObjLanguage on the sibling Activities. A denied permission must
 * yield a Result\Error and leave the Setting untouched.
 */
class SetLanguageDetectionEnabledTest extends ActivityContractTestCase
{
    protected function createDefaultActivity(): SetLanguageDetectionEnabled
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

        $this->assertTrue($activity->isAllowedToPerform(6, ['enabled' => true]));
    }

    public function testIsAllowedToPerformReturnsFalseWhenRbacDenies(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(false);

        $activity = $this->createActivity(rbac_system: $rbac);

        $this->assertFalse($activity->isAllowedToPerform(6, ['enabled' => true]));
    }

    /** isAllowedToPerform() is a pure permission check: no Setting access. */
    public function testIsAllowedToPerformNeverTouchesSettings(): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('setLanguageDetectionEnabled');

        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $activity = $this->createActivity(rbac_system: $rbac, repository: $repository);

        $this->assertTrue($activity->isAllowedToPerform(6, ['enabled' => true]));
    }

    public function testPerformWithEnabledTrueWritesOneIntoLangDetectionSettingAndReturnsEnabledTrue(): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->once())
            ->method('setLanguageDetectionEnabled')->with(true);

        $result = $this->createActivity(repository: $repository)->perform(['enabled' => true]);

        $this->assertSame(['enabled' => true], $result);
    }

    public function testPerformWithEnabledFalseWritesZeroIntoLangDetectionSettingAndReturnsEnabledFalse(): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->once())
            ->method('setLanguageDetectionEnabled')->with(false);

        $result = $this->createActivity(repository: $repository)->perform(['enabled' => false]);

        $this->assertSame(['enabled' => false], $result);
    }

    public function testPerformRejectsMissingParametersArray(): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('setLanguageDetectionEnabled');

        $this->expectException(InvalidInputException::class);

        $this->createActivity(repository: $repository)->perform([]);
    }

    public function testPerformRejectsNonArrayParameters(): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('setLanguageDetectionEnabled');

        $this->expectException(InvalidInputException::class);

        $this->createActivity(repository: $repository)->perform('not-an-array');
    }

    /** perform() requires a strict bool for 'enabled'; truthy/falsy look-alikes ("1", 1, ...) are rejected. */
    #[DataProvider('nonBooleanEnabledValuesProvider')]
    public function testPerformRejectsNonStrictBooleanEnabledValues(mixed $value): void
    {
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('setLanguageDetectionEnabled');

        $this->expectException(InvalidInputException::class);

        $this->createActivity(repository: $repository)->perform(['enabled' => $value]);
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

    public function testMaybePerformAsWithGrantedPermissionPerformsAndReturnsOkResult(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->once())->method('setLanguageDetectionEnabled')->with(true);

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['enabled' => true]);

        $this->assertFalse($result->isError());
        $this->assertSame(['enabled' => true], $result->value());
    }

    /** The permission check is a real gate: a denied "write" yields a Result\Error and leaves the Setting untouched. */
    public function testMaybePerformAsWithDeniedPermissionReturnsErrorAndNeverWritesTheSetting(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())
            ->method('checkAccessOfUser')
            ->with(6, 'write', $this->anything())
            ->willReturn(false);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('setLanguageDetectionEnabled');

        $language = $this->createMock(Language::class);
        $language->method('txt')->with('msg_no_perm_write')->willReturn('no write permission');

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository, language: $language)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['enabled' => true]);

        $this->assertTrue($result->isError());
    }

    public function testMaybePerformAsRejectsAnEnabledValueOutsideTheCheckboxWhitelistAsA400WithoutTouchingSettings(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('setLanguageDetectionEnabled');

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['enabled' => 'not-a-bool']);

        $this->assertTrue($result->isError());
        // outside the whitelist of normalizeCheckboxRawValue(): grind() turns it into an InvalidInputException
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertSame(400, $result->error()->getCode());
    }

    /** A missing 'enabled' defaults to false like an unchecked HTML checkbox, and still reaches perform(). */
    public function testMaybePerformAsWithMissingEnabledKeyDefaultsToFalseAndStillPerformsTheWrite(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())
            ->method('checkAccessOfUser')
            ->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->once())->method('setLanguageDetectionEnabled')->with(false);

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, []);

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

    /** Unlike perform(), maybePerformAs() accepts common primitive representations of "true". */
    #[DataProvider('tolerantlyAcceptedTruthyEnabledValuesProvider')]
    public function testMaybePerformAsToleratesCommonPrimitiveTruthyRepresentationsAndWritesEnabledTrue(mixed $value): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->once())->method('setLanguageDetectionEnabled')->with(true);

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['enabled' => $value]);

        $this->assertFalse($result->isError());
        $this->assertSame(['enabled' => true], $result->value());
    }

    /** Same for "false". */
    #[DataProvider('tolerantlyAcceptedFalsyEnabledValuesProvider')]
    public function testMaybePerformAsToleratesCommonPrimitiveFalsyRepresentationsAndWritesEnabledFalse(mixed $value): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->once())->method('setLanguageDetectionEnabled')->with(false);

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
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
     * Values outside the whitelist of GrindsFormInput::normalizeCheckboxRawValue() are still rejected.
     */
    #[DataProvider('rejectedEnabledValuesProvider')]
    public function testMaybePerformAsRejectsValuesGrindCannotInterpretAsACheckboxWithoutTouchingSettings(mixed $value): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->expects($this->never())->method('setLanguageDetectionEnabled');

        $result = $this->createActivity(rbac_system: $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['enabled' => $value]);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertSame(400, $result->error()->getCode());
    }

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

        $group = $this->createMock(\ILIAS\UI\Component\Input\Field\Section::class);
        $field->expects($this->once())
            ->method('section')
            ->with(['enabled' => $checkbox], $this->anything())
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

    private function createActivity(
        ?\ilRbacSystem $rbac_system = null,
        ?RepositoryProvider $repository = null,
        ?Language $language = null,
        int $language_folder_ref_id = 0
    ): SetLanguageDetectionEnabled {
        return new SetLanguageDetectionEnabled(
            $language ?? $this->createMock(Language::class),
            $this->rbacOf($rbac_system ?? $this->createMock(\ilRbacSystem::class)),
            $this->languageFolder($language_folder_ref_id),
            $repository ?? $this->createMock(RepositoryProvider::class)
        );
    }
}
