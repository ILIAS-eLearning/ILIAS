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
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Activities\SafeToDisplayActivityError;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * RemoveLocalLanguageChanges has no ilSetupLanguage collaborator (unlike
 * InstallLanguage/UpdateLanguage) - instead it resolves language keys to
 * objects via two closures, $lng_objects and $obj_language_factory. The
 * object returned by $obj_language_factory must behave like \ilObjLanguage
 * (isInstalled(), removeLocalChanges()), but \ilObjLanguage itself cannot be
 * constructed or meaningfully mocked in a unit test (its constructor needs
 * the global $DIC) - so tests use a small fake object instead, injected
 * through $obj_language_factory.
 */
class RemoveLocalLanguageChangesTest extends ActivityWithPerformResultContractTestCase
{
    protected function createDefaultActivity(): RemoveLocalLanguageChanges
    {
        [, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld(['de' => []]);

        return $this->createActivity($lng_objects, $obj_language_factory);
    }

    protected function validPerformParameters(): array
    {
        return ['language_keys' => 'de'];
    }

    /**
     * @param array<string, array{installed?: bool, remove_local_changes_return?: bool}> $language_objects
     *        Keyed by language key; each entry becomes a fake object with
     *        the given isInstalled() answer (default true) and
     *        removeLocalChanges() return value (default true) reachable
     *        from the corresponding obj_id (assigned in iteration order,
     *        starting at 1).
     * @return array{0: array<int, FakeRemoveLocalLanguageChangesObject>, 1: \Closure, 2: \Closure}
     *         [obj id => fake object, lng_objects closure, obj_language_factory closure]
     */
    private function buildFakeLanguageWorld(array $language_objects): array
    {
        $lng_objects_list = [];
        $fakes_by_obj_id = [];
        $obj_id = 1;

        foreach ($language_objects as $language_key => $flags) {
            $lng_objects_list[] = ['obj_id' => $obj_id, 'title' => $language_key];
            $fakes_by_obj_id[$obj_id] = new FakeRemoveLocalLanguageChangesObject(
                is_installed: $flags['installed'] ?? true,
                remove_local_changes_return_value: $flags['remove_local_changes_return'] ?? true,
            );
            $obj_id++;
        }

        $lng_objects = static fn(): array => $lng_objects_list;
        $obj_language_factory = static fn(int $id): FakeRemoveLocalLanguageChangesObject => $fakes_by_obj_id[$id];

        return [$fakes_by_obj_id, $lng_objects, $obj_language_factory];
    }

    // -----------------------------------------------------------------
    // resolveObjIdsByLanguageKey() ambiguous-title guard (m1): two "lng"
    // objects sharing the same title must reject the whole request for
    // that title rather than silently resolving to whichever one happened
    // to be enumerated last - see UninstallLanguage's identical guard and
    // UninstallLanguageTest's equivalent tests for the same reasoning.
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
     * layer's job (see RemoveLocalLanguageChanges::perform()'s own comment
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
     * key coexisting with an ambiguous one must still resolve and have its
     * local changes removed normally.
     */
    public function testUnambiguousLanguageKeyStillWorksAlongsideAnAmbiguousOneInTheSameLngObjectsList(): void
    {
        $lng_objects = static fn(): array => [
            ['obj_id' => 1, 'title' => 'de'],
            ['obj_id' => 2, 'title' => 'de'],
            ['obj_id' => 3, 'title' => 'fr'],
        ];
        $fr = new FakeRemoveLocalLanguageChangesObject(is_installed: true);
        $obj_language_factory = static function (int $id) use ($fr): FakeRemoveLocalLanguageChangesObject {
            if ($id === 3) {
                return $fr;
            }

            throw new \LogicException('must never be called for the ambiguous "de" title');
        };

        $activity = $this->createActivity($lng_objects, $obj_language_factory);

        $result = $activity->perform(['language_keys' => 'fr']);

        $this->assertSame(['fr'], $result['removed_local_changes_language_keys']);
        $this->assertSame(1, $fr->removeLocalChangesCallCount());
    }

    /**
     * Regression test for the ambiguity check being moved OUT of the
     * per-key foreach loop and performed for ALL requested keys BEFORE any
     * removeLocalChanges() call (see the comment directly above that check
     * in perform(): "Checked upfront, for all requested keys at once, so a
     * request naming both an unambiguous and an ambiguous key never
     * changes the unambiguous one before rejecting the whole call."). A
     * request naming an unambiguous key ('de') FIRST and an ambiguous one
     * ('fr') SECOND must reject the whole request and must NOT have already
     * changed 'de' by the time 'fr' is reached - before this fix, the
     * ambiguity check ran inline inside the loop, so 'de' (processed first)
     * would already have had its local changes removed for real before the
     * loop reached the ambiguous 'fr' and threw.
     */
    public function testAnUnambiguousKeyBeforeAnAmbiguousOneNeverHasLocalChangesRemovedOnceTheWholeRequestIsRejected(): void
    {
        $de = new FakeRemoveLocalLanguageChangesObject(is_installed: true);

        $lng_objects = static fn(): array => [
            ['obj_id' => 1, 'title' => 'de'],
            ['obj_id' => 2, 'title' => 'fr'],
            ['obj_id' => 3, 'title' => 'fr'],
        ];
        $factory_calls = [];
        $obj_language_factory = static function (int $id) use (&$factory_calls, $de): FakeRemoveLocalLanguageChangesObject {
            $factory_calls[] = $id;
            if ($id === 1) {
                return $de;
            }

            throw new \LogicException('must never be called for the ambiguous "fr" title');
        };

        $activity = $this->createActivity($lng_objects, $obj_language_factory);

        try {
            // 'de' listed BEFORE the ambiguous 'fr' on purpose - see the
            // "Checked upfront, for all requested keys at once" comment
            // above the ambiguity check in perform().
            $activity->perform(['language_keys' => 'de,fr']);
            $this->fail('Expected an AmbiguousLanguageTitleException to be thrown.');
        } catch (AmbiguousLanguageTitleException $e) {
            $this->assertStringContainsString('"fr"', $e->getMessage());
            // 'de' was never resolved to a factory call, let alone changed -
            // the ambiguity check ran BEFORE the per-key loop.
            $this->assertSame([], $factory_calls);
            $this->assertSame(0, $de->removeLocalChangesCallCount());
        }
    }

    /**
     * Same regression, exercised end-to-end through maybePerformAs(): the
     * Result\Error must surface without 'de' having had its local changes
     * removed.
     */
    public function testMaybePerformAsNeverRemovesLocalChangesForAnUnambiguousKeyWhenAnotherRequestedKeyIsAmbiguous(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $de = new FakeRemoveLocalLanguageChangesObject(is_installed: true);

        $lng_objects = static fn(): array => [
            ['obj_id' => 1, 'title' => 'de'],
            ['obj_id' => 2, 'title' => 'fr'],
            ['obj_id' => 3, 'title' => 'fr'],
        ];
        $obj_language_factory = static function (int $id) use ($de): FakeRemoveLocalLanguageChangesObject {
            if ($id === 1) {
                return $de;
            }

            throw new \LogicException('must never be called for the ambiguous "fr" title');
        };

        $result = $this->createActivity($lng_objects, $obj_language_factory, $rbac)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => ['de', 'fr']]);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(AmbiguousLanguageTitleException::class, $result->error());
        $this->assertSame(0, $de->removeLocalChangesCallCount());
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

