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

use ILIAS\Language\Activities\AddLanguageEntry;
use ILIAS\Language\Activities\InvalidInputException;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Text;
use ILIAS\Data\Description\Factory as DescriptionFactory;
use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * Mocks the RepositoryProvider, answering from the closures of createActivity(); no real database or lang file.
 */
class AddLanguageEntryTest extends ActivityContractTestCase
{
    use AddLanguageEntryTestFixture;

    protected function createDefaultActivity(): AddLanguageEntry
    {
        return $this->createActivity([]);
    }

    public function testIsAllowedToPerformDelegatesToRbacSystemWithWriteAndTheConfiguredRefId(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())
            ->method('checkAccessOfUser')
            ->with(6, 'write', 42)
            ->willReturn(true);

        $activity = $this->createActivity([], rbac: $rbac, language_folder_ref_id: 42);

        $this->assertTrue($activity->isAllowedToPerform(6, ['module' => 'common', 'identifier' => 'foo']));
    }

    public function testIsAllowedToPerformReturnsFalseWhenRbacDenies(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(false);

        $activity = $this->createActivity([], rbac: $rbac);

        $this->assertFalse($activity->isAllowedToPerform(6, ['module' => 'common', 'identifier' => 'foo']));
    }

    public function testIsAllowedToPerformNeverTouchesRepositoryOrWriteClosures(): void
    {
        $repository = new FakeInstalledLanguageRepository(static function (): array {
            throw new \LogicException('getInstalledLanguages() must never be called by isAllowedToPerform()');
        });

        $rbac = $this->createStub(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $replace_lang_entry = static function (): bool {
            throw new \LogicException('replace_lang_entry must never be called by isAllowedToPerform()');
        };
        $update_module_cache = static function (): void {
            throw new \LogicException('update_module_cache must never be called by isAllowedToPerform()');
        };

        $activity = $this->createActivity(
            [],
            rbac: $rbac,
            installed_language_repository: $repository,
            replace_lang_entry: $replace_lang_entry,
            update_module_cache: $update_module_cache
        );

        $this->assertTrue($activity->isAllowedToPerform(6, ['module' => 'common', 'identifier' => 'foo']));
    }

    public function testPermissionDeniedBeforePerformNeverCallsWriteClosuresAndReturnsA403ResultError(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())
            ->method('checkAccessOfUser')
            ->with(6, 'write', $this->anything())
            ->willReturn(false);

        // getInputDescription() calls txt('meta_l_de') before isAllowedToPerform(), so the mock must allow it too
        $language = $this->createStub(Language::class);
        $language->method('txt')->willReturnCallback(
            static fn(string $key): string => $key === 'msg_no_perm_write' ? 'no write permission' : $key
        );

        $calls = [];
        $activity = $this->createActivity(
            ['de'],
            language: $language,
            rbac: $rbac,
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo'],
        ]);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(\DomainException::class, $result->error());
        $this->assertSame(403, $result->error()->getCode());
        $this->assertArrayNotHasKey('replace', $calls);
    }

    public function testPermissionGrantedPerformsAndReturnsOkResultWithExpectedStructure(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $activity = $this->createActivity(['de', 'fr'], rbac: $rbac);

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo'],
        ]);

        $this->assertFalse($result->isError());
        $value = $result->value();
        $this->assertSame('common', $value['module']);
        $this->assertSame('new_topic', $value['identifier']);
        $this->assertSame(['de'], $value['added_language_keys']);
        $this->assertSame(['fr'], $value['skipped_empty_language_keys']);
    }

    public function testUsrIdArgumentIsUsedForBothThePermissionCheckAndTheAuditLoginRegardlessOfRawParameters(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())
            ->method('checkAccessOfUser')
            ->with(6, 'write', $this->anything())
            ->willReturn(true);

        $login_calls = [];
        $user_login = static function (int $usr_id) use (&$login_calls): string {
            $login_calls[] = $usr_id;
            return 'login';
        };

        $activity = $this->createActivity(['de'], rbac: $rbac, user_login: $user_login);

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo'],
            // A caller-supplied usr_id must be ignored entirely.
            'usr_id' => 999,
        ]);

        $this->assertFalse($result->isError());
        $this->assertSame([6], $login_calls);
    }

    public function testDefectFromWithinPerformIsMaskedAsA500ResultErrorKeepingTheOriginal(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $replace_lang_entry = static function (): bool {
            throw new \RuntimeException('database write failed');
        };

        $activity = $this->createActivity(
            ['de'],
            rbac: $rbac,
            replace_lang_entry: $replace_lang_entry
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo'],
        ]);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertSame(500, $error->getCode());
        $this->assertStringNotContainsString('database', $error->getMessage());
        $this->assertSame('database write failed', $error->getPrevious()?->getMessage());
    }

    // grind() already rejects a missing 'module', so rbac must never be touched
    public function testMissingModuleViaMaybePerformAsIsAResultErrorAndNeverChecksPermission(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $activity = $this->createActivity(['de'], rbac: $rbac);

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo'],
        ]);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertStringContainsString('module:', $result->error()->getMessage());
    }

    // unlike the top level, a nested group rejects unknown keys outright
    public function testMaybePerformAsRejectsAnUnknownLanguageKeyInsideTheNestedTranslationsGroup(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $calls = [];
        $activity = $this->createActivity(
            ['de'],
            rbac: $rbac,
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => [
                'de' => 'Hallo',
                'xx' => 'whatever',
            ],
        ]);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(InvalidInputException::class, $error);
        $this->assertSame(400, $error->getCode());
        $this->assertStringContainsString('Unknown key(s) for translations: xx', $error->getMessage());
        $this->assertArrayNotHasKey('replace', $calls);
        $this->assertArrayNotHasKey('cache', $calls);
    }

    public function testMissingRawParametersKeyIsAResultError(): void
    {
        $activity = $this->createActivity(['de']);

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, []);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertNotSame('', $result->error()->getMessage());
    }

    /** Rejected by grind() itself (non-array 'translations', non-string key) as InvalidInputException. */
    public static function invalidRawParametersRejectedByGrindWithStringErrorProvider(): array
    {
        return [
            'non-array translations' => [['module' => 'common', 'identifier' => 'foo', 'translations' => 'nope']],
            'translations with non-string key' => [
                ['module' => 'common', 'identifier' => 'foo', 'translations' => [0 => 'Hallo']],
            ],
        ];
    }

    #[DataProvider('invalidRawParametersRejectedByGrindWithStringErrorProvider')]
    public function testMaybePerformAsRejectsStructurallyInvalidTranslationsAtGrindLevelWithoutCheckingPermission(
        array $raw_parameters
    ): void {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $activity = $this->createActivity(['de'], rbac: $rbac);

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, $raw_parameters);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertNotSame('', $result->error()->getMessage());
    }

    public static function invalidRawParametersProvider(): array
    {
        return [
            // valid 'de' lets grind() succeed, so the blank module/identifier is caught by toNonEmptyString()
            'blank module' => [['module' => '   ', 'identifier' => 'foo', 'translations' => ['de' => 'Hallo']]],
            'blank identifier' => [['module' => 'common', 'identifier' => '   ', 'translations' => ['de' => 'Hallo']]],
            // grind() converts the UI framework's blank \InvalidArgumentException into an InvalidInputException
            'translations with non-string value' => [
                ['module' => 'common', 'identifier' => 'foo', 'translations' => ['de' => 42]],
            ],
        ];
    }

    #[DataProvider('invalidRawParametersProvider')]
    public function testMaybePerformAsRejectsInvalidRawParametersAsResultError(array $raw_parameters): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $activity = $this->createActivity(['de'], rbac: $rbac);

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, $raw_parameters);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertSame(400, $result->error()->getCode());
    }

    public function testNormalizeParametersTrimsModuleAndIdentifierButKeepsTranslationsAsGiven(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $calls = [];
        $activity = $this->createActivity(
            ['de'],
            rbac: $rbac,
            replace_lang_entry: $this->spyReplaceLangEntry($calls)
        );

        $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [
            'module' => '  common  ',
            'identifier' => '  new_topic  ',
            'translations' => ['de' => 'Hallo'],
        ]);

        $this->assertSame('common', $calls['replace'][0][0]);
        $this->assertSame('new_topic', $calls['replace'][0][1]);
    }

    /**
     * A PHP variance fatal is not catchable, hence a separate process: a regression fails this test instead of
     * crashing the suite.
     */
    #[RunInSeparateProcess]
    public function testSubclassOverridingGetInputDescriptionWithThePlainInterfaceSignatureDoesNotTriggerAFatalError(): void
    {
        // txt() must be stubbed (called per translation field); createActivity() can't be reused because of the inline subclass
        $language = $this->createStub(Language::class);
        $language->method('txt')->willReturnCallback(static fn(string $k): string => $k);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->method('getInstalledLanguages')->willReturn(['de']);

        $activity = new class (
            $language,
            $this->rbacOf($this->createMock(\ilRbacSystem::class)),
            $this->languageFolder(),
            $repository,
        ) extends AddLanguageEntry {
            public function getInputDescription(FieldFactory $f): FormInput
            {
                return parent::getInputDescription($f);
            }
        };

        $this->assertInstanceOf(
            FormInput::class,
            $activity->getInputDescription($this->createRealFieldsUiFactory()->input()->field())
        );
    }

    public function testInputDescriptionBuildsModuleIdentifierAndPerLanguageTranslationFields(): void
    {
        $module_text = $this->createStub(Text::class);
        $module_text->method('withRequired')->willReturnCallback(
            function (bool $required) use ($module_text): Text {
                $this->assertTrue($required);
                return $module_text;
            }
        );
        $module_text->method('withDedicatedName')->willReturnCallback(
            function (string $name) use ($module_text): Text {
                $this->assertSame('module', $name);
                return $module_text;
            }
        );

        $identifier_text = $this->createStub(Text::class);
        $identifier_text->method('withRequired')->willReturnCallback(
            function (bool $required) use ($identifier_text): Text {
                $this->assertTrue($required);
                return $identifier_text;
            }
        );
        $identifier_text->method('withDedicatedName')->willReturnCallback(
            function (string $name) use ($identifier_text): Text {
                $this->assertSame('identifier', $name);
                return $identifier_text;
            }
        );

        $de_text = $this->createStub(Text::class);
        $de_required = null;
        $de_text->method('withRequired')->willReturnCallback(function (bool $required) use ($de_text, &$de_required) {
            $de_required = $required;
            return $de_text;
        });
        $de_text->method('withDedicatedName')->willReturnCallback(
            function (string $name) use ($de_text): Text {
                $this->assertSame('de', $name);
                return $de_text;
            }
        );

        $fr_text = $this->createStub(Text::class);
        $fr_required = null;
        $fr_text->method('withRequired')->willReturnCallback(function (bool $required) use ($fr_text, &$fr_required) {
            $fr_required = $required;
            return $fr_text;
        });
        $fr_text->method('withDedicatedName')->willReturnCallback(
            function (string $name) use ($fr_text): Text {
                $this->assertSame('fr', $name);
                return $fr_text;
            }
        );

        $text_calls = [];
        $field = $this->createStub(\ILIAS\UI\Component\Input\Field\Factory::class);
        $field->method('text')->willReturnCallback(
            function (string $label, ?string $byline = null) use (&$text_calls, $module_text, $identifier_text, $de_text, $fr_text) {
                $text_calls[] = $label;
                return match ($label) {
                    'Module' => $module_text,
                    'Identifier' => $identifier_text,
                    'meta_l_de' => $de_text,
                    'meta_l_fr' => $fr_text,
                    default => throw new \LogicException("unexpected text() label: $label"),
                };
            }
        );

        $translations_group = $this->createStub(\ILIAS\UI\Component\Input\Field\Group::class);
        $translations_group->method('withDedicatedName')->willReturnCallback(
            function (string $name) use ($translations_group): \ILIAS\UI\Component\Input\Field\Group {
                $this->assertSame('translations', $name);
                return $translations_group;
            }
        );

        $outer_section = $this->createMock(\ILIAS\UI\Component\Input\Field\Section::class);

        $group_calls = [];
        $field->method('group')->willReturnCallback(
            function (array $fields, ?string $label = null, ?string $byline = null) use (
                &$group_calls,
                $de_text,
                $fr_text,
                $translations_group
            ) {
                $group_calls[] = $fields;
                if ($fields === ['de' => $de_text, 'fr' => $fr_text]) {
                    return $translations_group;
                }
                throw new \LogicException('unexpected group() call');
            }
        );

        $section_calls = [];
        $field->method('section')->willReturnCallback(
            function (array $fields, string $label) use (&$section_calls, $module_text, $identifier_text, $translations_group, $outer_section) {
                $section_calls[] = [$fields, $label];
                if ($fields === ['module' => $module_text, 'identifier' => $identifier_text, 'translations' => $translations_group]) {
                    return $outer_section;
                }
                throw new \LogicException('unexpected section() call');
            }
        );

        $language = $this->createMock(Language::class);
        $language->method('txt')->willReturnCallback(static fn(string $key): string => $key);

        $activity = $this->createActivity(
            ['de', 'fr'],
            language: $language
        );

        $this->assertSame($outer_section, $activity->getInputDescription($field));
        $this->assertSame('Add a language entry', $section_calls[0][1]);
        $this->assertSame(['Module', 'Identifier', 'meta_l_de', 'meta_l_fr'], $text_calls);
        // only 'de' is required among translations
        $this->assertTrue($de_required);
        $this->assertFalse($fr_required);
    }

    public function testOutputDescriptionDeclaresTheExpectedFiveFields(): void
    {
        $activity = $this->createActivity(['de']);

        $description = $activity->getOutputDescription(new DescriptionFactory());

        $field_names = [];
        foreach ($description->getFields() as $field) {
            $field_names[] = $field->getName();
        }

        $this->assertSame(
            ['module', 'identifier', 'added_language_keys', 'skipped_empty_language_keys', 'overlay_write_failed_language_keys'],
            $field_names
        );
    }
}
