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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\HTTP\Services as HttpServices;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\KeyValueStorage\Exception\InvalidStoredValueException;
use ILIAS\KeyValueStorage\Services as KeyValueStorage;
use ILIAS\KeyValueStorage\Store;
use ILIAS\Language\Tests\InMemoryKeyValueStorage;
use ILIAS\Language\Tests\InMemoryKeyValueStore;
use ILIAS\KeyValueStorage\Internal\StorageServices;
use ILIAS\KeyValueStorage\Internal\SubjectProviders;
use ILIAS\KeyValueStorage\SessionRepository;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Tests\KeyValueStorage\InMemoryRepository;
use ILIAS\Tests\KeyValueStorage\InMemorySubjectRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

require_once __DIR__ . '/InMemoryKeyValueStore.php';
require_once __DIR__ . '/InMemoryKeyValueStorage.php';
require_once __DIR__ . '/../../KeyValueStorage/tests/InMemoryRepository.php';
require_once __DIR__ . '/../../KeyValueStorage/tests/InMemorySubjectRepository.php';

class ilObjLanguageAccessTest extends ilLanguageBaseTestCase
{
    private InMemoryKeyValueStore $store;

    protected function setUp(): void
    {
        parent::setUp();

        $storage = new InMemoryKeyValueStorage();
        $this->store = $storage->session(['language', 'ext_maintenance']);
        $this->setGlobalVariable(KeyValueStorage::class, $storage);
        $this->setGlobalVariable('refinery', new RefineryFactory(new DataFactory(), $this->createStub(ilLanguage::class)));
    }

    private function setCachedCheckTranslate(bool $value): void
    {
        (new ReflectionProperty(ilObjLanguageAccess::class, 'cached_check_translate'))->setValue(null, $value);
        $this->setGlobalVariable('ilSetting', $this->createStub(ilSetting::class));
        $this->setGlobalVariable('ilUser', $this->createStub(ilObjUser::class));
        $this->setGlobalVariable('rbacsystem', $this->createStub(ilRbacSystem::class));
        $this->setGlobalVariable('rbac', $this->rbacOf($this->createStub(ilRbacSystem::class)));
    }

    private function storeThrowing(): void
    {
        $store = $this->createStub(Store::class);
        $store->method('get')->willThrowException(new InvalidStoredValueException('not valid JSON'));
        $storage = $this->createStub(KeyValueStorage::class);
        $storage->method('session')->willReturn($store);
        $this->setGlobalVariable(KeyValueStorage::class, $storage);
    }

    /** @return iterable<string, array{mixed}> */
    public static function nonListValues(): iterable
    {
        yield 'null' => [null];
        yield 'string' => ['a'];
        yield 'int' => [5];
        yield 'bool' => [true];
    }

    #[DataProvider('nonListValues')]
    public function testListsFallBackToEmptyOnWrongType(mixed $stored): void
    {
        $this->store->set('used_modules', $stored);
        $this->store->set('used_topics', $stored);

        self::assertSame([], ilObjLanguageAccess::_getSavedModules());
        self::assertSame([], ilObjLanguageAccess::_getSavedTopics());
    }

    public function testListsFallBackToEmptyOnInvalidStoredValue(): void
    {
        $this->storeThrowing();

        self::assertSame([], ilObjLanguageAccess::_getSavedModules());
        self::assertSame([], ilObjLanguageAccess::_getSavedTopics());
    }

    public function testIntegerTopicsAreReturnedAsStrings(): void
    {
        $this->store->set('used_topics', [123, 'abc', 4.0]);
        $this->store->set('used_modules', [7]);

        self::assertSame(['123', 'abc', '4'], ilObjLanguageAccess::_getSavedTopics());
        self::assertSame(['7'], ilObjLanguageAccess::_getSavedModules());
    }

