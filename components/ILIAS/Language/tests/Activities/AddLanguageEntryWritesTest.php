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

use ILIAS\Language\Language;
use PHPUnit\Framework\Attributes\DataProvider;

class AddLanguageEntryWritesTest extends \ilLanguageBaseTestCase
{
    use RealFieldsUiFactory;
    use AddLanguageEntryTestFixture;

    public function testAddsEntryForEachInstalledLanguageWithANonEmptyValueAndSkipsEmptyOnes(): void
    {
        $calls = [];
        $replace_lang_entry = static function (
            string $module,
            string $identifier,
            string $lang_key,
            string $value,
            string $local_change,
            string $remarks
        ) use (&$calls): bool {
            $calls['replace'][] = [$module, $identifier, $lang_key, $value, $local_change, $remarks];
            return true;
        };
        $update_module_cache = static function (
            string $lang_key,
            string $module,
            string $identifier,
            string $value
        ) use (&$calls): void {
            $calls['cache'][] = [$lang_key, $module, $identifier, $value];
        };
        $user_login = static fn(int $usr_id): string => 'login-' . $usr_id;

        $activity = $this->createActivity(
            ['de', 'en', 'fr'],
            replace_lang_entry: $replace_lang_entry,
            update_module_cache: $update_module_cache,
            user_login: $user_login
        );

        $result = $activity->perform([
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => [
                'de' => 'Hallo',
                'en' => 'Hello',
            ],
            'usr_id' => 6,
        ]);

        $this->assertSame('common', $result['module']);
        $this->assertSame('new_topic', $result['identifier']);
        $this->assertSame(['de', 'en'], $result['added_language_keys']);
        $this->assertSame(['fr'], $result['skipped_empty_language_keys']);

        $this->assertCount(2, $calls['replace']);
        $this->assertSame(['common', 'new_topic', 'de', 'Hallo'], array_slice($calls['replace'][0], 0, 4));
        $this->assertSame('login-6', $calls['replace'][0][5]);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $calls['replace'][0][4]);
        $this->assertSame(['common', 'new_topic', 'en', 'Hello'], array_slice($calls['replace'][1], 0, 4));

        $this->assertCount(2, $calls['cache']);
        $this->assertSame(['de', 'common', 'new_topic', 'Hallo'], $calls['cache'][0]);
        $this->assertSame(['en', 'common', 'new_topic', 'Hello'], $calls['cache'][1]);

