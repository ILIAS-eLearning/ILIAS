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

use ILIAS\Language as LanguageComponent;
use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\UpdateLanguage;
use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Activities\AddLanguageEntry;
use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\Language\Activities\SetLanguageDetectionEnabled;
use ILIAS\Language\Activities\SetLanguageTranslationEnabled;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\LanguageLegacyInitialisationAdapter;
use ILIAS\Language\Setup\InstalledLanguageDatabaseRepository;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\Language\Setup\LanguageInstallationManager;
use ILIAS\Language\UserSettings\Settings as UserSettingsSettings;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Component\Dependencies\OutType;
use ILIAS\Component\Dependencies\Reader;
use ILIAS\Component\Dependencies\RenamingDIC;
use Pimple\Container as PimpleContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Wiring of Language.php::init() through container types that mirror the generated bootstrap (Pimple\Container,
 * RenamingDIC for $implement/$contribute), so regressions that only show with a real lazily-evaluated container are caught.
 *
 * $use is seeded only with what init() reads: a mock nobody asserts against would hide a reappearing $ui_factory dependency.
 */
class LanguageComponentGraphTest extends TestCase
{
    private PimpleContainer $contributions;

    /**
     * @param array<string, mixed>|\ArrayAccess<string, mixed>|null $use
     * @return array{0: array, 1: RenamingDIC, 2: array, 3: RenamingDIC, 4: array, 5: PimpleContainer, 6: array, 7: PimpleContainer}
     */
    private function initComponent(array | \ArrayAccess | null $use = null, ?\ILIAS\Database\Connection $connection = null): array
    {
        $define = [];
        $use ??= [
            \ILIAS\Language\Language::class => $this->createStub(\ILIAS\Language\Language::class),
            \ILIAS\AccessControl\PublicInterface\RBAC::class => $this->createStub(\ILIAS\AccessControl\PublicInterface\RBAC::class),
        ];
        $use[\ILIAS\Environment\Configuration\Installation\Directories::class] ??= $this->createDirectories(
            (string) realpath(__DIR__ . '/../../../../')
        );
        $use[\ILIAS\Environment\Configuration\Installation\ClientIdProvider::class] ??= $this->createClientIdProvider('default');
        $use[\ILIAS\Logging\Logger\LoggerFactoryInterface::class] ??= $this->createStub(
            \ILIAS\Logging\Logger\LoggerFactoryInterface::class
        );
        $seek = [
            LanguageFileDirectory::class => [],
        ];
        $pull = [
            RefineryFactory::class => $this->createStub(RefineryFactory::class),
            \ILIAS\Database\Connection::class => $connection ?? $this->createStub(\ILIAS\Database\Connection::class),
        ];

        // RenamingDIC (as in the generated bootstrap) keeps several assignments to one key apart; a plain Pimple\Container overwrites
        $implement = new RenamingDIC(new PimpleContainer());
        $this->contributions = new PimpleContainer();
        $contribute = new RenamingDIC($this->contributions);
        $provide = new PimpleContainer();
        $internal = new PimpleContainer();

        (new LanguageComponent())->init(
            $define,
            $implement,
            $use,
            $contribute,
            $seek,
            $provide,
            $pull,
            $internal
        );

        return [$define, $implement, $use, $contribute, $seek, $provide, $pull, $internal];
    }

    /**
     * All instances contributed for $type, independent of the position of the contribution in init().
     *
     * @return list<object>
     */
    private function contributionsOf(string $type): array
    {
        $contributions = [];
        foreach ($this->contributions->keys() as $key) {
            if (preg_match('/^' . preg_quote($type, '/') . '_\\d+$/', $key) === 1) {
                $contributions[] = $this->contributions[$key];
            }
        }

        return $contributions;
    }

    private function contributedSetupAgent(): \ilLanguageSetupAgent
    {
        $agents = $this->contributionsOf(\ILIAS\Setup\Agent::class);
        self::assertCount(1, $agents);
        self::assertInstanceOf(\ilLanguageSetupAgent::class, $agents[0]);

        return $agents[0];
    }

    public function testDefinesTheLanguageInterface(): void
    {
        [$define] = $this->initComponent();

        self::assertSame([\ILIAS\Language\Language::class], $define);
    }