    /**
     * @param array<int|string, int> $modules
     * @param array<int|string, int> $topics
     */
    private function givenCollectedUsages(array $modules, array $topics): void
    {
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('getUsedModules')->willReturn($modules);
        $lng->method('getUsedTopics')->willReturn($topics);
        $lng->method('getLangKey')->willReturn('en');
        $this->setGlobalVariable('lng', $lng);

        $query = $this->createStub(ArrayBasedRequestWrapper::class);
        $query->method('has')->willReturn(false);
        $wrapper = $this->createStub(WrapperFactory::class);
        $wrapper->method('query')->willReturn($query);
        $http = $this->createStub(HttpServices::class);
        $http->method('wrapper')->willReturn($wrapper);
        $this->setGlobalVariable('http', $http);
        $this->setCachedCheckTranslate(true);
    }

    /** Real storage service on an in-memory repository, so values take the JSON round trip. */
    private function useRealStorage(): void
    {
        $session = new class () extends InMemoryRepository implements SessionRepository {
        };
        $this->setGlobalVariable(KeyValueStorage::class, new StorageServices(
            $session,
            new InMemoryRepository(),
            new InMemorySubjectRepository(),
            new SubjectProviders([]),
            $GLOBALS['refinery']
        ));
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testSaveUsagesAndPresetsDoNotOverwriteEachOther(): void
    {
        $this->givenCollectedUsages(['common' => 1, 'lang' => 1], ['common#:#save' => 1, 123 => 1]);

        $this->store->set('import_mode_existing', 'keep');
        $this->store->set('export_scope', 'local');
        $this->store->set('maintain', 'remove');
        ilObjLanguageAccess::_saveUsages();

        self::assertSame(['common', 'lang'], ilObjLanguageAccess::_getSavedModules());
        self::assertSame(['common#:#save', '123'], ilObjLanguageAccess::_getSavedTopics());
        self::assertSame('keep', $this->store->values['import_mode_existing']);
        self::assertSame('local', $this->store->values['export_scope']);
        self::assertSame('remove', $this->store->values['maintain']);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testRealStorageKeepsPresetsNextToTheListsAndReturnsIntegerTopicsAsStrings(): void
    {
        $this->useRealStorage();
        $this->givenCollectedUsages(['common' => 1, 'lang' => 1], ['common#:#save' => 1, 123 => 1]);
        $store = $GLOBALS[KeyValueStorage::class]->session(ilObjLanguageAccess::MAINTENANCE_NAMESPACE);
        $store->set('export_scope', 'local');
        $store->set('maintain', 'clear');

        ilObjLanguageAccess::_saveUsages();
        $store->set('import_mode_existing', 'replace');

        self::assertSame(['common', 'lang'], ilObjLanguageAccess::_getSavedModules());
        self::assertSame(['common#:#save', '123'], ilObjLanguageAccess::_getSavedTopics());
        self::assertSame(
            ['export_scope', 'import_mode_existing', 'maintain', 'used_modules', 'used_topics'],
            $store->keys()
        );
        self::assertSame('local', $store->get('export_scope', $GLOBALS['refinery']->to()->string()));
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testSaveUsagesDropsInvalidUtf8EntriesInsteadOfThrowingAndKeepsTheValidOnes(): void
    {
        $this->useRealStorage();
        $this->givenCollectedUsages(
            ['common' => 1, "bad\xFF" => 1, 'lang' => 1],
            ['common#:#save' => 1, "common#:#bad\xFF" => 1, 7 => 1]
        );

        ilObjLanguageAccess::_saveUsages();

        self::assertSame(['common', 'lang'], ilObjLanguageAccess::_getSavedModules());
        self::assertSame(['common#:#save', '7'], ilObjLanguageAccess::_getSavedTopics());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testSaveUsagesWritesNothingWithoutTranslatePermission(): void
    {
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('getUsedModules')->willReturn(['common' => 1]);
        $this->setGlobalVariable('lng', $lng);
        $this->setCachedCheckTranslate(false);

        ilObjLanguageAccess::_saveUsages();

        self::assertSame([], $this->store->values);
    }
}
