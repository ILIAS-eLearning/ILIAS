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

use ILIAS\KeyValueStorage\Internal\KeyRules;
use ILIAS\KeyValueStorage\Internal\NamespacedStore;
use ILIAS\KeyValueStorage\Internal\Values;
use ILIAS\KeyValueStorage\StorageNamespace;
use ILIAS\Tests\KeyValueStorage\InMemoryRepository;
use PHPUnit\Framework\TestCase;

class NamespacedStoreTest extends TestCase
{
    private InMemoryRepository $repository;

    private StorageNamespace $namespace;

    private NamespacedStore $store;

    protected function setUp(): void
    {
        $this->repository = new InMemoryRepository();
        $this->namespace = new StorageNamespace('my_component.view_state');
        $this->store = $this->storeFor($this->namespace);
    }

    private function storeFor(StorageNamespace $namespace): NamespacedStore
    {
        return new NamespacedStore($namespace, $this->repository, new KeyRules(), new Values());
    }

    public function testValuesAreStoredEncodedAndReadBackDecoded(): void
    {
        $this->store->set('filters', ['status' => 'open', 'limit' => 10]);

        $this->assertSame(
            '{"status":"open","limit":10}',
            $this->repository->entries['my_component.view_state']['filters']
        );

        $this->assertSame(['status' => 'open', 'limit' => 10], $this->storeFor($this->namespace)->get('filters'));
    }

    public function testAbsentKeysYieldTheDefault(): void
    {
        $this->assertNull($this->store->get('absent'));
        $this->assertSame('fallback', $this->store->get('absent', 'fallback'));
        $this->assertFalse($this->store->has('absent'));
    }

    public function testAStoredNullIsDistinguishedFromAnAbsentKey(): void
    {
        $this->store->set('maybe', null);

        $this->assertTrue($this->store->has('maybe'));
        $this->assertNull($this->store->get('maybe', 'fallback'));
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
        $other = $this->storeFor(new StorageNamespace('my_component.view_state.details'));
        $this->store->set('a', 1);
        $other->set('a', 2);

        $this->store->clear();

        $this->assertFalse($this->store->has('a'));
        $this->assertTrue($other->has('a'));
    }

    public function testReadingTheSameKeyTwiceHitsTheRepositoryOnce(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';

        $this->assertSame('title', $this->store->get('sort'));
        $this->assertSame('title', $this->store->get('sort'));

        $this->assertSame(1, $this->repository->reads);
    }

    public function testAnAbsentKeyIsOnlyLookedUpOnce(): void
    {
        $this->store->get('absent');
        $this->store->get('absent');

        $this->assertSame(1, $this->repository->reads);
    }

    public function testAKnownAbsentKeyStillYieldsTheDefault(): void
    {
        $this->store->get('absent');

        $this->assertSame('fallback', $this->store->get('absent', 'fallback'));
        $this->assertSame(1, $this->repository->reads);
    }

    public function testHasAnswersFromWhatWasAlreadyRead(): void
    {
        $this->repository->entries['my_component.view_state']['sort'] = '"title"';
        $this->store->get('sort');

        $this->assertTrue($this->store->has('sort'));
        $this->assertSame(1, $this->repository->reads);
    }

    public function testAWriteIsVisibleWithoutReadingTheRepositoryAgain(): void
    {
        $this->store->set('sort', 'title');

        $this->assertSame('title', $this->store->get('sort'));
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

        $this->assertSame(['a' => 1], $this->store->get('value'));
    }

    public function testEveryOperationValidatesTheKey(): void
    {
        foreach (['has', 'get', 'delete'] as $method) {
            try {
                $this->store->{$method}('in:valid');
                $this->fail($method . '() accepted an invalid key.');
            } catch (\InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->store->set('in:valid', 1);
    }

    public function testAnInvalidKeyIsRejectedEvenWhenTheValueIsAlreadyKnown(): void
    {
        $this->repository->entries['my_component.view_state']['in:valid'] = '1';

        $this->expectException(\InvalidArgumentException::class);
        $this->store->get('in:valid');
    }
}