    public function testProvideEntriesResolveToExpectedConcreteClasses(): void
    {
        [, , , , , $provide] = $this->initComponent();

        self::assertInstanceOf(LanguageFileDirectoryManager::class, $provide[LanguageFileDirectoryManager::class]);
        self::assertFalse(isset($provide[InstalledLanguageRepository::class]));
        self::assertFalse(isset($provide[LanguageInstallationManager::class]));
        self::assertInstanceOf(InstallLanguage::class, $provide[InstallLanguage::class]);
        self::assertInstanceOf(UpdateLanguage::class, $provide[UpdateLanguage::class]);
        self::assertInstanceOf(UninstallLanguage::class, $provide[UninstallLanguage::class]);
        self::assertInstanceOf(RemoveLocalLanguageChanges::class, $provide[RemoveLocalLanguageChanges::class]);
        self::assertInstanceOf(AddLanguageEntry::class, $provide[AddLanguageEntry::class]);
        self::assertInstanceOf(SetLanguageDetectionEnabled::class, $provide[SetLanguageDetectionEnabled::class]);
        self::assertInstanceOf(SetLanguageTranslationEnabled::class, $provide[SetLanguageTranslationEnabled::class]);
    }

    public function testProvidedServicesShareTheSameInternalSingletonInstances(): void
    {
        [, , , , , $provide, , $internal] = $this->initComponent();

        // $provide[...] re-exports $internal[...]: both must be the same object
        self::assertSame($internal[LanguageFileDirectoryManager::class], $provide[LanguageFileDirectoryManager::class]);
        self::assertSame($internal[InstallLanguage::class], $provide[InstallLanguage::class]);
        self::assertSame($internal[UpdateLanguage::class], $provide[UpdateLanguage::class]);
        self::assertSame($internal[UninstallLanguage::class], $provide[UninstallLanguage::class]);
        self::assertSame($internal[RemoveLocalLanguageChanges::class], $provide[RemoveLocalLanguageChanges::class]);
        self::assertSame($internal[AddLanguageEntry::class], $provide[AddLanguageEntry::class]);
        self::assertSame(
            $internal[SetLanguageDetectionEnabled::class],
            $provide[SetLanguageDetectionEnabled::class]
        );
        self::assertSame(
            $internal[SetLanguageTranslationEnabled::class],
            $provide[SetLanguageTranslationEnabled::class]
        );
    }

    public function testContributeEntriesResolveToExpectedConcreteClasses(): void
    {
        $this->initComponent();

        $directories = $this->contributionsOf(LanguageFileDirectory::class);
        self::assertCount(1, $directories);
        self::assertInstanceOf(MainLanguageFileDirectory::class, $directories[0]);

        $this->contributedSetupAgent();

        $activities = array_map(
            static fn(object $activity): string => $activity::class,
            $this->contributionsOf(\ILIAS\Component\Activities\Activity::class)
        );
        sort($activities);
        $expected = [
            InstallLanguage::class,
            UpdateLanguage::class,
            UninstallLanguage::class,
            RemoveLocalLanguageChanges::class,
            AddLanguageEntry::class,
            SetLanguageDetectionEnabled::class,
            SetLanguageTranslationEnabled::class,
        ];
        sort($expected);
        self::assertSame($expected, $activities);

        $user_settings = $this->contributionsOf(\ILIAS\User\Settings\UserSettings::class);
        self::assertCount(1, $user_settings);
        self::assertInstanceOf(UserSettingsSettings::class, $user_settings[0]);
    }

    /** The Setup agent gets UpdateLanguage's Setup instance, not the GUI's: without provider it re-applies no plugin language files. */
    public function testTheSetupAgentGetsTheSetupInstanceOfUpdateLanguage(): void
    {
        [, , , $contribute, , $provide] = $this->initComponent();
        $agent = $contribute[\ILIAS\Setup\Agent::class . '_1'];

        $update_language = (new ReflectionProperty(\ilLanguageSetupAgent::class, 'update_language'))->getValue($agent);
        $repository = new ReflectionProperty(UpdateLanguage::class, 'repository');

        self::assertNotSame($provide[UpdateLanguage::class], $update_language);
        self::assertNull($repository->getValue($update_language));
        self::assertInstanceOf(RepositoryProvider::class, $repository->getValue($provide[UpdateLanguage::class]));
    }

    public function testContributedActivitiesAreTheSameSingletonsAsTheProvidedInstances(): void
    {
        [, , , , , $provide] = $this->initComponent();

        // the Setup path and the GUI must share the very same Activity instances, not merely equal ones
        $contributed = $this->contributionsOf(\ILIAS\Component\Activities\Activity::class);
        foreach ([
            InstallLanguage::class, UpdateLanguage::class, UninstallLanguage::class, RemoveLocalLanguageChanges::class,
            AddLanguageEntry::class, SetLanguageDetectionEnabled::class, SetLanguageTranslationEnabled::class,
        ] as $class) {
            self::assertTrue(in_array($provide[$class], $contributed, true), $class . ' is not contributed as provided');
        }
    }

