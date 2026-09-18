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
use ILIAS\Language\Activities\AmbiguousLanguageTitleException;
use ILIAS\Language\Activities\InvalidInputException;
use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\UI\Component\Input\Field\Text;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * UninstallLanguage has no ilSetupLanguage collaborator (unlike
 * InstallLanguage/UpdateLanguage) - instead it resolves language keys to
 * objects via two closures, $lng_objects and $obj_language_factory. The
 * object returned by $obj_language_factory must behave like \ilObjLanguage
 * (isSystemLanguage(), isUserLanguage(), isInstalled(), uninstall()), but
 * \ilObjLanguage itself cannot be constructed or meaningfully mocked in a
 * unit test (its constructor needs the global $DIC) - so tests use a small
 * fake object instead, injected through $obj_language_factory.
 */
class UninstallLanguageTest extends ActivityWithPerformResultContractTestCase
{
    protected function createDefaultActivity(): UninstallLanguage
    {
        [, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld(['de' => []]);

        return $this->createActivity($lng_objects, $obj_language_factory);
    }

    protected function validPerformParameters(): array
    {
        return ['language_keys' => 'de'];
    }

    // -----------------------------------------------------------------
    // maybePerformAs() turning a \Throwable from perform() into a
    // Result\Error (m5) - here, the new ambiguous-title \RuntimeException
    // from resolveObjIdsByLanguageKey() (see below) doubles as the concrete
    // \Throwable this class' perform() can actually raise.
    // -----------------------------------------------------------------

    public function testThrowableFromWithinPerformIsTurnedIntoAResultErrorByMaybePerformAs(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $lng_objects = static fn(): array => [
            ['obj_id' => 1, 'title' => 'de'],
            ['obj_id' => 2, 'title' => 'de'],
        ];
        $obj_language_factory = static function (int $id): never {
            throw new \LogicException('must never be called for an ambiguous title');
        };

        $result = $this->createActivity($lng_objects, $obj_language_factory, $rbac)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => 'de']);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(\RuntimeException::class, $result->error());
    }

    // -----------------------------------------------------------------
    // resolveObjIdsByLanguageKey() ambiguous-title guard (m1): two "lng"
    // objects sharing the same title must reject the whole request for
    // that title rather than silently resolving to whichever one happened
    // to be enumerated last.
    // -----------------------------------------------------------------

    public function testAmbiguousTitleRejectsTheRequestAndNeverCallsTheObjectFactoryForIt(): void
    {
        $lng_objects = static fn(): array => [
            ['obj_id' => 1, 'title' => 'de'],
            ['obj_id' => 2, 'title' => 'de'],
        ];
        $factory_calls = [];
        $obj_language_factory = static function (int $id) use (&$factory_calls): never {
            $factory_calls[] = $id;
            throw new \LogicException('must never be called for an ambiguous title');
        };

        $activity = $this->createActivity($lng_objects, $obj_language_factory);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/"de"/');

        try {
            $activity->perform(['language_keys' => 'de']);
        } finally {
            $this->assertSame([], $factory_calls);
        }
    }

    /**
     * Regression test: the AmbiguousLanguageTitleException message embeds
     * the ambiguous title RAW/unescaped - escaping HTML-significant
     * characters for safe display is deliberately no longer this domain
     * layer's job (see UninstallLanguage::perform()'s own docblock/comment
     * above the throw), only \ILIAS\Language\RendersActivityErrors::
     * activityErrorMessage()'s. If this message were pre-escaped here, it
     * would end up double-escaped once it reaches that single seam.
     *
     * NOTE (found while adding format validation coverage for
     * ParsesLanguageKeyList::toLanguageKeyList()): this test originally used
     * an HTML/XSS payload ('<script>alert(1)</script>&"quoted"') as BOTH the
     * ambiguous objects' title AND the requested 'language_keys' value -
     * $language_keys must equal an object's title verbatim for
     * resolveObjIdsByLanguageKey()'s ambiguity map to ever be consulted
     * (perform() intersects the requested, already-parsed $language_keys
     * against that map's keys). Since toLanguageKeyList() now rejects any
     * value that is not exactly two lowercase ASCII letters BEFORE that
     * intersection is even computed, such a value now throws
     * InvalidInputException immediately and never reaches
     * AmbiguousLanguageTitleException at all - this test would otherwise
     * fail with that InvalidInputException instead of the expected
     * AmbiguousLanguageTitleException. The dangerous-title scenario is
     * therefore no longer reachable through perform()'s public parameter
     * surface at all (a real, if incidental, hardening side effect of the
     * new format check) - only a title that happens to already be a
     * plausible two-letter language key can ever become "ambiguous" via a
     * real request. The title/key below is changed accordingly; the
     * "no HTML-escaping happens" assertions are kept for continuity, even
     * though a two-letter title trivially satisfies them.
     */
    public function testAmbiguousTitleExceptionMessageContainsTheRawUnescapedTitle(): void
    {
        $ambiguous_title = 'de';
        $lng_objects = static fn(): array => [
            ['obj_id' => 1, 'title' => $ambiguous_title],
            ['obj_id' => 2, 'title' => $ambiguous_title],
        ];
        $obj_language_factory = static function (int $id): never {
            throw new \LogicException('must never be called for an ambiguous title');
        };

        $activity = $this->createActivity($lng_objects, $obj_language_factory);

        try {
            $activity->perform(['language_keys' => $ambiguous_title]);
            $this->fail('Expected an AmbiguousLanguageTitleException to be thrown.');
        } catch (AmbiguousLanguageTitleException $e) {
            $this->assertStringContainsString($ambiguous_title, $e->getMessage());
            $this->assertStringNotContainsString('&lt;script&gt;', $e->getMessage());
            $this->assertStringNotContainsString('&quot;', $e->getMessage());
        }
    }

    /**
     * Via maybePerformAs(), the same ambiguous-title \RuntimeException must
     * surface as a Result\Error, not propagate as an uncaught exception -
     * and, again, the object factory must never be reached for it.
     */
    public function testAmbiguousTitleViaMaybePerformAsReturnsResultErrorAndNeverCallsTheObjectFactory(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $lng_objects = static fn(): array => [
            ['obj_id' => 1, 'title' => 'de'],
            ['obj_id' => 2, 'title' => 'de'],
        ];
        $factory_calls = [];
        $obj_language_factory = static function (int $id) use (&$factory_calls): never {
            $factory_calls[] = $id;
            throw new \LogicException('must never be called for an ambiguous title');
        };

        $result = $this->createActivity($lng_objects, $obj_language_factory, $rbac)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => 'de']);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(\RuntimeException::class, $result->error());
        $this->assertSame([], $factory_calls);
    }

    /**
     * An ambiguous title must not poison unrelated, unambiguous titles in
     * the same $lng_objects() world/request - a genuinely unique language
     * key coexisting with an ambiguous one must still resolve and uninstall
     * normally.
     */
    public function testUnambiguousLanguageKeyStillWorksAlongsideAnAmbiguousOneInTheSameLngObjectsList(): void
    {
        $lng_objects = static fn(): array => [
            ['obj_id' => 1, 'title' => 'de'],
            ['obj_id' => 2, 'title' => 'de'],
            ['obj_id' => 3, 'title' => 'fr'],
        ];
        $fr = new FakeLanguageObject(is_system_language: false, is_user_language: false, is_installed: true);
        $obj_language_factory = static function (int $id) use ($fr): FakeLanguageObject {
            if ($id === 3) {
                return $fr;
            }

            throw new \LogicException('must never be called for the ambiguous "de" title');
        };

        $activity = $this->createActivity($lng_objects, $obj_language_factory);

        $result = $activity->perform(['language_keys' => 'fr']);

        $this->assertSame(['fr'], $result['uninstalled_language_keys']);
        $this->assertSame(1, $fr->uninstallCallCount());
    }

    /**
     * Regression test for the ambiguity check being moved OUT of the
     * per-key foreach loop and performed for ALL requested keys BEFORE any
     * uninstall() call (see the comment directly above the
     * AmbiguousLanguageTitleException throw in UninstallLanguage::perform():
     * "Checked upfront, for all requested keys at once, so a request naming
     * both an unambiguous and an ambiguous key never uninstalls the
     * unambiguous one before rejecting the whole call"). A request naming
     * an unambiguous key ('de') FIRST and an ambiguous one ('fr') SECOND
     * must reject the whole request and must NOT have already uninstalled
     * 'de' by the time 'fr' is reached - before this fix, the ambiguity
     * check ran inline inside the loop, so 'de' (processed first) would
     * already have been uninstalled for real before the loop reached the
     * ambiguous 'fr' and threw.
     */
    public function testAnUnambiguousKeyBeforeAnAmbiguousOneIsNeverUninstalledOnceTheWholeRequestIsRejected(): void
    {
        $de = new FakeLanguageObject(is_system_language: false, is_user_language: false, is_installed: true);

        $lng_objects = static fn(): array => [
            ['obj_id' => 1, 'title' => 'de'],
            ['obj_id' => 2, 'title' => 'fr'],
            ['obj_id' => 3, 'title' => 'fr'],
        ];
        $factory_calls = [];
        $obj_language_factory = static function (int $id) use (&$factory_calls, $de): FakeLanguageObject {
            $factory_calls[] = $id;
            if ($id === 1) {
                return $de;
            }

            throw new \LogicException('must never be called for the ambiguous "fr" title');
        };

        $activity = $this->createActivity($lng_objects, $obj_language_factory);

        try {
            // 'de' listed BEFORE the ambiguous 'fr' on purpose - this is the
            // exact ordering the regression above (see this test's own
            // docblock) requires: were the ambiguity check still inline in
            // the per-key loop, 'de' being processed first would already
            // have been uninstalled before 'fr' was ever reached.
            $activity->perform(['language_keys' => 'de,fr']);
            $this->fail('Expected an AmbiguousLanguageTitleException to be thrown.');
        } catch (AmbiguousLanguageTitleException $e) {
            $this->assertStringContainsString('"fr"', $e->getMessage());
            // 'de' was never resolved to a factory call, let alone
            // uninstalled - the ambiguity check ran BEFORE the per-key loop.
            $this->assertSame([], $factory_calls);
            $this->assertSame(0, $de->uninstallCallCount());
        }
    }

    /**
     * Same regression, exercised end-to-end through maybePerformAs(): the
     * Result\Error must surface without 'de' having been uninstalled.
     */
    public function testMaybePerformAsNeverUninstallsAnUnambiguousKeyWhenAnotherRequestedKeyIsAmbiguous(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $de = new FakeLanguageObject(is_system_language: false, is_user_language: false, is_installed: true);

        $lng_objects = static fn(): array => [
            ['obj_id' => 1, 'title' => 'de'],
            ['obj_id' => 2, 'title' => 'fr'],
            ['obj_id' => 3, 'title' => 'fr'],
        ];
        $obj_language_factory = static function (int $id) use ($de): FakeLanguageObject {
            if ($id === 1) {
                return $de;
            }

            throw new \LogicException('must never be called for the ambiguous "fr" title');
        };

        $result = $this->createActivity($lng_objects, $obj_language_factory, $rbac)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => ['de', 'fr']]);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(AmbiguousLanguageTitleException::class, $result->error());
        $this->assertSame(0, $de->uninstallCallCount());
    }

    /**
     * @param array<string, array{system?: bool, user?: bool, installed?: bool, uninstall_return?: string}> $language_objects
     *        Keyed by language key; each entry becomes a fake object with
     *        the given isSystemLanguage()/isUserLanguage()/isInstalled()
     *        answers (default false/false/true) and uninstall() return
     *        value (default 'uninstalled') reachable from the
     *        corresponding obj_id (assigned in iteration order, starting at 1).
     * @return array{0: array<int, FakeLanguageObject>, 1: \Closure, 2: \Closure}
     *         [obj id => fake object, lng_objects closure, obj_language_factory closure]
     */
    private function buildFakeLanguageWorld(array $language_objects): array
    {
        $lng_objects_list = [];
        $fakes_by_obj_id = [];
        $obj_id = 1;

        foreach ($language_objects as $language_key => $flags) {
            $lng_objects_list[] = ['obj_id' => $obj_id, 'title' => $language_key];
            $fakes_by_obj_id[$obj_id] = new FakeLanguageObject(
                is_system_language: $flags['system'] ?? false,
                is_user_language: $flags['user'] ?? false,
                is_installed: $flags['installed'] ?? true,
                uninstall_return_value: $flags['uninstall_return'] ?? 'uninstalled',
            );
            $obj_id++;
        }

        $lng_objects = static fn(): array => $lng_objects_list;
        $obj_language_factory = static fn(int $id): FakeLanguageObject => $fakes_by_obj_id[$id];

        return [$fakes_by_obj_id, $lng_objects, $obj_language_factory];
    }

    public function testInstalledOrdinaryLanguageIsUninstalled(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => [],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => 'de',
        ]);

        $this->assertSame(['de'], $result['uninstalled_language_keys']);
        $this->assertSame([], $result['system_language_keys']);
        $this->assertSame([], $result['user_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame(1, $fakes[1]->uninstallCallCount());
    }

    public function testSystemLanguageIsNeverUninstalled(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => ['system' => true],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => 'de',
        ]);

        $this->assertSame([], $result['uninstalled_language_keys']);
        $this->assertSame(['de'], $result['system_language_keys']);
        $this->assertSame([], $result['user_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame(0, $fakes[1]->uninstallCallCount());
    }

    public function testLanguageCurrentlyInUseIsNeverUninstalled(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => ['user' => true],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => 'de',
        ]);

        $this->assertSame([], $result['uninstalled_language_keys']);
        $this->assertSame([], $result['system_language_keys']);
        $this->assertSame(['de'], $result['user_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame(0, $fakes[1]->uninstallCallCount());
    }

    /**
     * isSystemLanguage() takes priority: a language that is somehow flagged
     * as both the system language and the language currently in use must be
     * reported as the system language, not as "in use" - the two branches
     * are checked in that order in perform().
     */
    public function testSystemLanguageTakesPriorityOverUserLanguage(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => ['system' => true, 'user' => true],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => 'de',
        ]);

        $this->assertSame(['de'], $result['system_language_keys']);
        $this->assertSame([], $result['user_language_keys']);
    }

    public function testExistingButNotInstalledLanguageObjectIsReportedAsNotInstalled(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => ['installed' => false],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => 'de',
        ]);

        $this->assertSame([], $result['uninstalled_language_keys']);
        $this->assertSame([], $result['system_language_keys']);
        $this->assertSame([], $result['user_language_keys']);
        $this->assertSame(['de'], $result['not_installed_language_keys']);
        $this->assertSame(0, $fakes[1]->uninstallCallCount());
    }

    /**
     * Regression test: uninstall() itself is the authority on whether the
     * uninstallation actually happened, not merely the three outer guards
     * (isSystemLanguage()/isUserLanguage()/isInstalled()). A language that
     * passes all three guards (so perform() does call uninstall()) but
     * whose uninstall() nonetheless returns '' (e.g. because a future,
     * additional internal guard inside \ilObjLanguage::uninstall() itself
     * rejects it) must be reported as not_installed_language_keys, never
     * as uninstalled_language_keys - anything else would misreport a
     * rejected uninstallation as a success.
     */
    public function testLanguageWhereUninstallItselfRejectsIsReportedAsNotInstalled(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => ['uninstall_return' => ''],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => 'de',
        ]);

        $this->assertSame([], $result['uninstalled_language_keys']);
        $this->assertSame([], $result['system_language_keys']);
        $this->assertSame([], $result['user_language_keys']);
        $this->assertSame(['de'], $result['not_installed_language_keys']);
        // uninstall() must still have been attempted - unlike the guarded
        // cases (system/user/not-installed), where it is never called.
        $this->assertSame(1, $fakes[1]->uninstallCallCount());
    }

    /**
     * A language key with no corresponding "lng" object at all must be
     * reported as not installed - and, crucially, $obj_language_factory
     * must never even be invoked for it (there is no obj_id to construct it
     * from in the first place).
     */
    public function testUnknownLanguageKeyIsReportedAsNotInstalledWithoutTouchingTheObjectFactory(): void
    {
        $lng_objects = static fn(): array => [];
        $factory_calls = [];
        $obj_language_factory = static function (int $id) use (&$factory_calls): FakeLanguageObject {
            $factory_calls[] = $id;
            throw new \LogicException('must never be called for an unknown language key');
        };

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => 'xx',
        ]);

        $this->assertSame([], $result['uninstalled_language_keys']);
        $this->assertSame([], $result['system_language_keys']);
        $this->assertSame([], $result['user_language_keys']);
        $this->assertSame(['xx'], $result['not_installed_language_keys']);
        $this->assertSame([], $factory_calls);
    }

    /**
     * A single request can carry languages in every possible state at once;
     * each must land in its own bucket, and only the genuinely uninstalled
     * one may ever call uninstall().
     */
    public function testMixedBulkRequestPartitionsEachKeyIntoTheCorrectBucket(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => [],
            'en' => ['system' => true],
            'fr' => ['user' => true],
            'it' => ['installed' => false],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            // 'xx' has no backing object at all.
            'language_keys' => ['de', 'en', 'fr', 'it', 'xx'],
        ]);

        $this->assertSame(['de'], $result['uninstalled_language_keys']);
        $this->assertSame(['en'], $result['system_language_keys']);
        $this->assertSame(['fr'], $result['user_language_keys']);
        $this->assertSame(['it', 'xx'], $result['not_installed_language_keys']);

        $this->assertSame(1, $fakes[1]->uninstallCallCount(), 'de must be uninstalled');
        $this->assertSame(0, $fakes[2]->uninstallCallCount(), 'system language must never be uninstalled');
        $this->assertSame(0, $fakes[3]->uninstallCallCount(), 'in-use language must never be uninstalled');
        $this->assertSame(0, $fakes[4]->uninstallCallCount(), 'not-installed language must never be uninstalled');
    }

    public function testDuplicateLanguageKeysAcrossStringAndArrayAreDeduplicated(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => [],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => [' de, de ', 'de'],
        ]);

        $this->assertSame(['de'], $result['uninstalled_language_keys']);
        $this->assertSame(1, $fakes[1]->uninstallCallCount());
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

        $this->createActivity(static fn(): array => [], static fn(int $id) => null)->perform($parameters);
    }

    /**
     * Regression test for the perform()-parameter-type-check unification
     * (see UninstallLanguage::perform()): a non-array $parameters must now
     * raise the concrete InvalidInputException - not just the more general
     * \InvalidArgumentException it extends - since perform() was switched
     * from a plain \InvalidArgumentException to InvalidInputException for
     * this check, matching every other Activity in this component.
     */
    public function testNonArrayParametersAreRejected(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity(static fn(): array => [], static fn(int $id) => null)->perform('not-an-array');
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
        // UninstallLanguage has no mode parameter - the select field must
        // never be built.
        $field->expects($this->never())->method('select');

        $group = $this->createMock(Group::class);
        $field->expects($this->once())
            ->method('group')
            ->with(['language_keys' => $text])
            ->willReturn($group);

        $activity = $this->createActivity(
            static fn(): array => [],
            static fn(int $id) => null
        );

        $this->assertSame($group, $activity->getInputDescription($field));
    }

    public function testPermissionDeniedBeforePerformNeverCallsObjectFactoryOrUninstall(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->once())
            ->method('checkAccessOfUser')
            ->with(6, 'write', $this->anything())
            ->willReturn(false);

        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => [],
        ]);
        $language = $this->createMock(Language::class);
        $language->method('txt')->with('msg_no_perm_write')->willReturn('no write permission');

        $result = $this->createActivity(
            $lng_objects,
            $obj_language_factory,
            $rbac,
            $language
        )->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => 'de']);

        $this->assertTrue($result->isError());
        $this->assertSame(0, $fakes[1]->uninstallCallCount());
    }

    public function testPermissionGrantedPerformsAndReturnsOkResult(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => [],
        ]);

        $result = $this->createActivity(
            $lng_objects,
            $obj_language_factory,
            $rbac
        )->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => 'de']);

        $this->assertFalse($result->isError());
        $this->assertSame(['de'], $result->value()['uninstalled_language_keys']);
        $this->assertSame(1, $fakes[1]->uninstallCallCount());
    }

    /**
     * Contract test: a real GUI caller (class.ilObjLanguageFolderGUI.php)
     * always builds 'language_keys' as a PHP array of strings, never a
     * comma-separated string - and GrindsFormInput::grind(), via
     * collectRawValues()'s call to joinListOfStringsRawValue() (see that
     * method's own docblock: "Returns $raw_value joined into a single
     * comma-separated string if every one of its items is a string;
     * otherwise returns $raw_value unchanged, in whatever shape the caller
     * passed"), must join that array into the same shape a real HTML text
     * input would carry BEFORE it reaches the declared Text field, rather
     * than rejecting it. This is exercised through the REAL
     * getInputDescription()/grind() pipeline (createRealFieldsUiFactory(),
     * not a mocked FormInput) via maybePerformAs() - the previously
     * blocking regression this test guards against.
     */
    public function testMaybePerformAsAcceptsAnArrayOfLanguageKeysAndUninstallsEachOne(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => [],
            'fr' => [],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory, $rbac)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => ['de', 'fr']]);

        $this->assertFalse($result->isError());
        $this->assertSame(['de', 'fr'], $result->value()['uninstalled_language_keys']);
        $this->assertSame(1, $fakes[1]->uninstallCallCount());
        $this->assertSame(1, $fakes[2]->uninstallCallCount());
    }

    private function createActivity(
        \Closure $lng_objects,
        \Closure $obj_language_factory,
        ?\ilRbacSystem $rbac = null,
        ?Language $language = null
    ): UninstallLanguage {
        return new UninstallLanguage(
            $this->createMock(RefineryFactory::class),
            $language ?? $this->createMock(Language::class),
            $rbac ?? $this->createMock(\ilRbacSystem::class),
            0,
            $lng_objects,
            $obj_language_factory
        );
    }
}

