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

namespace ILIAS\Tests\KeyValueStorage\Internal;

use ILIAS\KeyValueStorage\Exception\InvalidStoredValueException;
use ILIAS\KeyValueStorage\Internal\KeyRules;
use ILIAS\KeyValueStorage\Internal\NamespacedStore;
use ILIAS\KeyValueStorage\Internal\Values;
use ILIAS\KeyValueStorage\Internal\StorageNamespace;
use ILIAS\Tests\KeyValueStorage\InMemoryRepository;
use ILIAS\Tests\KeyValueStorage\RefineryHelper;
use PHPUnit\Framework\TestCase;

class NamespacedStoreTest extends TestCase
{
    use RefineryHelper;

    private InMemoryRepository $repository;

    private StorageNamespace $namespace;

    private NamespacedStore $store;

    protected function setUp(): void
    {
        $this->repository = new InMemoryRepository();
        $this->namespace = new StorageNamespace(['my_component', 'view_state']);
        $this->store = $this->storeFor($this->namespace);
    }

    private function storeFor(StorageNamespace $namespace): NamespacedStore
    {
        return new NamespacedStore($namespace, $this->repository, new KeyRules(), new Values($this->refinery()));
    }

    public function testValuesAreStoredEncodedAndReadBackDecoded(): void
    {
        $this->store->set('filters', ['status' => 'open', 'limit' => 10]);

        $this->assertSame(
            '{"status":"open","limit":10}',
            $this->repository->entries['my_component.view_state']['filters']
        );

        $this->assertSame(
            ['status' => 'open', 'limit' => 10],
            $this->storeFor($this->namespace)->get('filters', $this->asStored())
        );
    }

    public function testAbsentKeysArePassedToTheTransformationAsNull(): void
    {
        $this->assertNull($this->store->get('absent', $this->asStored()));
        $this->assertSame(
            'fallback',
            $this->store->get('absent', $this->withDefault('fallback'))
        );
        $this->assertFalse($this->store->has('absent'));
    }

    public function testAStoredNullIsDistinguishedFromAnAbsentKeyViaHas(): void
    {
        $this->store->set('maybe', null);

        $this->assertTrue($this->store->has('maybe'));
        $this->assertNull($this->store->get('maybe', $this->asStored()));
        $this->assertFalse($this->store->has('absent'));
    }

    public function testDeleteRemovesASingleKey(): void
    {
        $this->store->set('a', 1);
        $this->store->set('b', 2);

        $this->store->delete('a');

        $this->assertFalse($this->store->has('a'));
        $this->assertTrue($this->store->has('b'));
    }

    public function testClearRemovesOnlyTheOwnNamespace(): void
    {
        $other = $this->storeFor(new StorageNamespace(['my_component', 'view_state', 'details']));
        $this->store->set('a', 1);
        $other->set('a', 2);

        $this->store->clear();

        $this->assertFalse($this->store->has('a'));
        $this->assertTrue($other->has('a'));
    }

    public function testReadingTheSameKeyTwiceHitsTheRepositoryOnce(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';

        $this->assertSame('title', $this->store->get('sort', $this->asStored()));
        $this->assertSame('title', $this->store->get('sort', $this->asStored()));

        $this->assertSame(1, $this->repository->reads);
    }

    public function testAnAbsentKeyIsOnlyLookedUpOnce(): void
    {
        $this->store->get('absent', $this->asStored());
        $this->store->get('absent', $this->asStored());

        $this->assertSame(1, $this->repository->reads);
    }

    public function testAKnownAbsentKeyStillYieldsTheTransformedDefault(): void
    {
        $this->store->get('absent', $this->asStored());

        $this->assertSame(
            'fallback',
            $this->store->get('absent', $this->withDefault('fallback'))
        );
        $this->assertSame(1, $this->repository->reads);
    }

    public function testHasAnswersFromWhatWasAlreadyRead(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';
        $this->store->get('sort', $this->asStored());

        $this->assertTrue($this->store->has('sort'));
        $this->assertSame(1, $this->repository->reads);
    }

    public function testAWriteIsVisibleWithoutReadingTheRepositoryAgain(): void
    {
        $this->store->set('sort', 'title');

        $this->assertSame('title', $this->store->get('sort', $this->asStored()));
        $this->assertSame(0, $this->repository->reads);
    }

    public function testAJsonSerializableReadsBackAsItsJsonFormWithinTheSameRequest(): void
    {
        $this->store->set('value', new class () implements \JsonSerializable {
            /**
             * @return array{a: int}
             */
            public function jsonSerialize(): array
            {
                return ['a' => 1];
            }
        });

        $this->assertSame(['a' => 1], $this->store->get('value', $this->asStored()));
    }

