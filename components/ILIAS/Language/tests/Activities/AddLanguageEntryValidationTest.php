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

use ILIAS\Language\Activities\InvalidInputException;
use ILIAS\Language\Language;
use PHPUnit\Framework\Attributes\DataProvider;

class AddLanguageEntryValidationTest extends \ilLanguageBaseTestCase
{
    use RealFieldsUiFactory;
    use AddLanguageEntryTestFixture;

    public function testDeAndEnBothInstalledOneBlankRejectsWholeRequestAndWritesNothingForAnyLanguage(): void
    {
        $calls = [];
        $activity = $this->createActivity(
            ['de', 'en'],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        try {
            $activity->perform([
                'module' => 'common',
                'identifier' => 'new_topic',
                'translations' => [
                    'de' => 'Hallo',
                ],
                'usr_id' => 6,
            ]);
            $this->fail('Expected an InvalidInputException to be thrown.');
        } catch (InvalidInputException $e) {
            $this->assertArrayNotHasKey('replace', $calls);
            $this->assertArrayNotHasKey('cache', $calls);
        }
    }

    /**
     * Disallowed markup (TranslationMarkupPolicy) in a single language rejects the whole request before any write.
     */
    public function testDisallowedMarkupInOneLanguageRejectsTheWholeRequestAndWritesNothingForAnyLanguage(): void
    {
        $calls = [];
        $activity = $this->createActivity(
            ['de', 'en', 'fr'],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        try {
            $activity->perform([
                'module' => 'common',
                'identifier' => 'new_topic',
                'translations' => [
                    'de' => 'Hallo',
                    'en' => 'Hello',
                    'fr' => '<script>alert(1)</script>',
                ],
                'usr_id' => 6,
            ]);
            $this->fail('Expected an InvalidInputException to be thrown.');
        } catch (InvalidInputException $e) {
            $this->assertStringContainsString('fr', $e->getMessage());
            $this->assertStringNotContainsString('alert(1)', $e->getMessage());
            $this->assertStringNotContainsString('script', $e->getMessage());
            $this->assertArrayNotHasKey('replace', $calls);
            $this->assertArrayNotHasKey('cache', $calls);
        }
    }

    /**
     * A form key missing from the shipped message (here "item [2]", reported by the provider) rejects the request
     * before any write, naming the language but not the value.
     */
    public function testAFormKeyWithNoSuchFormInAShippedMessageRejectsTheRequestBeforeAnyWrite(): void
    {
        $calls = [];
        $asked = [];
        $activity = $this->createActivity(
            ['de', 'fr'],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls),
            languages_without_plural_form: static function (string $module, string $identifier, array $lang_keys) use (&$asked): array {
                $asked[] = [$module, $identifier, $lang_keys];
                return ['de'];
            }
        );

        try {
            $activity->perform([
                'module' => 'poll',
                'identifier' => 'item [2]',
                'translations' => ['de' => 'Ein geheimer Wert', 'fr' => ''],
                'usr_id' => 6,
            ]);
            $this->fail('Expected an InvalidInputException to be thrown.');
        } catch (InvalidInputException $e) {
            $this->assertStringContainsString('de', $e->getMessage());
            $this->assertStringNotContainsString('Ein geheimer Wert', $e->getMessage());
            // only the languages with a value are asked
            $this->assertSame([['poll', 'item [2]', ['de']]], $asked);
            $this->assertArrayNotHasKey('replace', $calls);
            $this->assertArrayNotHasKey('cache', $calls);
        }
    }

    public function testAnIdentifierExistingInAnotherModuleRejectsTheRequestBeforeAnyWrite(): void
    {
        $calls = [];
        $asked = [];
        $activity = $this->createActivity(
            ['de', 'en'],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls),
            duplicates_of: static function (string $module, string $identifier) use (&$asked): array {
                $asked[] = [$module, $identifier];
                return ['mme: component_not_active'];
            }
        );

        try {
            $activity->perform([
                'module' => 'common',
                'identifier' => 'component_not_active',
                'translations' => ['de' => 'Inaktiv', 'en' => 'Inactive'],
            ]);
            $this->fail('Expected an InvalidInputException to be thrown.');
        } catch (InvalidInputException $e) {
            $this->assertStringContainsString('mme: component_not_active', $e->getMessage());
            $this->assertSame(400, $e->getCode());
            $this->assertSame([['common', 'component_not_active']], $asked);
            $this->assertArrayNotHasKey('replace', $calls);
            $this->assertArrayNotHasKey('cache', $calls);
        }
    }

