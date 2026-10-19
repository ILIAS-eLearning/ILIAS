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
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * \ilObjLanguage cannot be constructed in a unit test (its constructor needs $DIC), so a fake object stands in.
 */
class RemoveLocalLanguageChangesTest extends ActivityWithPerformResultContractTestCase
{
    use LanguageObjectRepositoryProvider;

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
     * @param array<string, array{installed?: bool, remove_local_changes_return?: bool}> $language_objects keyed by language key
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

    /** The ambiguous title is embedded raw: escaping is RendersActivityErrors' job, doing it here would double-escape. */
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

    /** Via maybePerformAs() the ambiguity surfaces as Result\Error and the object factory is never reached. */
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

    /** An ambiguous title does not poison unrelated, unambiguous ones. */
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

    /** The ambiguity check covers all requested keys before any change: 'de' must not be changed when 'fr' is ambiguous. */
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
            // 'de' first on purpose
            $activity->perform(['language_keys' => 'de,fr']);
            $this->fail('Expected an AmbiguousLanguageTitleException to be thrown.');
        } catch (AmbiguousLanguageTitleException $e) {
            $this->assertStringContainsString('"fr"', $e->getMessage());
            // 'de' was never resolved, the check ran before the per-key loop
            $this->assertSame([], $factory_calls);
            $this->assertSame(0, $de->removeLocalChangesCallCount());
        }
    }

    /** Same, end-to-end through maybePerformAs(). */
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

    /** isAllowedToPerform() is a pure permission check: no object access, no removeLocalChanges(). */
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

    /** removeLocalChanges() returning false (invalid language file) is reported as invalid_language_file_keys. */
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
        $this->assertSame(1, $fakes[1]->removeLocalChangesCallCount());
    }

    /** A key without a "lng" object is not installed, and the object factory is never invoked for it. */
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

    /** Every state in one request lands in its own bucket; removeLocalChanges() only runs for the two installed ones. */
    public function testMixedBulkRequestPartitionsEachKeyIntoTheCorrectBucket(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => [],
            'fr' => ['remove_local_changes_return' => false],
            'it' => ['installed' => false],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
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

    /** Pins InvalidInputException, not just its parent \InvalidArgumentException, which would pass silently. */
    public function testNonArrayParametersAreRejected(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->createActivity(static fn(): array => [], static fn(int $id) => null)->perform('not-an-array');
    }

    /** Denied permission short-circuits before perform(): no object is touched. */
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

    /** ilObjLanguageFolderGUI sends 'language_keys' as array of strings; the real grind() pipeline must join it. */
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

    /** A Throwable from perform() becomes a Result\Error. */
    public function testThrowableFromPerformIsTurnedIntoAResultError(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $lng_objects = static fn(): array => [];
        $obj_language_factory = static fn(int $id) => null;

        // an empty language_keys value throws inside perform(), after the permission check passed
        $result = $this->createActivity(
            $lng_objects,
            $obj_language_factory,
            $rbac
        )->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => ' , ']);

        $this->assertTrue($result->isError());
        $this->assertInstanceOf(InvalidInputException::class, $result->error());
        $this->assertSame(400, $result->error()->getCode());
    }

    /** A missing 'language_keys' fails the required Text field in grind(), before rbac is touched. */
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
            $language ?? $this->createMock(Language::class),
            $this->rbacOf($rbac ?? $this->createMock(\ilRbacSystem::class)),
            $this->languageFolder($language_folder_ref_id),
            $this->languageObjectProvider($lng_objects, $obj_language_factory)
        );
    }
}

/**
 * Stand-in for \ilObjLanguage (see above); counts removeLocalChanges() calls.
 */
final class FakeRemoveLocalLanguageChangesObject
{
    private int $remove_local_changes_calls = 0;

    /**
     * @param bool $remove_local_changes_return_value false mimics a language file failing check() after isInstalled() passed
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