    public function testImplementCandidatesAreOfferedInDeclarationOrder(): void
    {
        [, $implement] = $this->initComponent();

        // consumers disambiguate by position (Renderer::renderUse()): the Setup candidate must be registered before the runtime one
        self::assertInstanceOf(\ilSetupLanguage::class, $implement[\ILIAS\Language\Language::class . '_0']);
        self::assertInstanceOf(
            LanguageLegacyInitialisationAdapter::class,
            $implement[\ILIAS\Language\Language::class . '_1']
        );
    }

    private function createDirectories(
        string $root,
        string $data_dir = '/nonexistent-ilias-data'
    ): \ILIAS\Environment\Configuration\Installation\Directories {
        $directories = $this->createStub(\ILIAS\Environment\Configuration\Installation\Directories::class);
        $directories->method('getRoot')->willReturn($root);
        $directories->method('getDataDir')->willReturn($data_dir);

        return $directories;
    }

    private function createClientIdProvider(string $client_id): \ILIAS\Environment\Configuration\Installation\ClientIdProvider
    {
        $provider = $this->createStub(\ILIAS\Environment\Configuration\Installation\ClientIdProvider::class);
        $provider->method('getClientId')->willReturn(new \ILIAS\Data\ClientId($client_id));

        return $provider;
    }

    public function testTheRuntimeClientDataDirComesFromDirectoriesAndTheClientId(): void
    {
        $data_dir = sys_get_temp_dir() . '/ilias_lang_graph_' . bin2hex(random_bytes(4));
        mkdir($data_dir . '/myclient', 0775, true);
        try {
            [, , , , , $provide, , $internal] = $this->initComponent([
                \ILIAS\Language\Language::class => $this->createStub(\ILIAS\Language\Language::class),
                \ILIAS\AccessControl\PublicInterface\RBAC::class => $this->createStub(\ILIAS\AccessControl\PublicInterface\RBAC::class),
                \ILIAS\Environment\Configuration\Installation\Directories::class => $this->createDirectories(
                    (string) realpath(__DIR__ . '/../../../../'),
                    $data_dir
                ),
                \ILIAS\Environment\Configuration\Installation\ClientIdProvider::class => $this->createClientIdProvider('myclient'),
            ]);
            $setup_language = self::readPrivateProperty($provide[InstallLanguage::class], 'setup_language');
            $provider_resolver = self::readPrivateProperty($internal[RepositoryProvider::class], 'client_data_dir_resolver');

            self::assertSame($data_dir . '/myclient', $setup_language->getClientDataDir());
            self::assertSame($data_dir . '/myclient', $provider_resolver());
        } finally {
            rmdir($data_dir . '/myclient');
            rmdir($data_dir);
        }
    }

    public function testAnInvalidClientIdYieldsNoClientDataDir(): void
    {
        $client_id_provider = $this->createStub(\ILIAS\Environment\Configuration\Installation\ClientIdProvider::class);
        $client_id_provider->method('getClientId')->willReturnCallback(
            static fn(): \ILIAS\Data\ClientId => new \ILIAS\Data\ClientId('')
        );
        [, , , , , $provide, , $internal] = $this->initComponent([
            \ILIAS\Language\Language::class => $this->createStub(\ILIAS\Language\Language::class),
            \ILIAS\AccessControl\PublicInterface\RBAC::class => $this->createStub(\ILIAS\AccessControl\PublicInterface\RBAC::class),
            \ILIAS\Environment\Configuration\Installation\ClientIdProvider::class => $client_id_provider,
        ]);
        $setup_language = self::readPrivateProperty($provide[InstallLanguage::class], 'setup_language');
        $provider_resolver = self::readPrivateProperty($internal[RepositoryProvider::class], 'client_data_dir_resolver');

        self::assertNull($setup_language->getClientDataDir());
        self::assertNull($provider_resolver());
    }

