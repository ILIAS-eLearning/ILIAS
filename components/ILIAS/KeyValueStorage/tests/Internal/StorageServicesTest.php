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

use ILIAS\KeyValueStorage\Internal\StorageServices;
use ILIAS\KeyValueStorage\Internal\SubjectProviders;
use ILIAS\KeyValueStorage\SessionRepository;
use ILIAS\KeyValueStorage\Subject\Subject;
use ILIAS\KeyValueStorage\Subject\SubjectId;
use ILIAS\KeyValueStorage\Subject\SubjectResolver;
use ILIAS\Tests\KeyValueStorage\InMemoryRepository;
use ILIAS\Tests\KeyValueStorage\InMemorySubjectRepository;
use ILIAS\Tests\KeyValueStorage\NamedSubjectProvider;
use ILIAS\Tests\KeyValueStorage\RefineryHelper;
use PHPUnit\Framework\TestCase;

class StorageServicesTest extends TestCase
{
    use RefineryHelper;

    private InMemoryRepository $session;

    private InMemoryRepository $persistent;

    private InMemorySubjectRepository $subjects;

    private StorageServices $services;

    protected function setUp(): void
    {
        $this->session = new class () extends InMemoryRepository implements SessionRepository {
        };
        $this->persistent = new InMemoryRepository();
        $this->subjects = new InMemorySubjectRepository();
        $this->services = new StorageServices(
            $this->session,
            $this->persistent,
            $this->subjects,
            new SubjectProviders([new NamedSubjectProvider('test'), new NamedSubjectProvider('other')]),
            $this->refinery()
        );
    }

    public function testTheScopesUseSeparateRepositories(): void
    {
        $this->services->session(['ui', 'storage'])->set('a', 1);
        $this->services->persistent(['ui', 'storage'])->set('b', 2);

        $this->assertSame(['a' => '1'], $this->session->entries['ui.storage']);
        $this->assertSame(['b' => '2'], $this->persistent->entries['ui.storage']);
    }

    public function testTheSameScopeAndNamespaceYieldTheSameStore(): void
    {
        $this->assertSame(
            $this->services->session(['ui', 'storage']),
            $this->services->session(['ui', 'storage'])
        );
    }

    public function testDifferentScopesYieldDifferentStoresForTheSameNamespace(): void
    {
        $this->assertNotSame(
            $this->services->session(['ui', 'storage']),
            $this->services->persistent(['ui', 'storage'])
        );
    }

    public function testDifferentNamespacesYieldDifferentStores(): void
    {
        $this->assertNotSame(
            $this->services->session(['ui', 'storage']),
            $this->services->session(['ui', 'table'])
        );
    }

    public function testTheSharedStoreKeepsWhatWasWrittenThroughIt(): void
    {
        $this->services->session(['ui', 'storage'])->set('sort', 'title');

        $this->assertSame(
            'title',
            $this->services->session(['ui', 'storage'])->get('sort', $this->asStored())
        );
        $this->assertSame(0, $this->session->reads);
    }

    public function testKeysAndGetManyGoThroughTheSameStore(): void
    {
        $this->services->persistent(['ui', 'storage'])->set('sort', 'title');
        $this->services->persistent(['ui', 'storage'])->set('limit', 10);
        $this->persistent->entries['ui.storage']['extra'] = '"from-repository"';

        $this->assertSame(
            ['extra', 'limit', 'sort'],
            $this->services->persistent(['ui', 'storage'])->keys()
        );
        $this->assertSame(
            ['sort' => 'title', 'limit' => 10, 'missing' => 'id'],
            $this->services->persistent(['ui', 'storage'])->getMany([
                'sort' => $this->asStored(),
                'limit' => $this->asStored(),
                'missing' => $this->withDefault('id'),
            ])
        );
        $this->assertSame(1, $this->persistent->bulk_reads);
        $this->assertSame(0, $this->persistent->reads);
    }

    public function testPersistentForWritesOnlyTheNamedSubject(): void
    {
        $this->services->persistent(['ui', 'storage'])->set('sort', 'global');
        $this->services->persistentFor($this->named('42'), ['ui', 'storage'])->set('sort', 'mine');
        $this->services->persistentFor($this->named('7'), ['ui', 'storage'])->set('sort', 'theirs');

        $this->assertSame(['sort' => '"global"'], $this->persistent->entries['ui.storage']);
        $this->assertSame(['sort' => '"mine"'], $this->subjects->entries['test:42']['ui.storage']);
        $this->assertSame(['sort' => '"theirs"'], $this->subjects->entries['test:7']['ui.storage']);
        $this->assertSame(
            'mine',
            $this->services->persistentFor($this->named('42'), ['ui', 'storage'])->get('sort', $this->asStored())
        );
    }

    public function testTheSameSubjectAndNamespaceYieldTheSameStore(): void
    {
        $this->assertSame(
            $this->services->persistentFor($this->named('42'), ['ui', 'storage']),
            $this->services->persistentFor($this->named('42'), ['ui', 'storage'])
        );
    }