    // same de/en rule as the perform() test above, via maybePerformAs()
    public function testMaybePerformAsRejectsWholeRequestAndWritesNothingWhenEnIsBlankWhileDeIsValid(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $calls = [];
        $activity = $this->createActivity(
            ['de', 'en'],
            rbac: $rbac,
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => [
                'de' => 'Hallo',
                'en' => '   ', // whitespace-only counts as blank too
            ],
        ]);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertSame(400, $result->error()->getCode());
        $this->assertArrayNotHasKey('replace', $calls);
        $this->assertArrayNotHasKey('cache', $calls);
    }

    public function testPerformRejectsWholeRequestAndWritesNothingWhenDeIsBlankWhileEnIsValid(): void
    {
        $calls = [];
        $activity = $this->createActivity(
            ['de', 'en'],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        $this->expectException(InvalidInputException::class);
        try {
            $activity->perform([
                'module' => 'common',
                'identifier' => 'new_topic',
                'translations' => ['en' => 'Hello'],
                'usr_id' => 6,
            ]);
        } finally {
            $this->assertArrayNotHasKey('replace', $calls);
            $this->assertArrayNotHasKey('cache', $calls);
        }
    }

    public function testDeAndEnNotInstalledAtAllImposesNoMandatoryRequirement(): void
    {
        $calls = [];
        $activity = $this->createActivity(
            ['fr'],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        $result = $activity->perform([
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => [],
            'usr_id' => 6,
        ]);

        $this->assertSame([], $result['added_language_keys']);
        $this->assertSame(['fr'], $result['skipped_empty_language_keys']);
        $this->assertArrayNotHasKey('replace', $calls);
    }

    public static function invalidPerformParametersProvider(): array
    {
        return [
            'missing module' => [['identifier' => 'foo', 'translations' => [], 'usr_id' => 6]],
            'empty string module' => [['module' => '', 'identifier' => 'foo', 'translations' => [], 'usr_id' => 6]],
            'non-string module' => [['module' => 42, 'identifier' => 'foo', 'translations' => [], 'usr_id' => 6]],
            'missing identifier' => [['module' => 'common', 'translations' => [], 'usr_id' => 6]],
            'empty string identifier' => [['module' => 'common', 'identifier' => '', 'translations' => [], 'usr_id' => 6]],
            'non-string identifier' => [['module' => 'common', 'identifier' => [], 'translations' => [], 'usr_id' => 6]],
            'missing translations' => [['module' => 'common', 'identifier' => 'foo', 'usr_id' => 6]],
            'non-array translations' => [['module' => 'common', 'identifier' => 'foo', 'translations' => 'nope', 'usr_id' => 6]],
            // a missing/null usr_id is allowed; only a non-null usr_id of the wrong type is invalid
            'non-int usr_id' => [['module' => 'common', 'identifier' => 'foo', 'translations' => [], 'usr_id' => '6']],
        ];
    }

    #[DataProvider('invalidPerformParametersProvider')]
    public function testPerformRejectsInvalidOrIncompleteParameters(array $parameters): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity(['de'])->perform($parameters);
    }

    public function testPerformRejectsNonArrayParameters(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity(['de'])->perform('not-an-array');
    }

    /**
     * The fake repository returns different installed-language lists on the 1st (getInputDescription()) and
     * 2nd (perform()) call.
     */
    public function testOptionalLanguageInstalledBetweenGetInputDescriptionAndPerformIsSkippedNotRejected(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $calls = 0;
        $repository = new FakeInstalledLanguageRepository(static function () use (&$calls): array {
            $calls++;
            // 1st call: 'fr' not installed yet; 2nd call: installed
            return $calls === 1 ? ['de', 'en'] : ['de', 'en', 'fr'];
        });

        $activity = $this->createActivity(
            [],
            rbac: $rbac,
            installed_language_repository: $repository
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo', 'en' => 'Hello'],
        ]);

        $this->assertFalse($result->isError());
        $value = $result->value();
        $this->assertSame(['de', 'en'], $value['added_language_keys']);
        $this->assertSame(['fr'], $value['skipped_empty_language_keys']);
        // two independent snapshots, not a cached one
        $this->assertSame(2, $calls);
    }

    // fail-closed: "en" becomes mandatory after the form was built without it; the request must be rejected
    public function testEnInstalledBetweenGetInputDescriptionAndPerformRejectsTheWholeRequestRatherThanSkippingEn(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $calls = 0;
        $repository = new FakeInstalledLanguageRepository(static function () use (&$calls): array {
            $calls++;
            // 1st call: only "de" installed; 2nd call: "en" installed
            return $calls === 1 ? ['de'] : ['de', 'en'];
        });

        $calls_to_write_closures = [];
        $activity = $this->createActivity(
            [],
            rbac: $rbac,
            installed_language_repository: $repository,
            replace_lang_entry: $this->spyReplaceLangEntry($calls_to_write_closures),
            update_module_cache: $this->spyUpdateModuleCache($calls_to_write_closures)
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo'],
        ]);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(InvalidInputException::class, $error);
        $this->assertSame('A value is required for: en.', $error->getMessage());
        $this->assertArrayNotHasKey('replace', $calls_to_write_closures);
        $this->assertArrayNotHasKey('cache', $calls_to_write_closures);
        // two independent snapshots, not a cached one
        $this->assertSame(2, $calls);
    }

    /**
     * An optional language uninstalled between the two calls has its submitted value silently dropped.
     */
    public function testOptionalLanguageUninstalledBetweenGetInputDescriptionAndPerformIsSilentlyDroppedNotWritten(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $calls = 0;
        $repository = new FakeInstalledLanguageRepository(static function () use (&$calls): array {
            $calls++;
            // 1st call: "fr" installed; 2nd call: uninstalled
            return $calls === 1 ? ['de', 'en', 'fr'] : ['de', 'en'];
        });

        $calls_to_write_closures = [];
        $activity = $this->createActivity(
            [],
            rbac: $rbac,
            installed_language_repository: $repository,
            replace_lang_entry: $this->spyReplaceLangEntry($calls_to_write_closures),
            update_module_cache: $this->spyUpdateModuleCache($calls_to_write_closures)
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo', 'en' => 'Hello', 'fr' => 'Bonjour'],
        ]);

        $this->assertFalse($result->isError());
        $value = $result->value();
        $this->assertSame(['de', 'en'], $value['added_language_keys']);
        $this->assertSame([], $value['skipped_empty_language_keys']);
        $this->assertNotContains('fr', $value['added_language_keys']);
        $this->assertNotContains('fr', $value['skipped_empty_language_keys']);
        $this->assertArrayHasKey('replace', $calls_to_write_closures);
        $written_lang_keys = array_column($calls_to_write_closures['replace'], 2);
        $this->assertSame(['de', 'en'], $written_lang_keys);
        $this->assertNotContains('fr', $written_lang_keys);
        // two independent snapshots, not a cached one
        $this->assertSame(2, $calls);
    }
}
