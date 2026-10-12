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
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * \ilObjLanguage cannot be constructed in a unit test (its constructor needs $DIC), so a fake object stands in.
 */
class RemoveLocalLanguageChangesTest extends ActivityWithPerformResultContractTestCase
{
    use RemoveLocalLanguageChangesTestFixture;

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
     * @param array<string, array{installed?: bool, remove_local_changes_return?: bool, unwritten_overlay?: list<string>}> $language_objects keyed by language key
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
                modules_with_unwritten_overlay: $flags['unwritten_overlay'] ?? [],
            );
            $obj_id++;
        }

        $lng_objects = static fn(): array => $lng_objects_list;
        $obj_language_factory = static fn(int $id): FakeRemoveLocalLanguageChangesObject => $fakes_by_obj_id[$id];

        return [$fakes_by_obj_id, $lng_objects, $obj_language_factory];
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
            42
        );

        $this->assertTrue($activity->isAllowedToPerform(6, ['language_keys' => 'de']));
    }

    public function testIsAllowedToPerformReturnsFalseWhenRbacDenies(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
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

        $rbac = $this->createStub(\ilRbacSystem::class);
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

    /**
     * A language whose migrated-module overlay could not be written still counts as removed and is also listed in
     * "overlay_write_failed_language_keys".
     */
    public function testALanguageWithAnUnwrittenOverlayIsListedAdditionallyToBeingRemoved(): void
    {
        [, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => ['unwritten_overlay' => ['pilot']],
            'en' => [],
            'fr' => ['remove_local_changes_return' => false, 'unwritten_overlay' => ['pilot']],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform([
            'language_keys' => ['de', 'en', 'fr'],
        ]);

        $this->assertSame(['de', 'en'], $result['removed_local_changes_language_keys']);
        $this->assertSame(['fr'], $result['invalid_language_file_keys']);
        $this->assertSame(['de'], $result['overlay_write_failed_language_keys']);
    }

    /** Plugin language files are re-applied once, only for languages whose local changes were actually removed. */
    public function testRefreshPluginsIsCalledOnceWithExactlyTheRemovedLanguageKeys(): void
    {
        [, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => [],
            'en' => [],
            'fr' => ['remove_local_changes_return' => false],
            'it' => ['installed' => false],
        ]);
        $repository = $this->languageObjectProviderMock($lng_objects, $obj_language_factory);
        $repository->expects($this->once())->method('refreshPlugins')->with(['de', 'en']);

        $this->createActivity($lng_objects, $obj_language_factory, repository: $repository)
            ->perform(['language_keys' => ['de', 'en', 'fr', 'it']]);
    }

    /** Without a single removed language refreshPlugins() is not called at all, not even with an empty list. */
    public function testRefreshPluginsIsNeverCalledWhenNoLanguagesLocalChangesWereActuallyRemoved(): void
    {
        [, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => ['remove_local_changes_return' => false],
            'en' => ['installed' => false],
        ]);
        $repository = $this->languageObjectProviderMock($lng_objects, $obj_language_factory);
        $repository->expects($this->never())->method('refreshPlugins');

        $this->createActivity($lng_objects, $obj_language_factory, repository: $repository)
            ->perform(['language_keys' => ['de', 'en']]);
    }

    /**
     * A Throwable from removeLocalChanges() itself (not a null return) is logged as a warning naming "setup update"
     * as the way back, then rethrown and masked as 500 by maybePerformAs().
     */
    public function testRemoveLocalChangesThrowingIsLoggedWithSetupUpdateAdviceAndSurfacesAsA500ResultError(): void
    {
        $lng_objects = static fn(): array => [['obj_id' => 1, 'title' => 'de']];
        $repository = $this->createMock(RepositoryProvider::class);
        $repository->method('listLanguageObjects')->willReturnCallback($lng_objects);
        $repository->method('isInstalled')->willReturn(true);
        $repository->method('removeLocalChanges')->willThrowException(new \RuntimeException('db gone'));
        $repository->expects($this->once())->method('logWarning')->with($this->stringContains('setup.php update'));
        $repository->expects($this->never())->method('refreshPlugins');

        $rbac = $this->createStub(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $result = $this->createActivity($lng_objects, static fn(int $id) => null, $rbac, repository: $repository)
            ->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => 'de']);

        $this->assertTrue($result->isError());
        $this->assertSame(500, $result->error()->getCode());
        $this->assertSame('db gone', $result->error()->getPrevious()?->getMessage());
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
        $result = $this->createActivity(
            $lng_objects,
            $obj_language_factory,
            $rbac
        )->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => 'de']);

        $this->assertTrue($result->isError());
        $this->assertSame(0, $fakes[1]->removeLocalChangesCallCount());
    }

    public function testPermissionGrantedPerformsAndReturnsOkResult(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
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
        $rbac = $this->createStub(\ilRbacSystem::class);
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
        $rbac = $this->createStub(\ilRbacSystem::class);
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
}