        foreach ($calls['replace'] as $call) {
            $this->assertNotSame('fr', $call[2]);
        }
        foreach ($calls['cache'] as $call) {
            $this->assertNotSame('fr', $call[0]);
        }
    }

    /**
     * update_module_cache() returns `false` when the PO/MO overlay of a PO-maintained module could not be written:
     * the language is listed in "overlay_write_failed_language_keys" but still counts as added.
     */
    public function testALanguageWhoseModuleCacheUpdateReportsAnUnwrittenOverlayIsListed(): void
    {
        $results = ['de' => false, 'en' => true, 'fr' => null];
        $update_module_cache = static fn(
            string $lang_key,
            string $module,
            string $identifier,
            string $value
        ): ?bool => $results[$lang_key];

        $result = $this->createActivity(['de', 'en', 'fr'], update_module_cache: $update_module_cache)->perform([
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo', 'en' => 'Hello', 'fr' => 'Bonjour'],
            'usr_id' => 6,
        ]);

        $this->assertSame(['de', 'en', 'fr'], $result['added_language_keys']);
        $this->assertSame(['de'], $result['overlay_write_failed_language_keys']);
    }

    /**
     * A language whose module content cannot be read is skipped before anything is written (not even lng_data) and
     * reported as unwritten instead of added; the other languages are written.
     */
    public function testALanguageWithUnreadableModuleContentIsReportedUnwrittenAndNeverWritten(): void
    {
        $calls = [];
        $activity = $this->createActivity(
            ['de', 'en', 'fr'],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls),
            has_unreadable_content: static fn(string $lang_key, string $module): bool => $lang_key === 'en'
        );

        $result = $activity->perform([
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo', 'en' => 'Hello', 'fr' => ''],
            'usr_id' => 6,
        ]);

        $this->assertSame(['de'], $result['added_language_keys']);
        $this->assertSame(['fr'], $result['skipped_empty_language_keys']);
        $this->assertSame(['en'], $result['overlay_write_failed_language_keys']);
        $this->assertSame(['de'], array_column($calls['replace'], 2));
        $this->assertSame(['de'], array_column($calls['cache'], 0));
    }

    /**
     * A skipped language never reaches update_module_cache(), so it is never listed as unwritten.
     */
    public function testASkippedLanguageIsNeverListedAsHavingAnUnwrittenOverlay(): void
    {
        $update_module_cache = static fn(
            string $lang_key,
            string $module,
            string $identifier,
            string $value
        ): bool => false;

        $result = $this->createActivity(['de', 'en', 'fr'], update_module_cache: $update_module_cache)->perform([
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo', 'en' => 'Hello'],
            'usr_id' => 6,
        ]);

        $this->assertSame(['fr'], $result['skipped_empty_language_keys']);
        $this->assertSame(['de', 'en'], $result['overlay_write_failed_language_keys']);
    }

    /**
     * Deliberately not transactional: if replace_lang_entry() throws on the 3rd of 3 languages, the earlier
     * writes stay and the exception propagates.
     */
    public function testAThrowingReplaceLangEntryPartwayThroughTheWriteLoopLeavesEarlierLanguagesWritten(): void
    {
        $calls = [];
        $replace_lang_entry = static function (
            string $module,
            string $identifier,
            string $lang_key,
            string $value,
            string $local_change,
            string $remarks
        ) use (&$calls): bool {
            $calls['replace'][] = [$module, $identifier, $lang_key, $value, $local_change, $remarks];
            if ($lang_key === 'fr') {
                throw new \RuntimeException('simulated database error while writing "fr"');
            }
            return true;
        };

        $activity = $this->createActivity(
            ['de', 'en', 'fr'],
            replace_lang_entry: $replace_lang_entry,
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        try {
            $activity->perform([
                'module' => 'common',
                'identifier' => 'new_topic',
                'translations' => [
                    'de' => 'Hallo',
                    'en' => 'Hello',
                    'fr' => 'Bonjour',
                ],
                'usr_id' => 6,
            ]);
            $this->fail('Expected the simulated \RuntimeException to propagate out of perform().');
        } catch (\RuntimeException $e) {
            $this->assertSame('simulated database error while writing "fr"', $e->getMessage());

            // "de" and "en", written before the failing "fr", remain written
            $this->assertSame(
                ['common', 'new_topic', 'de', 'Hallo', $calls['replace'][0][4], 'default-login'],
                $calls['replace'][0]
            );
            $this->assertSame(
                ['common', 'new_topic', 'en', 'Hello', $calls['replace'][1][4], 'default-login'],
                $calls['replace'][1]
            );
            $this->assertSame(['de', 'common', 'new_topic', 'Hallo'], $calls['cache'][0]);
            $this->assertSame(['en', 'common', 'new_topic', 'Hello'], $calls['cache'][1]);

            // "fr" was attempted but never reached update_module_cache()
            $this->assertCount(3, $calls['replace']);
            $this->assertCount(2, $calls['cache']);
        }
    }

    /**
     * Markup that TranslationMarkupPolicy allows (plain tag, link with allowed scheme) never blocks the request.
     */
    public function testAllowedMarkupInEveryLanguageIsWritten(): void
    {
        $calls = [];
        $activity = $this->createActivity(
            ['de', 'en'],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        $result = $activity->perform([
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => [
                'de' => '<b>Hallo</b>',
                'en' => '<a href="https://ilias.de">Hello</a>',
            ],
            'usr_id' => 6,
        ]);

        $this->assertSame(['de', 'en'], $result['added_language_keys']);
        $this->assertSame('<b>Hallo</b>', $calls['replace'][0][3]);
        $this->assertSame('<a href="https://ilias.de">Hello</a>', $calls['replace'][1][3]);
    }

    public function testWhitespaceOnlyValueIsTreatedAsEmptyAndSkipped(): void
    {
        // 'fr' avoids the separate 'de'/'en' mandatory-value rule
        $calls = [];
        $activity = $this->createActivity(
            ['fr'],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        $result = $activity->perform([
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['fr' => "  \t "],
            'usr_id' => 6,
        ]);

        $this->assertSame([], $result['added_language_keys']);
        $this->assertSame(['fr'], $result['skipped_empty_language_keys']);
        $this->assertArrayNotHasKey('replace', $calls);
        $this->assertArrayNotHasKey('cache', $calls);
    }

    public function testValueSurroundedByWhitespaceIsTrimmedBeforeBeingWritten(): void
    {
        $calls = [];
        $activity = $this->createActivity(
            ['de'],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        $result = $activity->perform([
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => "  Hallo Welt  "],
            'usr_id' => 6,
        ]);

        $this->assertSame(['de'], $result['added_language_keys']);
        $this->assertSame('Hallo Welt', $calls['replace'][0][3]);
        $this->assertSame('Hallo Welt', $calls['cache'][0][3]);
    }

    /**
     * A non-string value (direct perform() caller bypassing normalizeParameters()) counts as empty.
     */
    public function testNonStringTranslationValueIsTreatedAsEmptyAndSkippedRatherThanCoerced(): void
    {
        // 'fr' avoids the separate 'de'/'en' mandatory-value rule
        $calls = [];
        $activity = $this->createActivity(
            ['fr'],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        $result = $activity->perform([
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['fr' => 42],
            'usr_id' => 6,
        ]);

        $this->assertSame([], $result['added_language_keys']);
        $this->assertSame(['fr'], $result['skipped_empty_language_keys']);
        $this->assertArrayNotHasKey('replace', $calls);
    }

    public function testNoInstalledLanguagesResultsInEmptyBucketsAndNoWrites(): void
    {
        $calls = [];
        $activity = $this->createActivity(
            [],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls)
        );

        $result = $activity->perform([
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo'],
            'usr_id' => 6,
        ]);

        $this->assertSame([], $result['added_language_keys']);
        $this->assertSame([], $result['skipped_empty_language_keys']);
        $this->assertArrayNotHasKey('replace', $calls);
        $this->assertArrayNotHasKey('cache', $calls);
    }

    public function testUserLoginClosureIsCalledExactlyOnceWithTheGivenUsrIdRegardlessOfLanguageCount(): void
    {
        $login_calls = [];
        $user_login = static function (int $usr_id) use (&$login_calls): string {
            $login_calls[] = $usr_id;
            return 'resolved-login';
        };

        $activity = $this->createActivity(['de', 'en'], user_login: $user_login);

        $activity->perform([
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo', 'en' => 'Hello'],
            'usr_id' => 99,
        ]);

        $this->assertSame([99], $login_calls);
    }

    // a missing usr_id is allowed: a caller following the plain Activity contract never supplies one
    public function testPerformWithoutUsrIdKeySucceedsWithAnAuthorlessAuditEntryAndNeverCallsUserLoginClosure(): void
    {
        $login_calls = [];
        $user_login = static function (int $usr_id) use (&$login_calls): string {
            $login_calls[] = $usr_id;
            return 'must-not-be-used';
        };

        $calls = [];
        $activity = $this->createActivity(
            ['de', 'en'],
            replace_lang_entry: $this->spyReplaceLangEntry($calls),
            update_module_cache: $this->spyUpdateModuleCache($calls),
            user_login: $user_login
        );

        $result = $activity->perform([
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => ['de' => 'Hallo', 'en' => 'Hello'],
        ]);

        $this->assertSame(['de', 'en'], $result['added_language_keys']);
        $this->assertSame([], $login_calls, 'user_login() must never be called when usr_id is absent.');
        $this->assertSame('', $calls['replace'][0][5]);
        $this->assertSame('', $calls['replace'][1][5]);
    }

    /**
     * A missing or null usr_id must succeed; only a non-null usr_id of the wrong type is rejected.
     */
    public static function validUsrIdMissingOrNullWithValidTranslationsProvider(): array
    {
        return [
            'missing usr_id' => [
                ['module' => 'common', 'identifier' => 'foo', 'translations' => ['de' => 'Hallo', 'en' => 'Hello']],
            ],
            'null usr_id' => [
                [
                    'module' => 'common',
                    'identifier' => 'foo',
                    'translations' => ['de' => 'Hallo', 'en' => 'Hello'],
                    'usr_id' => null,
                ],
            ],
        ];
    }

    #[DataProvider('validUsrIdMissingOrNullWithValidTranslationsProvider')]
    public function testPerformSucceedsWhenUsrIdIsMissingOrNullGivenValidTranslations(array $parameters): void
    {
        $activity = $this->createActivity(['de', 'en']);

        $result = $activity->perform($parameters);

        $this->assertSame('common', $result['module']);
        $this->assertSame('foo', $result['identifier']);
        $this->assertSame(['de', 'en'], $result['added_language_keys']);
    }

    /**
     * An empty translations array is legal: every installed language is reported skipped, not rejected.
     */
    public function testEmptyTranslationsArrayIsAcceptedAndSkipsEveryInstalledLanguage(): void
    {
        // 'fr'/'it' avoid the separate 'de'/'en' mandatory-value rule
        $rbac = $this->createStub(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $activity = $this->createActivity(['fr', 'it'], rbac: $rbac);

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [
            'module' => 'common',
            'identifier' => 'new_topic',
            'translations' => [],
        ]);

        $this->assertFalse($result->isError());
        $this->assertSame([], $result->value()['added_language_keys']);
        $this->assertSame(['fr', 'it'], $result->value()['skipped_empty_language_keys']);
    }
}