    public function testEveryOperationValidatesTheKey(): void
    {
        try {
            $this->store->has('in:valid');
            $this->fail('has() accepted an invalid key.');
        } catch (\InvalidArgumentException) {
            $this->addToAssertionCount(1);
        }

        try {
            $this->store->get('in:valid', $this->asStored());
            $this->fail('get() accepted an invalid key.');
        } catch (\InvalidArgumentException) {
            $this->addToAssertionCount(1);
        }

        try {
            $this->store->delete('in:valid');
            $this->fail('delete() accepted an invalid key.');
        } catch (\InvalidArgumentException) {
            $this->addToAssertionCount(1);
        }

        try {
            $this->store->getMany(['in:valid' => $this->asStored()]);
            $this->fail('getMany() accepted an invalid key.');
        } catch (\InvalidArgumentException) {
            $this->addToAssertionCount(1);
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->store->set('in:valid', 1);
    }

    public function testAnInvalidKeyIsRejectedEvenWhenTheValueIsAlreadyKnown(): void
    {
        $this->repository->entries['my_component.view_state']['in:valid'] = '1';

        $this->expectException(\InvalidArgumentException::class);
        $this->store->get('in:valid', $this->asStored());
    }

    public function testKeysListsEveryPresentKeySorted(): void
    {
        $this->repository->entries['my_component.view_state'] = [
            'sort' => '"title"',
            'filters' => '{"status":"open"}',
            '10' => '1',
            'none' => 'null',
        ];

        $this->assertSame(['10', 'filters', 'none', 'sort'], $this->store->keys());
    }

    public function testKeysOmitsDeletedAndForeignEntries(): void
    {
        $this->store->set('keep', 1);
        $this->store->set('gone', 2);
        $this->store->delete('gone');
        $this->repository->entries['my_component.view_state.details']['keep'] = '3';
        $this->repository->entries['other_component']['keep'] = '4';

        $this->assertSame(['keep'], $this->store->keys());
    }

    public function testKeysOfAnEmptyNamespaceDoesNotLookKeysUpIndividually(): void
    {
        $this->assertSame([], $this->store->keys());
        $this->assertFalse($this->store->has('missing'));
        $this->assertNull($this->store->get('missing', $this->asStored()));

        $this->assertSame(1, $this->repository->bulk_reads);
        $this->assertSame(0, $this->repository->reads);
        $this->assertSame(0, $this->repository->has_calls);
    }

    public function testKeysLoadsTheNamespaceOnce(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';

        $this->assertSame(['sort'], $this->store->keys());
        $this->assertSame(['sort'], $this->store->keys());
        $this->assertSame('title', $this->store->get('sort', $this->asStored()));
        $this->assertTrue($this->store->has('sort'));
        $this->assertFalse($this->store->has('missing'));

        $this->assertSame(1, $this->repository->bulk_reads);
        $this->assertSame(0, $this->repository->reads);
        $this->assertSame(0, $this->repository->has_calls);
    }

    public function testKeysPrefersWhatThisStoreAlreadyKnows(): void
    {
        $this->store->set('sort', 'title');
        $this->store->set('limit', 5);
        $this->store->delete('limit');
        $this->repository->entries['my_component.view_state']['sort'] = '"stale"';
        $this->repository->entries['my_component.view_state']['limit'] = '5';
        $this->repository->entries['my_component.view_state']['extra'] = '"from-repository"';

        $this->assertSame(['extra', 'sort'], $this->store->keys());
        $this->assertSame('title', $this->store->get('sort', $this->asStored()));
        $this->assertSame(1, $this->repository->bulk_reads);
        $this->assertSame(0, $this->repository->reads);
    }

    public function testGetManyAppliesATransformationPerNamedKey(): void
    {
        $this->store->set('sort', 'title');
        $this->store->set('limit', 10);
        $this->store->set('none', null);
        $this->repository->entries['my_component.view_state']['extra'] = '"from-repository"';
        $as_string = $this->refinery()->custom()->transformation(
            static fn(mixed $value): string => $value === null ? 'fallback' : (string) $value
        );

        $this->assertSame(
            ['sort' => 'title', 'limit' => '10', 'absent' => 'fallback', 'none' => 'fallback'],
            $this->store->getMany([
                'sort' => $this->asStored(),
                'limit' => $as_string,
                'absent' => $this->withDefault('fallback'),
                'none' => $as_string,
            ])
        );
    }

    public function testGetManyDoesNotReturnKeysThatWereNotAskedFor(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';
        $this->repository->entries['my_component.view_state']['extra'] = '"from-repository"';

        $this->assertSame(
            ['sort' => 'title'],
            $this->store->getMany(['sort' => $this->asStored()])
        );
    }

    public function testGetManyOfAnEmptyMapDoesNotReadTheRepository(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';

        $this->assertSame([], $this->store->getMany([]));
        $this->assertSame(0, $this->repository->bulk_reads);
        $this->assertSame(0, $this->repository->reads);
    }

    public function testGetManyDoesNotReadKeysThisStoreAlreadyKnows(): void
    {
        $this->store->set('sort', 'title');
        $this->store->set('limit', 10);

        $this->assertSame(
            ['sort' => 'title', 'limit' => 10],
            $this->store->getMany([
                'sort' => $this->asStored(),
                'limit' => $this->asStored(),
            ])
        );
        $this->assertSame(0, $this->repository->bulk_reads);
        $this->assertSame(0, $this->repository->reads);
    }

    public function testGetManyLoadsTheNamespaceOnceWhenAKeyIsUnknown(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';
        $this->repository->entries['my_component.view_state']['limit'] = '10';

        $this->assertSame(
            ['sort' => 'title', 'missing' => 'fallback'],
            $this->store->getMany([
                'sort' => $this->asStored(),
                'missing' => $this->withDefault('fallback'),
            ])
        );
        $this->assertSame('title', $this->store->get('sort', $this->asStored()));
        $this->assertSame(10, $this->store->get('limit', $this->asStored()));
        $this->assertFalse($this->store->has('missing'));
        $this->assertSame(['limit', 'sort'], $this->store->keys());

        $this->assertSame(1, $this->repository->bulk_reads);
        $this->assertSame(0, $this->repository->reads);
        $this->assertSame(0, $this->repository->has_calls);
    }

    public function testGetManyRemembersTheStoredValueNotTheTransformedOne(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';
        $upper = $this->refinery()->custom()->transformation(
            static fn(mixed $value): string => \strtoupper((string) $value)
        );

        $this->assertSame(['sort' => 'TITLE'], $this->store->getMany(['sort' => $upper]));
        $this->assertSame('title', $this->store->get('sort', $this->asStored()));
        $this->assertSame(1, $this->repository->bulk_reads);
        $this->assertSame(0, $this->repository->reads);
    }

    public function testGetManyRejectsANonTransformationEntryWithoutReading(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';

        try {
            $this->store->getMany(['sort' => 'not-a-transformation']);
            $this->fail('getMany() accepted a value that is not a transformation.');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame(
                'Each getMany() entry must be a ILIAS\Refinery\Transformation, got string for key "sort".',
                $e->getMessage()
            );
        }

        $this->assertSame(0, $this->repository->bulk_reads);
        $this->assertSame(0, $this->repository->reads);
    }

    public function testAnInvalidKeyInGetManyDoesNotReadTheRepository(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';

        try {
            $this->store->getMany([
                'sort' => $this->asStored(),
                'in:valid' => $this->asStored(),
            ]);
            $this->fail('getMany() accepted an invalid key.');
        } catch (\InvalidArgumentException) {
            $this->addToAssertionCount(1);
        }

        $this->assertSame(0, $this->repository->bulk_reads);
        $this->assertSame(0, $this->repository->reads);
    }

    public function testGetManyRejectsANamespaceThatContainsAnUndecodableValue(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';
        $this->repository->entries['my_component.view_state']['broken'] = '{';

        try {
            $this->store->getMany(['sort' => $this->asStored()]);
            $this->fail('getMany() accepted an undecodable value.');
        } catch (InvalidStoredValueException) {
            $this->addToAssertionCount(1);
        }

        $this->assertSame('title', $this->store->get('sort', $this->asStored()));
        $this->assertSame(1, $this->repository->bulk_reads);
        $this->assertSame(1, $this->repository->reads);
    }

    public function testAFailingGetManyTransformationDoesNotReloadTheNamespace(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';
        $failing = $this->refinery()->custom()->transformation(
            static function (mixed $value): mixed {
                throw new \RuntimeException('nope: ' . \get_debug_type($value));
            }
        );

        try {
            $this->store->getMany(['sort' => $failing]);
            $this->fail('getMany() swallowed a failing transformation.');
        } catch (\RuntimeException $e) {
            $this->assertSame('nope: string', $e->getMessage());
        }

        $this->assertSame(['sort' => 'title'], $this->store->getMany(['sort' => $this->asStored()]));
        $this->assertSame(1, $this->repository->bulk_reads);
    }

    public function testClearForgetsTheLoadedNamespace(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';
        $this->store->keys();

        $this->store->clear();
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';

        $this->assertSame(['sort'], $this->store->keys());
        $this->assertSame(2, $this->repository->bulk_reads);
    }

    public function testADecimalKeyStaysReadableAfterKeys(): void
    {
        $this->repository->entries['my_component.view_state']['10'] = '"title"';

        $this->assertSame(['10'], $this->store->keys());
        $this->assertSame(
            ['10' => 'title'],
            $this->store->getMany(['10' => $this->asStored()])
        );
        $this->assertSame('title', $this->store->get('10', $this->asStored()));
        $this->assertTrue($this->store->has('10'));
        $this->assertSame(0, $this->repository->reads);
        $this->assertSame(0, $this->repository->has_calls);
    }
}