/**
 * Minimal stand-in for \ilObjLanguage: that class cannot be constructed or
 * meaningfully mocked in a unit test (its constructor requires the global
 * $DIC), so tests substitute this fake via UninstallLanguage's
 * $obj_language_factory closure instead. Counts uninstall() invocations so
 * tests can assert it was never called for a bucket other than
 * "uninstalled".
 */
final class FakeLanguageObject
{
    private int $uninstall_calls = 0;

    /**
     * @param string $uninstall_return_value What uninstall() returns - a
     *        non-empty string mimics a real, successful uninstall; ''
     *        mimics \ilObjLanguage::uninstall() internally rejecting the
     *        call despite the three outer guards (isSystemLanguage()/
     *        isUserLanguage()/isInstalled()) having let it through - e.g. a
     *        future additional guard added inside uninstall() itself.
     */
    public function __construct(
        private readonly bool $is_system_language,
        private readonly bool $is_user_language,
        private readonly bool $is_installed,
        private readonly string $uninstall_return_value = 'uninstalled',
    ) {
    }

    public function isSystemLanguage(): bool
    {
        return $this->is_system_language;
    }

    public function isUserLanguage(): bool
    {
        return $this->is_user_language;
    }

    public function isInstalled(): bool
    {
        return $this->is_installed;
    }

    public function uninstall(): string
    {
        $this->uninstall_calls++;

        return $this->uninstall_return_value;
    }

    public function uninstallCallCount(): int
    {
        return $this->uninstall_calls;
    }
}