    public function testDifferentSubjectsYieldDifferentStores(): void
    {
        $this->assertNotSame(
            $this->services->persistentFor($this->named('42'), ['ui', 'storage']),
            $this->services->persistentFor($this->named('7'), ['ui', 'storage'])
        );
    }

    public function testAnAnonymousSubjectCannotBePersisted(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Persistent subject storage requires a named subject.');

        $this->services->persistentFor($this->anonymous(), ['ui', 'storage']);
    }

    public function testAnAnonymousSubjectDoesNotWrite(): void
    {
        $rejected = false;
        try {
            $this->services->persistentFor($this->anonymous(), ['ui', 'storage']);
        } catch (\InvalidArgumentException $exception) {
            $rejected = $exception->getMessage() === 'Persistent subject storage requires a named subject.';
        }

        $this->assertTrue($rejected);
        $this->assertSame([], $this->subjects->entries);
        $this->assertSame([], $this->persistent->entries);
    }

    public function testPurgeRemovesOneSubjectAcrossNamespacesAndLeavesTheRest(): void
    {
        $this->services->persistent(['ui', 'storage'])->set('sort', 'global');
        $this->services->persistentFor($this->named('42'), ['ui', 'storage'])->set('sort', 'mine');
        $this->services->persistentFor($this->named('42'), ['export', 'job'])->set('step', 2);
        $this->services->persistentFor($this->named('7'), ['ui', 'storage'])->set('sort', 'theirs');

        $this->services->purgeSubject((new NamedSubjectProvider('test'))->subject('42'));

        $this->assertSame(['sort' => '"global"'], $this->persistent->entries['ui.storage']);
        $this->assertArrayNotHasKey('test:42', $this->subjects->entries);
        $this->assertSame(['sort' => '"theirs"'], $this->subjects->entries['test:7']['ui.storage']);
    }

    public function testAStoreDoesNotAnswerFromBeforeThePurgeOfItsSubject(): void
    {
        $this->services->persistentFor($this->named('42'), ['ui', 'storage'])->set('sort', 'mine');
        $this->services->persistentFor($this->named('4'), ['ui', 'storage'])->set('sort', 'other');

        $this->services->purgeSubject((new NamedSubjectProvider('test'))->subject('42'));

        $store = $this->services->persistentFor($this->named('42'), ['ui', 'storage']);
        $this->assertNull($store->get('sort', $this->refinery()->identity()));
        $store->set('sort', 'mine');
        $this->assertSame(['sort' => '"mine"'], $this->subjects->entries['test:42']['ui.storage']);
        $this->assertSame(['sort' => '"other"'], $this->subjects->entries['test:4']['ui.storage']);
    }

    public function testSubjectsOfDifferentProvidersDoNotShareValues(): void
    {
        $this->services->persistentFor($this->named('42', 'test'), ['ui', 'storage'])->set('sort', 'mine');
        $this->services->persistentFor($this->named('42', 'other'), ['ui', 'storage'])->set('sort', 'theirs');

        $this->assertSame(['sort' => '"mine"'], $this->subjects->entries['test:42']['ui.storage']);
        $this->assertSame(['sort' => '"theirs"'], $this->subjects->entries['other:42']['ui.storage']);
    }

    public function testPurgeDoesNotReachTheSameIdOfAnotherProvider(): void
    {
        $this->services->persistentFor($this->named('42', 'test'), ['ui', 'storage'])->set('sort', 'mine');
        $this->services->persistentFor($this->named('42', 'other'), ['ui', 'storage'])->set('sort', 'theirs');

        $this->services->purgeSubject((new NamedSubjectProvider('test'))->subject('42'));

        $this->assertArrayNotHasKey('test:42', $this->subjects->entries);
        $this->assertSame(['sort' => '"theirs"'], $this->subjects->entries['other:42']['ui.storage']);
    }

    public function testASubjectOfAnUnregisteredProviderCannotBePersisted(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The subject provider "unknown" is not registered');

        $this->services->persistentFor($this->named('42', 'unknown'), ['ui', 'storage']);
    }

    public function testASubjectOfAnUnregisteredProviderCannotBePurged(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The subject provider "unknown" is not registered');

        $this->services->purgeSubject((new NamedSubjectProvider('unknown'))->subject('42'));
    }

    public function testAProviderCannotUseTheNameOfAnotherOne(): void
    {
        $impostor = new ImpostorSubjectProvider();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The subject provider "test" is registered as ' . NamedSubjectProvider::class);

        $this->services->persistentFor($this->resolverFor(new SubjectId($impostor, '42')), ['ui', 'storage']);
    }

    private function named(string $id, string $provider = 'test'): SubjectResolver
    {
        return $this->resolverFor((new NamedSubjectProvider($provider))->subject($id));
    }

    private function resolverFor(SubjectId $id): SubjectResolver
    {
        return new class (Subject::named($id)) implements SubjectResolver {
            public function __construct(private readonly Subject $subject)
            {
            }

            public function subject(): Subject
            {
                return $this->subject;
            }
        };
    }

    private function anonymous(): SubjectResolver
    {
        return new class () implements SubjectResolver {
            public function subject(): Subject
            {
                return Subject::anonymous();
            }
        };
    }
}
