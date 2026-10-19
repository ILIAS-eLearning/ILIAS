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
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * UninstallLanguage works on language objects behind a mocked RepositoryProvider, answering from small fake
 * objects (\ilObjLanguage needs the global $DIC) via LanguageObjectRepositoryProvider.
 */
class UninstallLanguageTest extends ActivityWithPerformResultContractTestCase
{
    use LanguageObjectRepositoryProvider;

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

    /** The ambiguity check covers all requested keys before any uninstall: 'de' must stay installed when 'fr' is ambiguous. */
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
            // 'de' first on purpose
            $activity->perform(['language_keys' => 'de,fr']);
            $this->fail('Expected an AmbiguousLanguageTitleException to be thrown.');
        } catch (AmbiguousLanguageTitleException $e) {
            $this->assertStringContainsString('"fr"', $e->getMessage());
            // 'de' was never resolved, the check ran before the per-key loop
            $this->assertSame([], $factory_calls);
            $this->assertSame(0, $de->uninstallCallCount());
        }
    }

    /** Same, end-to-end through maybePerformAs(). */
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
     * @param array<string, array{system?: bool, user?: bool, installed?: bool, uninstall_return?: string}> $language_objects keyed by language key
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

    /** ilObjLanguageFolderGUI sends 'language_keys' as array of strings; the real grind() pipeline must join it. */
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
            $language ?? $this->createMock(Language::class),
            $this->rbacOf($rbac ?? $this->createMock(\ilRbacSystem::class)),
            $this->languageFolder(),
            $this->languageObjectProvider($lng_objects, $obj_language_factory)
        );
    }
}

/**
 * Stand-in for \ilObjLanguage (see above); counts uninstall() calls.
 */
final class FakeLanguageObject
{
    private int $uninstall_calls = 0;

    /**
     * @param string $uninstall_return_value '' mimics uninstall() rejecting the call despite the outer guards
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
