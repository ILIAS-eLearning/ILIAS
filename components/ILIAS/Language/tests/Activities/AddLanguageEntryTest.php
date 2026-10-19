<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Tests\Activities\ActivityContractTestCase;
use ILIAS\Language\Activities\AddLanguageEntry;
use ILIAS\Language\Activities\InvalidInputException;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Text;
use ILIAS\Data\Description\Factory as DescriptionFactory;
use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * AddLanguageEntry reads installed languages, writes entries and resolves the acting user's login (audit trail)
 * through the RepositoryProvider, mocked here and answering from the closures of createActivity(). No test touches
 * a real database or lang file.
 */
class AddLanguageEntryTest extends ActivityContractTestCase
{
    protected function createDefaultActivity(): AddLanguageEntry
    {
        return $this->createActivity([]);
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

        $activity = $this->createActivity([], rbac: $rbac, language_folder_ref_id: 42);

        $this->assertTrue($activity->isAllowedToPerform(6, ['module' => 'common', 'identifier' => 'foo']));
    }

    public function testIsAllowedToPerformReturnsFalseWhenRbacDenies(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(false);

        $activity = $this->createActivity([], rbac: $rbac);

        $this->assertFalse($activity->isAllowedToPerform(6, ['module' => 'common', 'identifier' => 'foo']));
    }

    public function testIsAllowedToPerformNeverTouchesRepositoryOrWriteClosures(): void
    {
        $repository = new FakeInstalledLanguageRepository(static function (): array {
            throw new \LogicException('getInstalledLanguages() must never be called by isAllowedToPerform()');
        });

        $rbac = $this->createMock(\ilRbacSystem::class);
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

    // -----------------------------------------------------------------
    // perform()
    // -----------------------------------------------------------------

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
                // 'fr' left out entirely - must be treated like a blank value.
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

        // Never touched for the skipped language.
        foreach ($calls['replace'] as $call) {
            $this->assertNotSame('fr', $call[2]);
        }
        foreach ($calls['cache'] as $call) {
            $this->assertNotSame('fr', $call[0]);
        }
    }

