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
        $seek = [
            LanguageFileDirectory::class => [],
        ];
        $pull = [
            RefineryFactory::class => $this->createStub(RefineryFactory::class),
            \ILIAS\Database\Connection::class => $connection ?? $this->createStub(\ILIAS\Database\Connection::class),
        ];

        // RenamingDIC (as in the generated bootstrap) keeps several assignments to one key apart; a plain Pimple\Container overwrites
        $implement = new RenamingDIC(new PimpleContainer());
        $contribute = new RenamingDIC(new PimpleContainer());
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

    public function testContributeEntriesResolveToExpectedConcreteClassesInPreservedOrder(): void
    {
        [, , , $contribute] = $this->initComponent();

        // RenamingDIC suffixes every offsetSet with a container-wide "_<counter>" in call order, so these keys pin the order of
        // the $contribute entries: a hardcoded counter shifts whenever a contribution is added ahead of it
        self::assertInstanceOf(MainLanguageFileDirectory::class, $contribute[LanguageFileDirectory::class . '_0']);
        self::assertInstanceOf(\ilLanguageSetupAgent::class, $contribute[\ILIAS\Setup\Agent::class . '_1']);
        self::assertInstanceOf(InstallLanguage::class, $contribute[\ILIAS\Component\Activities\Activity::class . '_2']);
        self::assertInstanceOf(UpdateLanguage::class, $contribute[\ILIAS\Component\Activities\Activity::class . '_3']);
        self::assertInstanceOf(UninstallLanguage::class, $contribute[\ILIAS\Component\Activities\Activity::class . '_4']);
        self::assertInstanceOf(RemoveLocalLanguageChanges::class, $contribute[\ILIAS\Component\Activities\Activity::class . '_5']);
        self::assertInstanceOf(AddLanguageEntry::class, $contribute[\ILIAS\Component\Activities\Activity::class . '_6']);
        self::assertInstanceOf(SetLanguageDetectionEnabled::class, $contribute[\ILIAS\Component\Activities\Activity::class . '_7']);
        self::assertInstanceOf(SetLanguageTranslationEnabled::class, $contribute[\ILIAS\Component\Activities\Activity::class . '_8']);
        self::assertInstanceOf(UserSettingsSettings::class, $contribute[\ILIAS\User\Settings\UserSettings::class . '_9']);
    }

    public function testContributedActivitiesAreTheSameSingletonsAsTheProvidedInstances(): void
    {
        [, , , $contribute, , $provide] = $this->initComponent();

        // the Setup path and the GUI must share the very same Activity instances, not merely equal ones
        self::assertSame(
            $provide[InstallLanguage::class],
            $contribute[\ILIAS\Component\Activities\Activity::class . '_2']
        );
        self::assertSame(
            $provide[UpdateLanguage::class],
            $contribute[\ILIAS\Component\Activities\Activity::class . '_3']
        );
        self::assertSame(
            $provide[UninstallLanguage::class],
            $contribute[\ILIAS\Component\Activities\Activity::class . '_4']
        );
        self::assertSame(
            $provide[RemoveLocalLanguageChanges::class],
            $contribute[\ILIAS\Component\Activities\Activity::class . '_5']
        );
        self::assertSame(
            $provide[AddLanguageEntry::class],
            $contribute[\ILIAS\Component\Activities\Activity::class . '_6']
        );
        self::assertSame(
            $provide[SetLanguageDetectionEnabled::class],
            $contribute[\ILIAS\Component\Activities\Activity::class . '_7']
        );
        self::assertSame(
            $provide[SetLanguageTranslationEnabled::class],
            $contribute[\ILIAS\Component\Activities\Activity::class . '_8']
        );
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

    private function createDirectories(string $root): \ILIAS\Environment\Configuration\Installation\Directories
    {
        $directories = $this->createStub(\ILIAS\Environment\Configuration\Installation\Directories::class);
        $directories->method('getRoot')->willReturn($root);

        return $directories;
    }

    public function testRepositoryGetsTheRootFromDirectories(): void
    {
        [, , , , , , , $internal] = $this->initComponent([
            \ILIAS\Language\Language::class => $this->createStub(\ILIAS\Language\Language::class),
            \ILIAS\AccessControl\PublicInterface\RBAC::class => $this->createStub(\ILIAS\AccessControl\PublicInterface\RBAC::class),
            \ILIAS\Environment\Configuration\Installation\Directories::class => $this->createDirectories('/ilias-root'),
        ]);

        self::assertSame('/ilias-root', self::readPrivateProperty($internal[InstalledLanguageDatabaseRepository::class], 'absolute_path'));
    }

    private function resolveEverything(PimpleContainer $provide, RenamingDIC $contribute): void
    {
        foreach ([
            InstallLanguage::class, UpdateLanguage::class, UninstallLanguage::class, RemoveLocalLanguageChanges::class,
            AddLanguageEntry::class, SetLanguageDetectionEnabled::class, SetLanguageTranslationEnabled::class,
        ] as $key) {
            self::assertIsObject($provide[$key]);
        }
        self::assertInstanceOf(\ilLanguageSetupAgent::class, $contribute[\ILIAS\Setup\Agent::class . '_1']);
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

    /** Repository and AddLanguageEntry query the pulled connection, never $GLOBALS['ilDB']. */
    public function testDatabaseConsumersGetThePulledConnectionNotTheGlobals(): void
    {
        $dic = new \ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;
        $GLOBALS['ilDB'] = $this->databaseExpectingQueries(\ilDBInterface::class, 0);
        $connection = $this->databaseExpectingQueries(\ILIAS\Database\Connection::class, 2);

        [, , , $contribute, , $provide, , $internal] = $this->initComponent(null, $connection);
        $this->resolveEverything($provide, $contribute);

        self::assertSame([], $internal[InstalledLanguageDatabaseRepository::class]->getInstalledLanguages());
        // default of AddLanguageEntry's module cache update, whose public path also calls the static ilObjLanguage::replaceLangEntry()
        self::readPrivateProperty($internal[AddLanguageEntry::class], 'update_module_cache')('de', 'common', 'x', 'y');
        self::assertSame($dic, $GLOBALS['DIC']);
    }

    public function testWiringNeverCallsRbacSystemWhileResolving(): void
    {
        $rbac = $this->createMock(\ILIAS\AccessControl\PublicInterface\RBAC::class);
        $rbac->expects(self::never())->method('system');

        [, , , $contribute, , $provide] = $this->initComponent([
            \ILIAS\Language\Language::class => $this->createStub(\ILIAS\Language\Language::class),
            \ILIAS\AccessControl\PublicInterface\RBAC::class => $rbac,
        ]);

        $this->resolveEverything($provide, $contribute);
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

        [, , , $contribute] = $this->initComponent($use);

        self::assertInstanceOf(\ilLanguageSetupAgent::class, $contribute[\ILIAS\Setup\Agent::class . '_1']);
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

        [, , , $contribute] = $this->initComponent(null, $connection);
        $agent = $contribute[\ILIAS\Setup\Agent::class . '_1'];

        $this->performReadOnly(
            self::readPrivateProperty($agent, $class === InstallLanguage::class ? 'install_language' : 'update_language')
        );
    }

    private static function readPrivateProperty(object $object, string $property): mixed
    {
        return (new \ReflectionProperty($object, $property))->getValue($object);
    }
}
