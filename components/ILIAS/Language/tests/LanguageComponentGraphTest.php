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
use PHPUnit\Framework\TestCase;

/**
 * Regression coverage for the wiring performed by
 * components/ILIAS/Language/Language.php::init().
 *
 * Language.php was reorganised so that $internal entries are declared in
 * dependency order and two previously-duplicated expressions
 * ($resolve_db / $ilias_root) were extracted into shared local variables.
 * Nothing about *which* class ends up behind which $implement / $contribute
 * / $provide key was meant to change - the container types involved
 * (Pimple\Container, wrapped in RenamingDIC for $implement/$contribute) are
 * exactly what makes the reordering safe: closures created early still see
 * later mutations of the same container object, because a PHP arrow
 * function captures the *variable* by value, and for an object-typed
 * variable ("by value" copies only the handle) that value is a reference to
 * the very same underlying container.
 *
 * These tests exercise Language.php::init() through container types that
 * mirror what artifacts/bootstrap_*.php actually builds at runtime (see
 * ILIAS\Component\Dependencies\Renderer::renderComponent()), so a
 * regression that only breaks when a real, mutable, lazily-evaluated
 * container is used (as opposed to a plain PHP array test double) would
 * still be caught here.
 *
 * $use only seeds \ILIAS\Language\Language::class: Language.php::init() does
 * not (any longer) read a \ILIAS\UI\Factory entry from $use - seeding one
 * here regardless would silently keep passing even if init() started reading
 * it again incorrectly (e.g. the old $ui_factory dependency reappearing by
 * accident), since a mock nobody asserts against never fails a test.
 */
class LanguageComponentGraphTest extends TestCase
{
    /**
     * @return array{0: array, 1: RenamingDIC, 2: array, 3: RenamingDIC, 4: array, 5: PimpleContainer, 6: array, 7: PimpleContainer}
     */
    private function initComponent(): array
    {
        $define = [];
        $use = [
            \ILIAS\Language\Language::class => $this->createMock(\ILIAS\Language\Language::class),
        ];
        $seek = [
            LanguageFileDirectory::class => [],
        ];
        $pull = [
            RefineryFactory::class => $this->createMock(RefineryFactory::class),
        ];

        // $implement and $contribute are wrapped in RenamingDIC in the real
        // generated bootstrap (see Renderer::renderComponent()) so that
        // several assignments to the very same key don't collide - a plain
        // Pimple\Container would silently let the second assignment
        // overwrite the first.
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
        self::assertInstanceOf(InstalledLanguageDatabaseRepository::class, $provide[InstalledLanguageRepository::class]);
        self::assertInstanceOf(LanguageInstallationManager::class, $provide[LanguageInstallationManager::class]);
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

        // $provide[...] is only a thin re-export of $internal[...]; both
        // must resolve to the exact same object (Pimple caches the closure
        // result), not merely to objects of the same class.
        self::assertSame($internal[LanguageFileDirectoryManager::class], $provide[LanguageFileDirectoryManager::class]);
        self::assertSame($internal[InstalledLanguageDatabaseRepository::class], $provide[InstalledLanguageRepository::class]);
        self::assertSame($internal[LanguageInstallationManager::class], $provide[LanguageInstallationManager::class]);
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

        // RenamingDIC assigns a single, container-wide "_<counter>" suffix
        // to every offsetSet call, in call order. Reading these exact keys
        // back pins the relative order of the ten $contribute entries,
        // which the reorganisation was explicitly required to preserve -
        // including the seven \ILIAS\Component\Activities\Activity::class
        // entries (InstallLanguage, then UpdateLanguage, then
        // UninstallLanguage, then RemoveLocalLanguageChanges, then
        // AddLanguageEntry, then SetLanguageDetectionEnabled, then
        // SetLanguageTranslationEnabled), one per Activity this component
        // offers. The UserSettings contribution's counter shifted from _4
        // to _5 when UninstallLanguage's own Activity contribution was
        // added ahead of it, then from _5 to _6 when
        // RemoveLocalLanguageChanges' Activity contribution was added ahead
        // of it in turn, then from _6 to _7 with AddLanguageEntry's Activity
        // contribution added ahead of it, then from _7 to _8 with
        // SetLanguageDetectionEnabled's Activity contribution added ahead of
        // it, and has now shifted again from _8 to _9 with
        // SetLanguageTranslationEnabled's Activity contribution added ahead
        // of it - a hardcoded '_8' for UserSettingsSettings would otherwise
        // silently start resolving to the wrong contribute entry (or, as
        // happened before, to no entry at all).
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

        // ilLanguageSetupAgent (Setup path) and ilObjLanguageFolderGUI
        // (via $provide[InstallLanguage::class]) must operate on the exact
        // same InstallLanguage instance, not merely equal ones - and the
        // same holds for UpdateLanguage, UninstallLanguage,
        // RemoveLocalLanguageChanges, AddLanguageEntry,
        // SetLanguageDetectionEnabled and SetLanguageTranslationEnabled.
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

        // The Setup-path candidate (\ilSetupLanguage) must be registered
        // before the runtime/Init-path candidate
        // (LanguageLegacyInitialisationAdapter) - consumers disambiguate
        // by position (see cli/build_bootstrap.php /
        // Renderer::renderUse()), so a swap here would silently flip which
        // candidate every $use[\ILIAS\Language\Language::class] consumer
        // gets wired to.
        self::assertInstanceOf(\ilSetupLanguage::class, $implement[\ILIAS\Language\Language::class . '_0']);
        self::assertInstanceOf(
            LanguageLegacyInitialisationAdapter::class,
            $implement[\ILIAS\Language\Language::class . '_1']
        );
    }

    /**
     * Regression guard for the $ilias_root / $resolve_db deduplication:
     * both InstalledLanguageDatabaseRepository and LanguageInstallationManager
     * must still end up with the exact same, correctly computed ILIAS root
     * path (previously two textually-identical `realpath()` calls, now one
     * shared local variable read twice).
     */
    public function testDeduplicatedIliasRootIsCorrectAndIdenticalForBothConsumers(): void
    {
        [, , , , , $provide] = $this->initComponent();

        $expected_root = (string) realpath(__DIR__ . '/../../../../');

        self::assertNotSame('', $expected_root, 'sanity check: the ILIAS root must resolve to a real path');

        $repository_root = self::readPrivateProperty($provide[InstalledLanguageRepository::class], 'absolute_path');
        $manager_root = self::readPrivateProperty($provide[LanguageInstallationManager::class], 'absolute_path');

        self::assertSame($expected_root, $repository_root);
        self::assertSame($expected_root, $manager_root);
    }

    private static function readPrivateProperty(object $object, string $property): mixed
    {
        return (new \ReflectionProperty($object, $property))->getValue($object);
    }
}