    public function testRepositoryGetsTheRootFromDirectories(): void
    {
        [, , , , , , , $internal] = $this->initComponent([
            \ILIAS\Language\Language::class => $this->createStub(\ILIAS\Language\Language::class),
            \ILIAS\AccessControl\PublicInterface\RBAC::class => $this->createStub(\ILIAS\AccessControl\PublicInterface\RBAC::class),
            \ILIAS\Environment\Configuration\Installation\Directories::class => $this->createDirectories('/ilias-root'),
        ]);

        self::assertSame(
            '/ilias-root',
            self::readPrivateProperty($internal[InstalledLanguageDatabaseRepository::class], 'installation_root')->getPath()
        );
    }

    public function testRepositoryDoesNotReadTheRootWhileWiring(): void
    {
        $directories = $this->createMock(\ILIAS\Environment\Configuration\Installation\Directories::class);
        $directories->expects(self::never())->method('getRoot');

        [, , , , , , , $internal] = $this->initComponent([
            \ILIAS\Language\Language::class => $this->createStub(\ILIAS\Language\Language::class),
            \ILIAS\AccessControl\PublicInterface\RBAC::class => $this->createStub(\ILIAS\AccessControl\PublicInterface\RBAC::class),
            \ILIAS\Environment\Configuration\Installation\Directories::class => $directories,
        ]);

        self::assertInstanceOf(InstalledLanguageDatabaseRepository::class, $internal[InstalledLanguageDatabaseRepository::class]);
    }

    private function resolveEverything(PimpleContainer $provide): void
    {
        foreach ([
            InstallLanguage::class, UpdateLanguage::class, UninstallLanguage::class, RemoveLocalLanguageChanges::class,
            AddLanguageEntry::class, SetLanguageDetectionEnabled::class, SetLanguageTranslationEnabled::class,
        ] as $key) {
            self::assertIsObject($provide[$key]);
        }
        $this->contributedSetupAgent();
    }

    /**
     * @template T of \ilDBInterface
     * @param class-string<T> $class
     * @return T&\PHPUnit\Framework\MockObject\MockObject
     */
    private function databaseExpectingQueries(string $class, int $queries): \ilDBInterface
    {
        $db = $this->createMock($class);
        $db->expects(self::exactly($queries))->method('query');

        return $db;
    }

    /** Repository and RepositoryProvider query the pulled connection, never $GLOBALS['ilDB']. */
    public function testDatabaseConsumersGetThePulledConnectionNotTheGlobals(): void
    {
        $dic = new \ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;
        $GLOBALS['ilDB'] = $this->databaseExpectingQueries(\ilDBInterface::class, 0);
        $connection = $this->databaseExpectingQueries(\ILIAS\Database\Connection::class, 2);

        [, , , , , $provide, , $internal] = $this->initComponent(null, $connection);
        $this->resolveEverything($provide);

        self::assertSame([], $internal[InstalledLanguageDatabaseRepository::class]->getInstalledLanguages());
        // the lookup behind the provider's updateModuleCache(); the update itself needs the legacy container
        (new ReflectionMethod(RepositoryProvider::class, 'currentModuleEntries'))
            ->invoke($internal[RepositoryProvider::class], 'de', 'common', null);
        self::assertSame($dic, $GLOBALS['DIC']);
    }

    public function testWiringNeverCallsRbacSystemWhileResolving(): void
    {
        $rbac = $this->createMock(\ILIAS\AccessControl\PublicInterface\RBAC::class);
        $rbac->expects(self::never())->method('system');

        [, , , , , $provide] = $this->initComponent([
            \ILIAS\Language\Language::class => $this->createStub(\ILIAS\Language\Language::class),
            \ILIAS\AccessControl\PublicInterface\RBAC::class => $rbac,
        ]);

        $this->resolveEverything($provide);
    }

    public function testSetupAgentIsBuildableWithoutAnyRbacEntry(): void
    {
        $use = new class () extends \ArrayObject {
            public function offsetGet(mixed $key): mixed
            {
                if ($key === \ILIAS\AccessControl\PublicInterface\RBAC::class) {
                    throw new \LogicException('the Setup agent must not touch RBAC');
                }
                return parent::offsetGet($key);
            }
        };
        $use[\ILIAS\Language\Language::class] = $this->createStub(\ILIAS\Language\Language::class);

        $this->initComponent($use);

        $this->contributedSetupAgent();
    }

    /** @return array<string, array{0: class-string<InstallLanguage|UpdateLanguage>}> */
    public static function setupLanguageActivities(): array
    {
        return [
            'install' => [InstallLanguage::class],
            'update' => [UpdateLanguage::class],
        ];
    }

