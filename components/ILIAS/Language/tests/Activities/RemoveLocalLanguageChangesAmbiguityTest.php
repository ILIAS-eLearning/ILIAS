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

use ILIAS\Language\Activities\AmbiguousLanguageTitleException;
use ILIAS\Language\Activities\InvalidInputException;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Language;

class RemoveLocalLanguageChangesAmbiguityTest extends \ilLanguageBaseTestCase
{
    use RealFieldsUiFactory;
    use RemoveLocalLanguageChangesTestFixture;

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
     * The exception message embeds the ambiguous title unescaped: escaping is
     * RendersActivityErrors::activityErrorMessage()'s job.
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
     * Via maybePerformAs() the exception must surface as a Result\Error, and the object factory stays untouched.
     */
    public function testAmbiguousTitleViaMaybePerformAsReturnsResultErrorAndNeverCallsTheObjectFactory(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
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
     * An ambiguous title must not affect a unique language key in the same request.
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
     * The ambiguity check runs for all requested keys before any removeLocalChanges() call.
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
            // 'de' is listed before the ambiguous 'fr' on purpose
            $activity->perform(['language_keys' => 'de,fr']);
            $this->fail('Expected an AmbiguousLanguageTitleException to be thrown.');
        } catch (AmbiguousLanguageTitleException $e) {
            $this->assertStringContainsString('"fr"', $e->getMessage());
            // 'de' was never resolved, let alone changed
            $this->assertSame([], $factory_calls);
            $this->assertSame(0, $de->removeLocalChangesCallCount());
        }
    }

    /**
     * Same, end-to-end through maybePerformAs().
     */
    public function testMaybePerformAsNeverRemovesLocalChangesForAnUnambiguousKeyWhenAnotherRequestedKeyIsAmbiguous(): void
    {
        $rbac = $this->createStub(\ilRbacSystem::class);
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
}
