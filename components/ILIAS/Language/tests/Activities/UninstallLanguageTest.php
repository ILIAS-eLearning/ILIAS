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
use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\UI\Component\Input\Field\Text;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * UninstallLanguage works on language objects behind a mocked RepositoryProvider, answering from small fake
 * objects (\ilObjLanguage needs the global $DIC) via LanguageObjectRepositoryProvider.
 */
class UninstallLanguageTest extends ActivityWithPerformResultContractTestCase
{
    use UninstallLanguageTestFixture;

    protected function createDefaultActivity(): UninstallLanguage
    {
        [, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld(['de' => []]);

        return $this->createActivity($lng_objects, $obj_language_factory);
    }

    protected function validPerformParameters(): array
    {
        return ['language_keys' => 'de'];
    }

    public function testThrowableFromWithinPerformIsTurnedIntoAResultErrorByMaybePerformAs(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
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

    /**
     * @param array<string, array{system?: bool, user?: bool, installed?: bool, uninstall_return?: string, unwritten_overlay_modules?: list<string>}> $language_objects keyed by language key
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
                unwritten_overlay_modules: $flags['unwritten_overlay_modules'] ?? [],
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
        $this->assertSame([], $result['overlay_write_failed_language_keys']);
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

    /** isSystemLanguage() takes priority over "in use" when both flags are set. */
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

    /** uninstall() returning '' is the authority: reported as not_installed_language_keys, never as uninstalled. */
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
        // uninstall() must still have been attempted
        $this->assertSame(1, $fakes[1]->uninstallCallCount());
    }

    /** A module whose PO/MO overlay could not be removed is reported in addition to the successful uninstall. */
    public function testOverlayWriteFailureIsReportedAlongsideTheSuccessfulUninstall(): void
    {
        [$fakes, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => ['unwritten_overlay_modules' => ['pilot']],
            'fr' => [],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform(['language_keys' => 'de,fr']);

        $this->assertSame(['de', 'fr'], $result['uninstalled_language_keys']);
        $this->assertSame(['de'], $result['overlay_write_failed_language_keys']);
        $this->assertSame(1, $fakes[1]->uninstallCallCount());
    }

    /** A language the guards of uninstall() rejected has no overlay state to report. */
    public function testNoOverlayWriteFailureIsReportedForALanguageUninstallRejected(): void
    {
        [, $lng_objects, $obj_language_factory] = $this->buildFakeLanguageWorld([
            'de' => ['uninstall_return' => '', 'unwritten_overlay_modules' => ['pilot']],
        ]);

        $result = $this->createActivity($lng_objects, $obj_language_factory)->perform(['language_keys' => 'de']);

        $this->assertSame(['de'], $result['not_installed_language_keys']);
        $this->assertSame([], $result['overlay_write_failed_language_keys']);
    }

    /** A key without a "lng" object is not installed, and the object factory is never invoked for it. */
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
     * Languages in every state at once land in their own bucket; only the uninstalled one calls uninstall().
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

    /** Pins InvalidInputException, not just its parent \InvalidArgumentException. */
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
        // no mode parameter: the select field must never be built
        $field->expects($this->never())->method('select');

        $group = $this->createMock(\ILIAS\UI\Component\Input\Field\Section::class);
        $field->expects($this->once())
            ->method('section')
            ->with(['language_keys' => $text], $this->anything())
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
        $result = $this->createActivity(
            $lng_objects,
            $obj_language_factory,
            $rbac
        )->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, ['language_keys' => 'de']);

        $this->assertTrue($result->isError());
        $this->assertSame(0, $fakes[1]->uninstallCallCount());
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
        $this->assertSame(['de'], $result->value()['uninstalled_language_keys']);
        $this->assertSame(1, $fakes[1]->uninstallCallCount());
    }

    /** ilObjLanguageFolderGUI sends 'language_keys' as array of strings; the real grind() pipeline must join it. */
    public function testMaybePerformAsAcceptsAnArrayOfLanguageKeysAndUninstallsEachOne(): void
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
        $this->assertSame(['de', 'fr'], $result->value()['uninstalled_language_keys']);
        $this->assertSame(1, $fakes[1]->uninstallCallCount());
        $this->assertSame(1, $fakes[2]->uninstallCallCount());
    }
}