    private function performReadOnly(InstallLanguage|UpdateLanguage $activity): void
    {
        $activity->perform(
            $activity instanceof InstallLanguage
                ? ['language_keys' => ['fr'], 'mode' => InstallLanguage::MODE_INSTALL_LOCAL]
                : ['language_keys' => ['fr']]
        );
    }

    /** @param class-string<InstallLanguage|UpdateLanguage> $class */
    #[DataProvider('setupLanguageActivities')]
    public function testWebActivitiesReadTheInstalledLanguagesFromThePulledConnection(string $class): void
    {
        $GLOBALS['ilDB'] = $this->databaseExpectingQueries(\ilDBInterface::class, 0);
        $connection = $this->databaseExpectingQueries(\ILIAS\Database\Connection::class, 1);

        [, , , , , $provide] = $this->initComponent(null, $connection);

        $this->performReadOnly($provide[$class]);
    }

    public function testSetupLanguageInstanceReadsTheGlobalDatabase(): void
    {
        $GLOBALS['ilDB'] = $this->databaseExpectingQueries(\ilDBInterface::class, 1);
        $connection = $this->databaseExpectingQueries(\ILIAS\Database\Connection::class, 0);

        [, $implement, , , , , , $internal] = $this->initComponent(null, $connection);

        self::assertSame($internal[\ilSetupLanguage::class], $implement[\ILIAS\Language\Language::class . '_0']);
        self::assertSame([], $internal[\ilSetupLanguage::class]->getInstalledLanguages());
    }

    /**
     * The agent's activities are only reachable through protected properties.
     *
     * @param class-string<InstallLanguage|UpdateLanguage> $class
     */
    #[DataProvider('setupLanguageActivities')]
    public function testSetupAgentActivitiesDoNotUseThePulledConnection(string $class): void
    {
        $GLOBALS['ilDB'] = $this->databaseExpectingQueries(\ilDBInterface::class, 1);
        $connection = $this->databaseExpectingQueries(\ILIAS\Database\Connection::class, 0);

        $this->initComponent(null, $connection);
        $agent = $this->contributedSetupAgent();

        $this->performReadOnly(
            self::readPrivateProperty($agent, $class === InstallLanguage::class ? 'install_language' : 'update_language')
        );
    }

    /** Activities write through an installation manager that invalidates the global language cache (a no-op without cache). */
    public function testTheRuntimeInstallationManagerHasATranslationCacheThatIsSafeWithoutGlobalCache(): void
    {
        [, , , , , $provide] = $this->initComponent();
        $setup_language = self::readPrivateProperty($provide[InstallLanguage::class], 'setup_language');
        $manager = (new \ReflectionMethod($setup_language, 'manager'))->invoke($setup_language);

        $translation_cache = self::readPrivateProperty($manager, 'translation_cache');

        self::assertInstanceOf(\ILIAS\Language\Setup\TranslationCacheLegacyProxy::class, $translation_cache);
        $translation_cache->invalidate('de');
    }

    public function testTheRuntimeInstallationManagerAndTheRepositoryProviderLogToTheLangLogger(): void
    {
        $logger = $this->createStub(\ILIAS\Logging\Logger\LoggerInterface::class);
        $other_logger = $this->createStub(\ILIAS\Logging\Logger\LoggerInterface::class);
        $logger_factory = $this->createStub(\ILIAS\Logging\Logger\LoggerFactoryInterface::class);
        $logger_factory->method('getLazy')->willReturnCallback(
            static fn(string $component_id) => $component_id === 'lang' ? $logger : $other_logger
        );
        [, , , , , $provide, , $internal] = $this->initComponent([
            \ILIAS\Language\Language::class => $this->createStub(\ILIAS\Language\Language::class),
            \ILIAS\AccessControl\PublicInterface\RBAC::class => $this->createStub(\ILIAS\AccessControl\PublicInterface\RBAC::class),
            \ILIAS\Logging\Logger\LoggerFactoryInterface::class => $logger_factory,
        ]);
        $setup_language = self::readPrivateProperty($provide[InstallLanguage::class], 'setup_language');
        $manager = (new \ReflectionMethod($setup_language, 'manager'))->invoke($setup_language);

        self::assertSame($logger, self::readPrivateProperty($manager, 'logger'));
        self::assertSame($logger, self::readPrivateProperty($internal[RepositoryProvider::class], 'logger'));
    }

    private static function readPrivateProperty(object $object, string $property): mixed
    {
        return (new \ReflectionProperty($object, $property))->getValue($object);
    }
}