    /**
     * Regression test for the deliberate "not transactional" behaviour noted above perform()'s
     * write loop: if replace_lang_entry() throws partway through (here: the 3rd of 3 languages),
     * languages already written must remain written, and the exception must propagate rather than
     * roll back.
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

            // "de" and "en" - written before the failing "fr" - remain
            // written: perform() gives no atomicity guarantee across the
            // write loop (see perform()'s own "Not transactional..."
            // comment above the loop).
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

            // Exactly the 3 attempted calls - "fr" itself was attempted
            // (and recorded before throwing) but never reached
            // update_module_cache().
            $this->assertCount(3, $calls['replace']);
            $this->assertCount(2, $calls['cache']);
        }
    }

    // -----------------------------------------------------------------
    // "de"/"en" mandatory, all-or-nothing (see getDescription()'s markdown)
    // -----------------------------------------------------------------

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
                    // 'en' left blank - must reject the whole request.
                ],
                'usr_id' => 6,
            ]);
            $this->fail('Expected an InvalidInputException to be thrown.');
        } catch (InvalidInputException $e) {
            // Nothing written for either language - not even the valid "de".
            $this->assertArrayNotHasKey('replace', $calls);
            $this->assertArrayNotHasKey('cache', $calls);
        }
    }

    // Same de/en rule as the perform() test above, exercised via maybePerformAs() with a real UI
    // Factory.
    public function testMaybePerformAsRejectsWholeRequestAndWritesNothingWhenEnIsBlankWhileDeIsValid(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
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

    public function testWhitespaceOnlyValueIsTreatedAsEmptyAndSkipped(): void
    {
        // 'fr' avoids the separate 'de'/'en' mandatory-value rule tested elsewhere.
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
     * A non-string translation value (e.g. from a direct perform() caller bypassing
     * normalizeParameters()) must be treated as empty, not coerced or crash.
     */
    public function testNonStringTranslationValueIsTreatedAsEmptyAndSkippedRatherThanCoerced(): void
    {
        // 'fr' avoids the separate 'de'/'en' mandatory-value rule tested elsewhere.
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

    // A missing usr_id is explicitly allowed (see perform()'s own docblock): a generic caller
    // following the plain Activity contract never supplies one.
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
            // 'usr_id' deliberately not present at all.
        ]);

        $this->assertSame(['de', 'en'], $result['added_language_keys']);
        $this->assertSame([], $login_calls, 'user_login() must never be called when usr_id is absent.');
        $this->assertSame('', $calls['replace'][0][5]);
        $this->assertSame('', $calls['replace'][1][5]);
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
            // A missing/null usr_id is explicitly ALLOWED (see
            // testPerformWithoutUsrIdKeySucceedsWithAnAuthorlessAuditEntryAndNeverCallsUserLoginClosure()
            // and testPerformSucceedsWhenUsrIdIsMissingOrNullGivenValidTranslations() below) - it must
            // NOT appear here as an "invalid" case. Only a non-null usr_id of the wrong TYPE is invalid.
            'non-int usr_id' => [['module' => 'common', 'identifier' => 'foo', 'translations' => [], 'usr_id' => '6']],
        ];
    }

    #[DataProvider('invalidPerformParametersProvider')]
    public function testPerformRejectsInvalidOrIncompleteParameters(array $parameters): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity(['de'])->perform($parameters);
    }

    /**
     * A missing or null usr_id, given otherwise-valid translations, must succeed - not throw (see
     * perform()'s own docblock: only a non-null usr_id of the wrong type is rejected).
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

    public function testPerformRejectsNonArrayParameters(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity(['de'])->perform('not-an-array');
    }

    // -----------------------------------------------------------------
    // Snapshot drift between getInputDescription() and perform() - see
    // getInputDescription()'s own docblock.
    // -----------------------------------------------------------------

    /**
     * The fake repository below returns different installed-language lists on its 1st
     * (getInputDescription()) and 2nd (perform()) call, modeling this snapshot drift within one
     * maybePerformAs() call.
     */
    public function testOptionalLanguageInstalledBetweenGetInputDescriptionAndPerformIsSkippedNotRejected(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $calls = 0;
        $repository = new FakeInstalledLanguageRepository(static function () use (&$calls): array {
            $calls++;
            // 1st call: getInputDescription() - 'fr' not installed yet, so no
            // field/value for it exists in the form.
            // 2nd call: perform() - 'fr' has since been installed.
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
            // No 'fr' key at all - getInputDescription() (1st call) never
            // asked for one, since it did not yet know 'fr' was installed.
            'translations' => ['de' => 'Hallo', 'en' => 'Hello'],
        ]);

        $this->assertFalse($result->isError());
        $value = $result->value();
        $this->assertSame(['de', 'en'], $value['added_language_keys']);
        $this->assertSame(['fr'], $value['skipped_empty_language_keys']);
        // Two independent snapshots, not a cached one - see getInputDescription()'s own docblock.
        $this->assertSame(2, $calls);
    }

    // Fail-closed branch: "en" becomes mandatory only after getInputDescription() already built
    // the form without asking for it - the whole request must be rejected, not partially skipped.
    public function testEnInstalledBetweenGetInputDescriptionAndPerformRejectsTheWholeRequestRatherThanSkippingEn(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $calls = 0;
        $repository = new FakeInstalledLanguageRepository(static function () use (&$calls): array {
            $calls++;
            // 1st call: getInputDescription() - only "de" installed, "en" not
            // yet, so no field/value for "en" exists in the form.
            // 2nd call: perform() - "en" has since been installed.
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
            // No 'en' key - getInputDescription() (1st call) never asked for
            // one, since "en" was not yet installed when the form was built.
            'translations' => ['de' => 'Hallo'],
        ]);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(InvalidInputException::class, $error);
        // Only "en" is reported missing - "de" has a valid value, so the mandatory-value check
        // doesn't flag it too.
        $this->assertSame('A value is required for: en.', $error->getMessage());
        // Nothing written for "de" either - fail-closed, not fail-partial.
        $this->assertArrayNotHasKey('replace', $calls_to_write_closures);
        $this->assertArrayNotHasKey('cache', $calls_to_write_closures);
        // Two independent snapshots, not a cached one - see getInputDescription()'s own docblock.
        $this->assertSame(2, $calls);
    }

    /**
     * The opposite direction from the two tests above: an OPTIONAL language uninstalled between
     * the two calls has a submitted value that is neither written nor rejected - it is silently
     * dropped, unlike installing "de"/"en" (fail-closed) or installing an optional language
     * (skipped normally). "fr" isolates this from the separate "de"/"en" mandatory rule tested
     * elsewhere.
     */
    public function testOptionalLanguageUninstalledBetweenGetInputDescriptionAndPerformIsSilentlyDroppedNotWritten(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $calls = 0;
        $repository = new FakeInstalledLanguageRepository(static function () use (&$calls): array {
            $calls++;
            // 1st call: getInputDescription() - "fr" still installed, so a
            // field/value for it exists in the form.
            // 2nd call: perform() - "fr" has since been uninstalled.
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
            // A valid value IS given for "fr" - getInputDescription() (1st
            // call) still asked for it, since "fr" was installed at the time.
            'translations' => ['de' => 'Hallo', 'en' => 'Hello', 'fr' => 'Bonjour'],
        ]);

        $this->assertFalse($result->isError());
        $value = $result->value();
        $this->assertSame(['de', 'en'], $value['added_language_keys']);
        $this->assertSame([], $value['skipped_empty_language_keys']);
        // "fr"'s given value is silently dropped: it appears in neither list.
        $this->assertNotContains('fr', $value['added_language_keys']);
        $this->assertNotContains('fr', $value['skipped_empty_language_keys']);
        // The remaining, still-installed languages are written normally.
        $this->assertArrayHasKey('replace', $calls_to_write_closures);
        $written_lang_keys = array_column($calls_to_write_closures['replace'], 2);
        $this->assertSame(['de', 'en'], $written_lang_keys);
        $this->assertNotContains('fr', $written_lang_keys);
        // Two independent snapshots, not a cached one - see getInputDescription()'s own docblock.
        $this->assertSame(2, $calls);
    }

    // -----------------------------------------------------------------
    // maybePerformAs()
    // -----------------------------------------------------------------

    public function testPermissionDeniedBeforePerformNeverCallsWriteClosuresAndReturnsA403ResultError(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())
            ->method('checkAccessOfUser')
            ->with(6, 'write', $this->anything())
            ->willReturn(false);

        // getInputDescription() calls txt('meta_l_de') before isAllowedToPerform(), so the mock must allow it too
        $language = $this->createMock(Language::class);
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
        $rbac = $this->createMock(\ilRbacSystem::class);
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
        $rbac = $this->createMock(\ilRbacSystem::class);
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

    /**
     * Both rejected by grind() itself, before normalizeParameters()/isAllowedToPerform() run, but
     * for different reasons: a non-array 'translations' fails collectRawValues()'s type guard on
     * the nested group; a non-string key can never match any of the group's string-keyed fields.
     * Either way: an InvalidInputException, not an InvalidArgumentException.
     */
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
        $rbac = $this->createMock(\ilRbacSystem::class);
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
     * An empty translations array is a legal input (e.g. a caller merely
     * reserving the module/identifier pair without giving any value yet) -
     * every installed language is then reported skipped, not rejected.
     */
    public function testEmptyTranslationsArrayIsAcceptedAndSkipsEveryInstalledLanguage(): void
    {
        // 'fr'/'it' avoid the separate 'de'/'en' mandatory-value rule tested elsewhere.
        $rbac = $this->createMock(\ilRbacSystem::class);
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

    // -----------------------------------------------------------------
    // getInputDescription()
    // -----------------------------------------------------------------

    /**
     * End-to-end companion to ActivityContractTestCase's reflection-based signature pin: actually
     * defines a subclass overriding getInputDescription() with the plain interface signature,
     * reproducing the PHP fatal error the old, buggy signature triggered (verified under PHP
     * 8.5.4). A PHP variance fatal is a compile-time error, not a catchable \Throwable, hence
     * #[RunInSeparateProcess] - so a regression fails this one test instead of crashing the suite.
     */
    #[RunInSeparateProcess]
    public function testSubclassOverridingGetInputDescriptionWithThePlainInterfaceSignatureDoesNotTriggerAFatalError(): void
    {
        // txt() must be stubbed (called per translation field); createActivity() can't be reused because of the inline subclass
        $language = $this->createMock(Language::class);
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
        $module_text = $this->createMock(Text::class);
        $module_text->method('withRequired')->with(true)->willReturnSelf();
        $module_text->method('withDedicatedName')->with('module')->willReturnSelf();

        $identifier_text = $this->createMock(Text::class);
        $identifier_text->method('withRequired')->with(true)->willReturnSelf();
        $identifier_text->method('withDedicatedName')->with('identifier')->willReturnSelf();

        $de_text = $this->createMock(Text::class);
        $de_required = null;
        $de_text->method('withRequired')->willReturnCallback(function (bool $required) use ($de_text, &$de_required) {
            $de_required = $required;
            return $de_text;
        });
        $de_text->method('withDedicatedName')->with('de')->willReturnSelf();

        $fr_text = $this->createMock(Text::class);
        $fr_required = null;
        $fr_text->method('withRequired')->willReturnCallback(function (bool $required) use ($fr_text, &$fr_required) {
            $fr_required = $required;
            return $fr_text;
        });
        $fr_text->method('withDedicatedName')->with('fr')->willReturnSelf();

        $text_calls = [];
        $field = $this->createMock(\ILIAS\UI\Component\Input\Field\Factory::class);
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

        $translations_group = $this->createMock(\ILIAS\UI\Component\Input\Field\Group::class);
        $translations_group->method('withDedicatedName')->with('translations')->willReturnSelf();

        $outer_section = $this->createMock(\ILIAS\UI\Component\Input\Field\Section::class);

        $group_calls = [];
        $field->method('group')->willReturnCallback(
            function (array $fields, ?string $label = null, ?string $byline = null) use (
                &$group_calls,
                $de_text,
                $fr_text,
                $module_text,
                $identifier_text,
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

    // -----------------------------------------------------------------
    // getOutputDescription()
    // -----------------------------------------------------------------

    public function testOutputDescriptionDeclaresTheExpectedFourFields(): void
    {
        $activity = $this->createActivity(['de']);

        $description = $activity->getOutputDescription(new DescriptionFactory());

        $field_names = [];
        foreach ($description->getFields() as $field) {
            $field_names[] = $field->getName();
        }

        $this->assertSame(
            ['module', 'identifier', 'added_language_keys', 'skipped_empty_language_keys'],
            $field_names
        );
    }

    // -----------------------------------------------------------------
    // Test helpers
    // -----------------------------------------------------------------

    /**
     * @param array<string, array> $calls
     */
    private function spyReplaceLangEntry(array &$calls): \Closure
    {
        return static function (
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
    }

    /**
     * @param array<string, array> $calls
     */
    private function spyUpdateModuleCache(array &$calls): \Closure
    {
        return static function (
            string $lang_key,
            string $module,
            string $identifier,
            string $value
        ) use (&$calls): void {
            $calls['cache'][] = [$lang_key, $module, $identifier, $value];
        };
    }

    /**
     * @param list<string> $installed_languages
     */
    private function createActivity(
        array $installed_languages,
        ?\ilRbacSystem $rbac = null,
        ?Language $language = null,
        ?InstalledLanguageRepository $installed_language_repository = null,
        int $language_folder_ref_id = 0,
        ?\Closure $replace_lang_entry = null,
        ?\Closure $update_module_cache = null,
        ?\Closure $user_login = null,
    ): AddLanguageEntry {
        // Safe no-op defaults (never the real \ilObjLanguage-backed closures, which need global
        // $DIC). user_login() runs unconditionally in perform(), so it always needs one too.
        // Tests that must prove a closure is never called pass their own throwing closure instead.
        $replace_lang_entry ??= static fn(
            string $module,
            string $identifier,
            string $lang_key,
            string $value,
            string $local_change,
            string $remarks
        ): bool => true;
        $update_module_cache ??= static function (
            string $lang_key,
            string $module,
            string $identifier,
            string $value
        ): void {
        };
        $user_login ??= static fn(int $usr_id): string => 'default-login';
        $installed_language_repository ??= new FakeInstalledLanguageRepository(static fn(): array => $installed_languages);

        $repository = $this->createMock(RepositoryProvider::class);
        $repository->method('getInstalledLanguages')->willReturnCallback(
            static fn(): array => $installed_language_repository->getInstalledLanguages()
        );
        $repository->method('replaceLangEntry')->willReturnCallback($replace_lang_entry);
        $repository->method('updateModuleCache')->willReturnCallback($update_module_cache);
        $repository->method('lookupLogin')->willReturnCallback($user_login);

        return new AddLanguageEntry(
            $language ?? $this->createMock(Language::class),
            $this->rbacOf($rbac ?? $this->createMock(\ilRbacSystem::class)),
            $this->languageFolder($language_folder_ref_id),
            $repository,
        );
    }
}

/**
 * Minimal in-memory stand-in for InstalledLanguageRepository:
 * InstalledLanguageDatabaseRepository (the real implementation) needs an
 * actual database connection, which unit tests must never construct. Only
 * getInstalledLanguages() is exercised by AddLanguageEntry; every other
 * method is unreachable from it and simply throws if a test ever calls it by
 * mistake.
 */
final class FakeInstalledLanguageRepository implements InstalledLanguageRepository
{
    public function __construct(
        private readonly \Closure $installed_languages,
    ) {
    }

    public function getInstalledLanguages(): array
    {
        return ($this->installed_languages)();
    }

    public function getInstalledLocalLanguages(): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function getAvailableLanguages(): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function getLocalChanges(string $lang_key, string $min_date = "", string $max_date = ""): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function getLanguageEntries(string $lang_key): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function getLocalLanguages(): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function getInstallableLanguages(): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function getInvalidLocalLanguageFiles(array $language_keys): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function checkLanguage(string $lang_key): bool
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }

    public function checkLocalLanguageFile(string $lang_key): bool
    {
        throw new \LogicException(__METHOD__ . ' is not used by AddLanguageEntry.');
    }
}