        $activity = $this->createActivity(
            static fn(): array => [],
            static fn(int $id) => null,
            $rbac,
            null,
            42
        );

        $this->assertTrue($activity->isAllowedToPerform(6, ['language_keys' => 'de']));
    }

    public function testIsAllowedToPerformReturnsFalseWhenRbacDenies(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(false);

        $activity = $this->createActivity(
            static fn(): array => [],
            static fn(int $id) => null,
            $rbac
        );

        $this->assertFalse($activity->isAllowedToPerform(6, ['language_keys' => 'de']));
    }

    /**
     * isAllowedToPerform() must be a pure permission check: it must never
     * touch $lng_objects or $obj_language_factory (and therefore never call
     * removeLocalChanges()) - unlike perform(), which is the only place
     * allowed to have side effects.
     */
    public function testIsAllowedToPerformNeverTouchesLngObjectsOrObjectFactory(): void
    {
        $lng_objects = static function (): array {
            throw new \LogicException('lng_objects must never be called by isAllowedToPerform()');
        };
        $obj_language_factory = static function (int $id) {
            throw new \LogicException('obj_language_factory must never be called by isAllowedToPerform()');
        };

        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $activity = $this->createActivity($lng_objects, $obj_language_factory, $rbac);

        $this->assertTrue($activity->isAllowedToPerform(6, ['language_keys' => 'de']));
    }

    // -----------------------------------------------------------------
    // perform()
    // -----------------------------------------------------------------

    public function testInstalledLanguageWithValidFileHasItsLocalChangesRemoved(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => [],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => 'de',
        ]);

        $this->assertSame(['de'], $result['removed_local_changes_language_keys']);
        $this->assertSame([], $result['invalid_language_file_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame(1, $fakes[1]->removeLocalChangesCallCount());
    }

    public function testExistingButNotInstalledLanguageObjectIsReportedAsNotInstalledWithoutCallingRemoveLocalChanges(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => ['installed' => false],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => 'de',
        ]);

        $this->assertSame([], $result['removed_local_changes_language_keys']);
        $this->assertSame([], $result['invalid_language_file_keys']);
        $this->assertSame(['de'], $result['not_installed_language_keys']);
        $this->assertSame(0, $fakes[1]->removeLocalChangesCallCount());
    }

    /**
     * Regression test: removeLocalChanges() itself is the authority on
     * whether the language file was valid, not merely the outer
     * isInstalled() guard. A language that passes isInstalled() (so
     * perform() does call removeLocalChanges()) but whose
     * removeLocalChanges() nonetheless returns false (invalid language
     * file) must be reported as invalid_language_file_keys, never as
     * removed_local_changes_language_keys or not_installed_language_keys.
     */
    public function testInstalledLanguageWhereRemoveLocalChangesFailsIsReportedAsInvalidLanguageFile(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => ['remove_local_changes_return' => false],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => 'de',
        ]);

        $this->assertSame([], $result['removed_local_changes_language_keys']);
        $this->assertSame(['de'], $result['invalid_language_file_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        // removeLocalChanges() must still have been attempted.
        $this->assertSame(1, $fakes[1]->removeLocalChangesCallCount());
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
        $obj_language_factory = static function (int $id) use (&$factory_calls) {
            $factory_calls[] = $id;
            throw new \LogicException('must never be called for an unknown language key');
        };

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => 'xx',
        ]);

        $this->assertSame([], $result['removed_local_changes_language_keys']);
        $this->assertSame([], $result['invalid_language_file_keys']);
        $this->assertSame(['xx'], $result['not_installed_language_keys']);
        $this->assertSame([], $factory_calls);
    }

    /**
     * A single request can carry languages in every possible state at once;
     * each must land in its own bucket, and removeLocalChanges() must only
     * ever be invoked for the two genuinely installed languages (regardless
     * of whether their file turns out valid or not) - never for the
     * not-installed or unknown ones.
     */
    public function testMixedBulkRequestPartitionsEachKeyIntoTheCorrectBucket(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => [],
            'fr' => ['remove_local_changes_return' => false],
            'it' => ['installed' => false],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            // 'xx' has no backing object at all.
            'language_keys' => ['de', 'fr', 'it', 'xx'],
        ]);

        $this->assertSame(['de'], $result['removed_local_changes_language_keys']);
        $this->assertSame(['fr'], $result['invalid_language_file_keys']);
        $this->assertSame(['it', 'xx'], $result['not_installed_language_keys']);

        $this->assertSame(1, $fakes[1]->removeLocalChangesCallCount(), 'de must have its local changes removed');
        $this->assertSame(1, $fakes[2]->removeLocalChangesCallCount(), 'fr is installed, so must still be attempted');
        $this->assertSame(0, $fakes[3]->removeLocalChangesCallCount(), 'not-installed language must never be attempted');
    }

    public function testDuplicateLanguageKeysAcrossStringAndArrayAreDeduplicated(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => [],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => [' de, de ', 'de'],
        ]);

        $this->assertSame(['de'], $result['removed_local_changes_language_keys']);
        $this->assertSame(1, $fakes[1]->removeLocalChangesCallCount());
    }

    public function testCommaSeparatedStringIsNormalizedIntoMultipleLanguageKeys(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => [],
            'fr' => [],
            'it' => [],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => ' de, fr ,it',
        ]);

        $this->assertSame(['de', 'fr', 'it'], $result['removed_local_changes_language_keys']);
    }

    public static function invalidLanguageKeysProvider(): array
    {
        return [
            'missing language_keys key' => [[]],
            'empty (only whitespace/commas)' => [['language_keys' => ' , ']],
            'nested array value' => [['language_keys' => ['de', ['fr']]]],
            'non-array, non-string value' => [['language_keys' => 42]],
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
     * (see RemoveLocalLanguageChanges::perform(), ~line 143): this check was
     * switched from a plain \InvalidArgumentException to the concrete
     * InvalidInputException, to be consistent with AddLanguageEntry,
     * SetLanguageDetectionEnabled and SetLanguageTranslationEnabled, which
     * already used it. The docblock/comment that used to justify the
     * opposite ("deliberately NOT an InvalidInputException") is stale and
     * has been removed - this test now pins the CURRENT, more specific
     * exception class, not just \InvalidArgumentException (which
     * InvalidInputException extends, and against which this assertion would
     * therefore have kept passing silently even after the behaviour change -
     * see RendersActivityErrors, which treats InvalidInputException/
     * SafeToDisplayActivityError specially).
     */
    public function testNonArrayParametersAreRejected(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity(static fn(): array => [], static fn(int $id) => null)->perform('not-an-array');
    }

    // -----------------------------------------------------------------
    // maybePerformAs()
    // -----------------------------------------------------------------

    /**
     * Regression test analogous to
     * UninstallLanguageTest::testPermissionDeniedBeforePerformNeverCallsObjectFactoryOrUninstall():
     * a denied permission must short-circuit before perform() ever runs -
     * $lng_objects/$obj_language_factory (and therefore removeLocalChanges())
     * must never be touched.
     */
    public function testPermissionDeniedBeforePerformNeverCallsObjectFactoryOrRemoveLocalChanges(): void
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
        $this->assertSame(0, $fakes[1]->removeLocalChangesCallCount());
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
        $this->assertSame(['de'], $result->value()['removed_local_changes_language_keys']);
        $this->assertSame(1, $fakes[1]->removeLocalChangesCallCount());
    }

    /**
     * Contract test: a real GUI caller (class.ilObjLanguageFolderGUI.php)
     * always builds 'language_keys' as a PHP array of strings, never a
     * comma-separated string - and GrindsFormInput::grind() (via its
     * private joinListOfStringsRawValue() helper - see that method's own
     * docblock) must join that array into the same shape a real HTML text
     * input would carry BEFORE
     * it reaches the declared Text field, rather than rejecting it. This
     * is exercised through the REAL getInputDescription()/grind() pipeline
     * (createRealFieldsUiFactory(), not a mocked FormInput) via
     * maybePerformAs() - the previously blocking regression this test
     * guards against.
     */
    public function testMaybePerformAsAcceptsAnArrayOfLanguageKeysAndRemovesLocalChangesForEachOne(): void
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
        $this->assertSame(['de', 'fr'], $result->value()['removed_local_changes_language_keys']);
        $this->assertSame(1, $fakes[1]->removeLocalChangesCallCount());
        $this->assertSame(1, $fakes[2]->removeLocalChangesCallCount());
    }

    /**
     * A Throwable raised from within perform() (e.g. a validation failure
     * on the normalized parameters, or any other unexpected failure) must
     * be turned into a Result\Error rather than propagating - this is the
     * whole point of maybePerformAs() wrapping perform() in a try/catch.
     */
    public function testThrowableFromPerformIsTurnedIntoAResultError(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $lng_objects = static fn(): array => [];
        $obj_language_factory = static fn(int $id) => null;

        // An empty language_keys value normalizes to [] and throws inside
        // perform() (via toLanguageKeyList()) - reachable only after the
        // permission check has already passed, exercising the catch block.
        $result = $this->createActivity(
            $lng_objects,
            $obj_language_factory,
            $rbac
        )->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => ' , ']);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertInstanceOf(SafeToDisplayActivityError::class, $result->error());
    }

    /**
     * A completely missing 'language_keys' key is now caught by grind()
     * itself (see GrindsFormInput): the required Text field receives a
     * blank raw value and fails its own required-field constraint before
     * normalizeParameters()/isAllowedToPerform() are ever reached - so the
     * rejection surfaces as a Result\Error carrying the UI field's own
     * (string) validation message, not an InvalidArgumentException thrown
     * by normalizeParameters(). Either way, the rbac system must never be
     * touched (validation happens first).
     */
    public function testMissingLanguageKeysParameterViaMaybePerformAsIsAResultErrorAndNeverChecksPermission(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $result = $this->createActivity(
            static fn(): array => [],
            static fn(int $id) => null,
            $rbac
        )->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, []);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertNotSame('', $result->error()->getMessage());
    }

    private function createActivity(
        \Closure $lng_objects,
        \Closure $obj_language_factory,
        ?\ilRbacSystem $rbac = null,
        ?Language $language = null,
        int $language_folder_ref_id = 0
    ): RemoveLocalLanguageChanges {
        return new RemoveLocalLanguageChanges(
            $this->createMock(RefineryFactory::class),
            $language ?? $this->createMock(Language::class),
            $rbac ?? $this->createMock(\ilRbacSystem::class),
            $language_folder_ref_id,
            $lng_objects,
            $obj_language_factory
        );
    }
}

/**
 * Minimal stand-in for \ilObjLanguage: that class cannot be constructed or
 * meaningfully mocked in a unit test (its constructor requires the global
 * $DIC), so tests substitute this fake via RemoveLocalLanguageChanges's
 * $obj_language_factory closure instead. Counts removeLocalChanges()
 * invocations so tests can assert it was never called for a bucket other
 * than "removed"/"invalid".
 */
final class FakeRemoveLocalLanguageChangesObject
{
    private int $remove_local_changes_calls = 0;

    /**
     * @param bool $remove_local_changes_return_value What removeLocalChanges()
     *        returns - true mimics a real, successful removal of local
     *        changes; false mimics \ilObjLanguage::removeLocalChanges()
     *        rejecting the call because the underlying language file failed
     *        check() (isInstalled() having already let it through).
     */
    public function __construct(
        private readonly bool $is_installed,
        private readonly bool $remove_local_changes_return_value = true,
    ) {
    }

    public function isInstalled(): bool
    {
        return $this->is_installed;
    }

    public function removeLocalChanges(): bool
    {
        $this->remove_local_changes_calls++;

        return $this->remove_local_changes_return_value;
    }

    public function removeLocalChangesCallCount(): int
    {
        return $this->remove_local_changes_calls;
    }
}
