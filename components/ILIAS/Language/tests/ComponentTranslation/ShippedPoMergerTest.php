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

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use ILIAS\Language\ComponentTranslation\TranslationGettextRepository;
use ILIAS\Language\ComponentTranslation\ShippedPoMerger;
use PHPUnit\Framework\TestCase;

/**
 * Direct coverage of ShippedPoMerger (merge(), backupShippedPoFiles(), findShippedChangesSinceBackup()), independent of
 * ilObjLanguageExt/ilObjLanguageExtGUI.
 *
 * Two roots as in production: the SHIPPED `.po`/`.pot` below ILIAS_ABSOLUTE_PATH ($this->fixture_directory), the only
 * files this class may write, and the OVERLAY `.po`/`.mo` below a throwaway client data directory
 * ($this->client_data_dir).
 */
class ShippedPoMergerTest extends TestCase
{
    private const string MODULE = 'mtest';
    private const string LANG = 'de';

    private string $fixture_directory;
    private string $client_data_dir;
    private string $backup_directory;
    private LanguageFileDirectory $directory;
    private LanguageFileDirectoryManager $manager;

    protected function setUp(): void
    {
        // never the build of the installation, see MigratedPoFixture::resetRuntime()
        MigratedPoFixture::resetRuntime();
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../../'));
        }

        $this->fixture_directory = __DIR__ . '/tmp-shipped-po-merger-' . bin2hex(random_bytes(4));
        mkdir($this->fixture_directory, 0775, true);
        $this->client_data_dir = sys_get_temp_dir() . '/ilias_spm_test_client_' . bin2hex(random_bytes(4));
        mkdir($this->client_data_dir, 0775, true);
        // AtomicFileWriter confines a shipped-.po backup write to below ILIAS_ABSOLUTE_PATH (like the
        // shipped .po/.pot themselves) - unlike the overlay, which lives below CLIENT_DATA_DIR
        $this->backup_directory = $this->fixture_directory . '-backup';
        mkdir($this->backup_directory, 0775, true);

        $this->directory = MigratedPoFixture::directory(
            self::MODULE,
            'components/ILIAS/Language/tests/ComponentTranslation/' . basename($this->fixture_directory) . '/'
        );
        $this->manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $this->directory);
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->fixture_directory);
        MigratedPoFixture::removeDirectory($this->client_data_dir);
        MigratedPoFixture::removeDirectory($this->backup_directory);

        parent::tearDown();
    }

    private function shippedPo(string $lang_key = self::LANG): string
    {
        return $this->fixture_directory . '/' . self::MODULE . '_' . $lang_key . '.po';
    }

    private function templatePo(): string
    {
        return $this->fixture_directory . '/' . self::MODULE . '.pot';
    }

    /**
     * @param array<string, string|array<string, mixed>> $entries see MigratedPoFixture::catalog()
     */
    private function seedShipped(array $entries, string $lang_key = self::LANG): void
    {
        MigratedPoFixture::writePo($this->shippedPo($lang_key), MigratedPoFixture::catalog(self::MODULE, $entries));
    }

    /**
     * @param array<string, string|list<string>> $entries plain value, or the forms of a plural message
     */
    private function seedShippedPlural(array $entries): void
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        foreach ($entries as $identifier => $value) {
            $entry = new TranslationEntry(self::MODULE, (string) $identifier);
            if (is_array($value)) {
                $entry->setPlural($identifier . '_plural', $value);
            } else {
                $entry->translate($value);
            }
            $catalog->add($entry);
        }
        MigratedPoFixture::writePo($this->shippedPo(), $catalog);
    }

    /**
     * @param array<string, string|array<string, mixed>> $entries
     */
    private function seedTemplate(array $entries): void
    {
        MigratedPoFixture::writePo($this->templatePo(), MigratedPoFixture::catalog(self::MODULE, $entries));
    }

    private function overlayBase(string $lang_key = self::LANG): string
    {
        return MigratedPoFixture::overlayBase($this->client_data_dir, $this->directory, $lang_key);
    }

    /**
     * A local change written the ordinary way (like an admin edit), with the overlay's production
     * "original"/"local_change" bookkeeping.
     *
     * @param array<string, string> $entries
     * @param array<string, string>|null $remarks
     */
    private function seedOverlay(array $entries, ?array $remarks = null): void
    {
        MigratedLanguageFileSync::sync(
            $this->manager,
            ILIAS_ABSOLUTE_PATH,
            self::LANG,
            self::MODULE,
            $entries,
            $this->client_data_dir,
            false,
            null,
            $remarks
        );
    }

    /**
     * Writes a single overlay entry directly, bypassing sync() - for shapes sync()'s flat
     * identifier => value map cannot produce (a plural overlay message, an entry with an empty
     * msgstr[0] next to other forms).
     */
    private function seedManualOverlayEntry(TranslationEntry $entry): void
    {
        $catalog = new TranslationCatalog();
        $catalog->add($entry);
        // .po only: a plural message with an empty msgstr[0] next to other forms (exactly the shape
        // some of these tests need) cannot be compiled into a `.mo` at all (see toMoString()) - the
        // overlay is read from its `.po` regardless (see readOverlayCatalog()).
        MigratedPoFixture::writePo($this->overlayBase() . '.po', $catalog);
    }

    private function readShippedPo(string $lang_key = self::LANG): TranslationCatalog
    {
        return TranslationCatalog::fromPoFile($this->shippedPo($lang_key));
    }

    /**
     * @return array{
     *     written: array<string, string>, skipped: array<string, string>,
     *     invalid_markup: array<string, array<string, list<string>>>, not_merged: array<string, list<string>>,
     *     unwritten_database: array<string, string>, unmarked_overlay: array<string, string>
     * }
     */
    private function merge(): array
    {
        return ShippedPoMerger::merge(
            $this->manager,
            ILIAS_ABSOLUTE_PATH,
            self::LANG,
            $this->client_data_dir,
            $this->backup_directory
        );
    }

    public function testMergeTakesOverTheOverlayValueRemovesFuzzyAndReconcilesTheOverlay(): void
    {
        $this->seedShipped(['greeting' => ['value' => 'Hallo', 'fuzzy' => true]]);
        $this->seedOverlay(['greeting' => 'Servus']);

        $result = $this->merge();

        $this->assertSame(['mtest' => $this->shippedPo()], $result['written']);
        $this->assertSame([], $result['skipped'] + $result['invalid_markup'] + $result['not_merged']);

        $entry = $this->readShippedPo()->find(self::MODULE, 'greeting');
        $this->assertSame('Servus', $entry?->getTranslation());
        $this->assertFalse($entry->hasFlag('fuzzy'));
        // the overlay is left as it is: the build (only "setup build") still holds the former value,
        // "setup update" drops the entry once it equals the shipped one
        $this->assertSame('Servus', MigratedPoFixture::readPo($this->overlayBase() . '.po')->find(null, 'greeting')?->getTranslation());
    }

    public function testMergeBacksUpTheShippedPoByteForByteBeforeWriting(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $original_bytes = (string) file_get_contents($this->shippedPo());
        $this->seedOverlay(['greeting' => 'Servus']);

        $this->merge();

        $backup_file = $this->backup_directory . '/' . self::MODULE . '_' . self::LANG . '.po';
        $this->assertFileExists($backup_file);
        $this->assertSame($original_bytes, file_get_contents($backup_file));
    }

    public function testASecondMergeRunWithoutFurtherLocalChangesDoesNothing(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => 'Servus']);
        $this->merge();
        $shipped_after_first_merge = (string) file_get_contents($this->shippedPo());

        $result = $this->merge();

        $this->assertSame(
            ['written' => [], 'skipped' => [], 'invalid_markup' => [], 'not_merged' => [], 'unwritten_database' => [], 'unmarked_overlay' => []],
            $result
        );
        $this->assertSame($shipped_after_first_merge, file_get_contents($this->shippedPo()));
    }

    /** An admin remark becomes a translator comment, unless identical to the note the entry shows. */
    public function testMergeAddsAnAdminRemarkAsTranslatorCommentWithoutDuplicatingAnIdenticalOne(): void
    {
        $shipped = MigratedPoFixture::catalog(self::MODULE, ['farewell' => 'Tschüss']);
        $shipped->find(self::MODULE, 'farewell')->addExtractedComment('Old note');
        MigratedPoFixture::writePo($this->shippedPo(), $shipped);
        // value unchanged, but a remark - and a second one identical to the existing extracted comment
        $this->seedOverlay(['farewell' => 'Tschüss'], ['farewell' => 'Old note']);

        $result = $this->merge();

        // nothing new to take over: the shipped .po is not written at all
        $this->assertSame([], $result['written']);
        $this->assertSame(['Old note'], $this->readShippedPo()->find(self::MODULE, 'farewell')?->getExtractedComments());
        $this->assertSame([], $this->readShippedPo()->find(self::MODULE, 'farewell')?->getTranslatorComments());
        $this->assertTrue(is_file($this->overlayBase() . '.po'), 'the overlay is left as it is');
    }

    /** A new remark becomes "# " next to a kept "#."; one equal to the shown note adds nothing. */
    public function testMergeWithAChangedValueAddsOnlyADifferingRemarkAsTranslatorComment(): void
    {
        $shipped = MigratedPoFixture::catalog(self::MODULE, ['farewell' => 'Tschüss', 'greeting' => 'Hallo']);
        $shipped->find(self::MODULE, 'farewell')->addExtractedComment('Old note');
        $shipped->find(self::MODULE, 'greeting')->addTranslatorNote('Own note');
        MigratedPoFixture::writePo($this->shippedPo(), $shipped);
        $this->seedOverlay(
            ['farewell' => 'Servus', 'greeting' => 'Moin'],
            ['farewell' => 'New note', 'greeting' => 'Own note']
        );

        $this->merge();

        $farewell = $this->readShippedPo()->find(self::MODULE, 'farewell');
        $this->assertSame('Servus', $farewell?->getTranslation());
        $this->assertSame(['Old note'], $farewell->getExtractedComments());
        $this->assertSame(['New note'], $farewell->getTranslatorComments());
        $greeting = $this->readShippedPo()->find(self::MODULE, 'greeting');
        $this->assertSame('Moin', $greeting?->getTranslation());
        $this->assertSame(['Own note'], $greeting->getTranslatorComments());
    }

    /** With an own note the reference note is hidden, so an equal remark is written; compared as stored. */
    public function testARemarkEqualToAHiddenReferenceNoteIsWrittenAndOneEqualToTheStoredNoteIsNot(): void
    {
        $shipped = MigratedPoFixture::catalog(self::MODULE, ['farewell' => 'Tschüss', 'greeting' => 'Hallo']);
        $shipped->find(self::MODULE, 'farewell')->addExtractedComment('en note');
        $shipped->find(self::MODULE, 'farewell')->addTranslatorNote('eigene Notiz');
        $shipped->find(self::MODULE, 'greeting')->addTranslatorNote('a b');
        MigratedPoFixture::writePo($this->shippedPo(), $shipped);
        $this->seedOverlay(['farewell' => 'Tschüss', 'greeting' => 'Hallo'], ['farewell' => 'en note', 'greeting' => "a\tb"]);

        $result = $this->merge();

        $this->assertArrayHasKey(self::MODULE, $result['written']);
        $farewell = $this->readShippedPo()->find(self::MODULE, 'farewell');
        $this->assertSame(['eigene Notiz', 'en note'], $farewell?->getTranslatorComments());
        $this->assertSame(['en note'], $farewell->getExtractedComments());
        $this->assertSame(['a b'], $this->readShippedPo()->find(self::MODULE, 'greeting')?->getTranslatorComments());
    }

    public function testMergeSkipsAValueWithDisallowedMarkupAndKeepsItInTheOverlay(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => '<script>alert(1)</script>']);

        $result = $this->merge();

        $this->assertSame([], $result['written']);
        $this->assertArrayHasKey('mtest', $result['invalid_markup']);
        $this->assertArrayHasKey('greeting', $result['invalid_markup']['mtest']);
        $this->assertSame('Hallo', $this->readShippedPo()->find(self::MODULE, 'greeting')?->getTranslation());
        $this->assertTrue(is_file($this->overlayBase() . '.po'));
    }

    public function testMergeSkipsAPluralOverlayForASingularShippedEntryAndKeepsItInTheOverlay(): void
    {
        $this->seedShipped(['items' => 'Element']);
        $entry = new TranslationEntry(self::MODULE, 'items');
        $entry->setPlural('items_plural', ['Kein Element', 'Elemente']);
        $this->seedManualOverlayEntry($entry);

        $result = $this->merge();

        $this->assertSame([], $result['written']);
        $this->assertSame(['mtest' => ['items']], $result['not_merged']);
        $this->assertSame('Element', $this->readShippedPo()->find(self::MODULE, 'items')?->getTranslation());
        $this->assertTrue(is_file($this->overlayBase() . '.po'));
    }

    public function testMergeSkipsAPluralWithAnEmptyFirstFormNextToOtherFormsAndKeepsItInTheOverlay(): void
    {
        $shipped = new TranslationEntry(self::MODULE, 'items');
        $shipped->setPlural('items_plural', ['Kein Element', 'Elemente']);
        $catalog = new TranslationCatalog();
        $catalog->add($shipped);
        MigratedPoFixture::writePo($this->shippedPo(), $catalog);

        $entry = new TranslationEntry(self::MODULE, 'items');
        $entry->setPlural('items_plural', ['', 'Elemente (lokal)']);
        $this->seedManualOverlayEntry($entry);

        $result = $this->merge();

        $this->assertSame([], $result['written']);
        $this->assertSame(['mtest' => ['items']], $result['not_merged']);
        $this->assertTrue(is_file($this->overlayBase() . '.po'));
    }

    /**
     * An identifier of the form "<identifier> [<n>]" would be read back as a plural form
     * (PluralFormKey) - a new key added locally in that shape is not taken over.
     */
    public function testMergeDoesNotAddANewKeyShapedLikeAPluralFormAndKeepsItInTheOverlay(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['newkey [0]' => 'Wert']);

        $result = $this->merge();

        $this->assertSame([], $result['written']);
        $this->assertSame(['mtest' => ['newkey [0]']], $result['not_merged']);
        $this->assertNull($this->readShippedPo()->find(self::MODULE, 'newkey [0]'));
        $this->assertTrue(is_file($this->overlayBase() . '.po'));
    }

    /**
     * A new identifier is inserted into the shipped `.po` in id order and, empty, into the template
     * (`.pot`) - the `.po` of another language is left untouched.
     */
    public function testMergeAddsANewKeySortedIntoThePoAndEmptyIntoThePotOtherLanguageStaysUnchanged(): void
    {
        $this->seedShipped(['aaa' => 'A', 'zzz' => 'Z']);
        $this->seedTemplate(['aaa' => '', 'zzz' => '']);
        $this->seedShipped(['aaa' => 'A (en)', 'zzz' => 'Z (en)'], 'en');
        $other_language_bytes = (string) file_get_contents($this->shippedPo('en'));
        $this->seedOverlay(['mmm' => 'M']);

        $result = $this->merge();

        $this->assertSame(['mtest' => $this->shippedPo()], $result['written']);
        $ids = array_map(static fn(TranslationEntry $e): string => $e->getId(), $this->readShippedPo()->getEntries());
        $this->assertSame(['aaa', 'mmm', 'zzz'], $ids);
        // a new entry is added without context - the shipped .po of a single module need not repeat
        // its name in every entry (see MigratedLanguageFileSync::findModuleEntry())
        $this->assertSame('M', $this->readShippedPo()->find(null, 'mmm')?->getTranslation());

        $template_entry = TranslationCatalog::fromPoFile($this->templatePo())->find(null, 'mmm');
        $this->assertNotNull($template_entry);
        $this->assertSame('', $template_entry->getTranslation());

        $this->assertSame($other_language_bytes, file_get_contents($this->shippedPo('en')));
    }

    public function testMergeSkipsTheWholeModuleWhenTheTemplateForANewKeyIsMissing(): void
    {
        $this->seedShipped(['aaa' => 'A']);
        // no .pot at all
        $this->seedOverlay(['mmm' => 'M']);

        $result = $this->merge();

        $this->assertSame([], $result['written']);
        $this->assertArrayHasKey('mtest', $result['skipped']);
        $this->assertNull($this->readShippedPo()->find(self::MODULE, 'mmm'));
    }

    public function testBackupShippedPoFilesCopiesTheShippedPoNamedLikeTheFile(): void
    {
        $this->seedShipped(['aaa' => 'A']);

        $failed = ShippedPoMerger::backupShippedPoFiles($this->manager, ILIAS_ABSOLUTE_PATH, self::LANG, $this->backup_directory, ILIAS_ABSOLUTE_PATH);

        $this->assertSame([], $failed);
        $this->assertSame(
            file_get_contents($this->shippedPo()),
            file_get_contents($this->backup_directory . '/' . self::MODULE . '_' . self::LANG . '.po')
        );
    }

    /**
     * Two modules whose shipped `.po` share a file name (both use the "shared_%s" pattern, see
     * NamesShippedLanguageFiles, each in its own directory) collide on the backup file name: neither is backed up,
     * rather than one overwriting the other.
     */
    public function testBackupShippedPoFilesReportsAModuleWhoseShippedFileNameIsNotUnique(): void
    {
        $other_module = self::MODULE . '2';
        $other_directory = $this->directoryNamedSharedByLanguage(
            $other_module,
            'components/ILIAS/Language/tests/ComponentTranslation/' . basename($this->fixture_directory) . '-other/'
        );
        $shared_directory = $this->directoryNamedSharedByLanguage(
            self::MODULE,
            'components/ILIAS/Language/tests/ComponentTranslation/' . basename($this->fixture_directory) . '/'
        );
        MigratedPoFixture::writePo(
            $this->fixture_directory . '/shared_' . self::LANG . '.po',
            MigratedPoFixture::catalog(self::MODULE, ['aaa' => 'A'])
        );
        mkdir($this->fixture_directory . '-other', 0775, true);
        MigratedPoFixture::writePo(
            $this->fixture_directory . '-other/shared_' . self::LANG . '.po',
            MigratedPoFixture::catalog($other_module, ['bbb' => 'B'])
        );
        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $shared_directory, $other_directory);

        try {
            $failed = ShippedPoMerger::backupShippedPoFiles($manager, ILIAS_ABSOLUTE_PATH, self::LANG, $this->backup_directory, ILIAS_ABSOLUTE_PATH);

            $this->assertSame([self::MODULE, $other_module], $failed);
        } finally {
            MigratedPoFixture::removeDirectory($this->fixture_directory . '-other');
        }
    }

    private function directoryNamedSharedByLanguage(string $prefix, string $path): LanguageFileDirectory
    {
        return new class ($prefix, $path) implements LanguageFileDirectory, \ILIAS\Language\ComponentTranslation\NamesShippedLanguageFiles {
            public function __construct(private string $prefix, private string $path)
            {
            }

            public function getPrefix(): string
            {
                return $this->prefix;
            }

            public function getPath(): string
            {
                return $this->path;
            }

            public function getSuffix(): string
            {
                return '';
            }

            public function isLocal(): bool
            {
                return false;
            }

            public function getShippedFileNamePattern(): string
            {
                return 'shared_%s';
            }
        };
    }

    public function testFindShippedChangesSinceBackupReportsWhatChangedSinceTheBackup(): void
    {
        $this->seedShipped(['aaa' => 'A', 'bbb' => 'B']);
        ShippedPoMerger::backupShippedPoFiles($this->manager, ILIAS_ABSOLUTE_PATH, self::LANG, $this->backup_directory, ILIAS_ABSOLUTE_PATH);
        // changed after the backup, directly on the shipped file (simulating a later ILIAS update)
        $this->seedShipped(['aaa' => 'A (geändert)', 'bbb' => 'B']);

        $changes = ShippedPoMerger::findShippedChangesSinceBackup($this->manager, ILIAS_ABSOLUTE_PATH, self::LANG, $this->backup_directory);

        $this->assertSame(['mtest' => ['aaa' => 'A (geändert)']], $changes['changes']);
        $this->assertSame([], $changes['without_backup']);
    }

    public function testFindShippedChangesSinceBackupListsAModuleWithoutABackupInstead(): void
    {
        $this->seedShipped(['aaa' => 'A']);
        // no backup written at all

        $changes = ShippedPoMerger::findShippedChangesSinceBackup($this->manager, ILIAS_ABSOLUTE_PATH, self::LANG, $this->backup_directory);

        $this->assertSame([], $changes['changes']);
        $this->assertSame(['mtest'], $changes['without_backup']);
    }

    /**
     * The "conflicts" filter compares plural messages form by form ("<identifier> [<form>]"); an entry
     * added to the shipped .po after the backup is a change, an entry removed after it is not (there
     * is no current shipped value to show).
     */
    public function testFindShippedChangesSinceBackupReportsPluralFormsNewEntriesAndIgnoresRemovedOnes(): void
    {
        $this->seedShippedPlural(['items' => ['Element', 'Elemente'], 'gone' => 'Weg', 'same' => 'Gleich']);
        ShippedPoMerger::backupShippedPoFiles($this->manager, ILIAS_ABSOLUTE_PATH, self::LANG, $this->backup_directory, ILIAS_ABSOLUTE_PATH);
        $this->seedShippedPlural(['items' => ['Element', 'Elemente (neu)'], 'added' => 'Neu', 'same' => 'Gleich']);

        $changes = ShippedPoMerger::findShippedChangesSinceBackup($this->manager, ILIAS_ABSOLUTE_PATH, self::LANG, $this->backup_directory);

        $this->assertSame(
            ['mtest' => ['items [1]' => 'Elemente (neu)', 'added' => 'Neu']],
            $changes['changes']
        );
        $this->assertSame([], $changes['without_backup']);
    }

    public function testFindShippedChangesSinceBackupListsAModuleWithoutReadableBackupEvenIfItChanged(): void
    {
        $this->seedShipped(['aaa' => 'A']);
        ShippedPoMerger::backupShippedPoFiles($this->manager, ILIAS_ABSOLUTE_PATH, self::LANG, $this->backup_directory, ILIAS_ABSOLUTE_PATH);
        file_put_contents($this->backup_directory . '/' . self::MODULE . '_' . self::LANG . '.po', "msgid \"broken\nmsgstr");
        $this->seedShipped(['aaa' => 'A (geändert)']);

        $changes = ShippedPoMerger::findShippedChangesSinceBackup($this->manager, ILIAS_ABSOLUTE_PATH, self::LANG, $this->backup_directory);

        $this->assertSame([], $changes['changes']);
        $this->assertSame(['mtest'], $changes['without_backup']);
    }

    public function testMergeTakesOverAllFormsOfAPluralOverlayMessageAndKeepsTheShippedPluralId(): void
    {
        $this->seedShippedPlural(['items' => ['Element', 'Elemente']]);
        $entry = new TranslationEntry(self::MODULE, 'items');
        $entry->setPlural('other_plural_id', ['Ein Ding', 'Dinge']);
        $this->seedManualOverlayEntry($entry);

        $result = $this->merge();

        $this->assertSame(['mtest' => $this->shippedPo()], $result['written']);
        $this->assertSame([], $result['not_merged']);
        $merged = $this->readShippedPo()->find(self::MODULE, 'items');
        $this->assertTrue($merged->isPlural());
        $this->assertSame('items_plural', $merged->getPluralId());
        $this->assertSame(['Ein Ding', 'Dinge'], $merged->getPluralTranslations());
    }

    /**
     * A plain value for a shipped plural message is its default form (the last one, see
     * PluralForms); the other forms keep their shipped value.
     */
    public function testMergeOfASingularOverlayValueReplacesOnlyTheDefaultFormOfAShippedPluralMessage(): void
    {
        $this->seedShippedPlural(['items' => ['Element', 'Elemente']]);
        $entry = new TranslationEntry(self::MODULE, 'items');
        $entry->translate('Dinge');
        $this->seedManualOverlayEntry($entry);

        $result = $this->merge();

        $this->assertSame([], $result['not_merged']);
        $merged = $this->readShippedPo()->find(self::MODULE, 'items');
        $this->assertTrue($merged->isPlural());
        $this->assertSame(['Element', 'Dinge'], $merged->getPluralTranslations());
    }

    /**
     * $after_module (the database write) failing for one module must not stop the others: the files
     * of every module are written regardless, only the failing one is reported in
     * unwritten_database.
     */
    public function testMergeContinuesOtherModulesWhenTheDatabaseCallbackThrowsForOne(): void
    {
        $other_module = self::MODULE . '2';
        $other_directory = MigratedPoFixture::directory(
            $other_module,
            'components/ILIAS/Language/tests/ComponentTranslation/' . basename($this->fixture_directory) . '-other2/'
        );
        mkdir($this->fixture_directory . '-other2', 0775, true);
        MigratedPoFixture::writePo(
            $this->fixture_directory . '-other2/' . $other_module . '_' . self::LANG . '.po',
            MigratedPoFixture::catalog($other_module, ['bbb' => 'B'])
        );
        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $this->directory, $other_directory);
        $this->seedShipped(['aaa' => 'A']);
        $this->seedOverlay(['aaa' => 'A (lokal)']);
        MigratedLanguageFileSync::sync(
            $manager,
            ILIAS_ABSOLUTE_PATH,
            self::LANG,
            $other_module,
            ['bbb' => 'B (lokal)'],
            $this->client_data_dir
        );

        try {
            $calls = [];
            $result = ShippedPoMerger::merge(
                $manager,
                ILIAS_ABSOLUTE_PATH,
                self::LANG,
                $this->client_data_dir,
                $this->backup_directory,
                static function (string $module) use (&$calls): void {
                    $calls[] = $module;
                    if ($module === self::MODULE) {
                        throw new RuntimeException('database is down');
                    }
                }
            );

            $this->assertSame([self::MODULE, $other_module], $calls);
            $this->assertArrayHasKey(self::MODULE, $result['unwritten_database']);
            $this->assertArrayNotHasKey($other_module, $result['unwritten_database']);
            // both files are written regardless of the database failure
            $this->assertCount(2, $result['written']);
            $this->assertSame('A (lokal)', $this->readShippedPo()->find(self::MODULE, 'aaa')?->getTranslation());
            $this->assertSame(
                'B (lokal)',
                TranslationCatalog::fromPoFile($this->fixture_directory . '-other2/' . $other_module . '_' . self::LANG . '.po')
                    ->find($other_module, 'bbb')?->getTranslation()
            );
        } finally {
            MigratedPoFixture::removeDirectory($this->fixture_directory . '-other2');
        }
    }

    /**
     * @return array<string, string> path relative to $directory => hash of the content, of every file
     */
    private static function snapshot(string $directory): array
    {
        $files = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $files[substr($file->getPathname(), strlen($directory))] = (string) hash_file('sha256', $file->getPathname());
        }
        ksort($files);

        return $files;
    }

    /**
     * The web server does not build: merge writes the shipped `.po`, but leaves the build and the
     * overlay as they are - the runtime keeps serving the local change from the overlay until
     * "setup build" and "setup update" ran.
     */
    public function testMergeNeitherBuildsNorTouchesTheOverlay(): void
    {
        $this->seedShipped(['aaa' => 'A']);
        $this->seedOverlay(['aaa' => 'A (lokal)']);
        MigratedPoFixture::build($this->manager, ILIAS_ABSOLUTE_PATH);
        $pointer_before = (string) file_get_contents(MigratedLanguageFilePaths::buildPointerFile(MigratedPoFixture::artifactDirectory()));
        $overlay_before = (string) file_get_contents($this->overlayBase() . '.po');
        $current_before = (string) file_get_contents(MigratedPoFixture::overlayCurrent($this->overlayBase()));
        $artifacts_before = self::snapshot(MigratedPoFixture::artifactDirectory());
        $overlay_before_tree = self::snapshot(dirname($this->overlayBase()));

        $result = $this->merge();

        $this->assertSame($artifacts_before, self::snapshot(MigratedPoFixture::artifactDirectory()), 'nothing written below artifacts/language');
        $overlay_tree = self::snapshot(dirname($this->overlayBase()));
        unset($overlay_tree['/mtest_de.po'], $overlay_before_tree['/mtest_de.po']);
        $this->assertSame($overlay_before_tree, $overlay_tree, 'no new revision, nothing removed - only the .po gets the marks');
        $this->assertSame(['mtest' => $this->shippedPo()], $result['written']);
        $this->assertSame('A (lokal)', $this->readShippedPo()->find(self::MODULE, 'aaa')?->getTranslation(), 'the shipped .po was written');
        $this->assertSame($pointer_before, (string) file_get_contents(MigratedLanguageFilePaths::buildPointerFile(MigratedPoFixture::artifactDirectory())));
        $merged_entry = MigratedPoFixture::readPo($this->overlayBase() . '.po')->find(null, 'aaa');
        $this->assertSame('A (lokal)', $merged_entry?->getTranslation(), 'the value stays in the overlay');
        $this->assertTrue(\ILIAS\Language\ComponentTranslation\LocalChangeComments::isMergedIntoShipped($merged_entry), 'marked as taken over');
        $this->assertNotSame($overlay_before, (string) file_get_contents($this->overlayBase() . '.po'));
        $this->assertSame($current_before, (string) file_get_contents(MigratedPoFixture::overlayCurrent($this->overlayBase())));
        $this->assertSame(
            'A (lokal)',
            (new TranslationGettextRepository())->getText(self::MODULE, self::LANG, 'aaa', $this->client_data_dir),
            'served from the overlay until the next setup build/update'
        );
    }

    /**
     * Another write of the module after merge but before "setup build" (an admin edit of another key, an import, "setup
     * update" without build) must not drop the taken-over entry: its value equals the new shipped one, but the build
     * still serves the former `.po`. Only after "setup build" does the next reconciling write drop it.
     */
    public function testATakenOverValueStaysServedUntilTheBuildServesTheWrittenPo(): void
    {
        $this->seedShipped(['aaa' => 'A', 'bbb' => 'B']);
        $this->seedOverlay(['aaa' => 'A (lokal)', 'bbb' => 'B']);
        MigratedPoFixture::build($this->manager, ILIAS_ABSOLUTE_PATH);

        $this->merge();
        $this->seedOverlay(['aaa' => 'A (lokal)', 'bbb' => 'B (lokal)']);

        $overlay = MigratedPoFixture::readPo($this->overlayBase() . '.po');
        $this->assertSame('A (lokal)', $overlay->find(null, 'aaa')?->getTranslation(), 'kept although it equals the shipped value now');
        $this->assertSame('A (lokal)', (new TranslationGettextRepository())->getText(self::MODULE, self::LANG, 'aaa', $this->client_data_dir));
        $this->assertSame('B (lokal)', (new TranslationGettextRepository())->getText(self::MODULE, self::LANG, 'bbb', $this->client_data_dir));

        // "setup build", then "setup update" (a reconciling write)
        MigratedPoFixture::build($this->manager, ILIAS_ABSOLUTE_PATH);
        MigratedLanguageFileSync::sync(
            $this->manager,
            ILIAS_ABSOLUTE_PATH,
            self::LANG,
            self::MODULE,
            ['aaa' => 'A (lokal)', 'bbb' => 'B (lokal)'],
            $this->client_data_dir,
            true
        );

        $overlay = MigratedPoFixture::readPo($this->overlayBase() . '.po');
        $this->assertNull($overlay->find(null, 'aaa'), 'served by the build now');
        $this->assertSame('B (lokal)', $overlay->find(null, 'bbb')?->getTranslation());
        $this->assertSame('A (lokal)', (new TranslationGettextRepository())->getText(self::MODULE, self::LANG, 'aaa', $this->client_data_dir));
    }

    /**
     * @param array<string, string> $entries
     */
    private function reconcile(array $entries): void
    {
        MigratedLanguageFileSync::sync($this->manager, ILIAS_ABSOLUTE_PATH, self::LANG, self::MODULE, $entries, $this->client_data_dir, true);
    }

    public function testATakenOverPluralMessageStaysServedUntilTheBuildServesTheWrittenPo(): void
    {
        $this->seedShippedPlural(['items' => ['Element', 'Elemente']]);
        $local = ['items [0]' => 'Ein Ding', 'items [1]' => 'Dinge'];
        $this->seedOverlay($local);
        MigratedPoFixture::build($this->manager, ILIAS_ABSOLUTE_PATH);

        $this->merge();
        $this->assertSame(['Ein Ding', 'Dinge'], $this->readShippedPo()->find(self::MODULE, 'items')?->getPluralTranslations(), 'precondition: taken over');
        $this->reconcile($local);

        $overlay_entry = MigratedPoFixture::readPo($this->overlayBase() . '.po')->find(null, 'items');
        $this->assertNotNull($overlay_entry, 'kept although it equals the shipped message now');
        $this->assertSame(['Ein Ding', 'Dinge'], $overlay_entry->getPluralTranslations());
        $this->assertSame('Ein Ding', (new TranslationGettextRepository())->getPluralText(self::MODULE, self::LANG, 'items', 1, $this->client_data_dir));
        $this->assertSame('Dinge', (new TranslationGettextRepository())->getPluralText(self::MODULE, self::LANG, 'items', 2, $this->client_data_dir));

        MigratedPoFixture::build($this->manager, ILIAS_ABSOLUTE_PATH);
        $this->reconcile($local);

        $this->assertFileDoesNotExist($this->overlayBase() . '.po', 'served by the build now, nothing left to keep');
        $this->assertSame('Ein Ding', (new TranslationGettextRepository())->getPluralText(self::MODULE, self::LANG, 'items', 1, $this->client_data_dir));
        $this->assertSame('Dinge', (new TranslationGettextRepository())->getPluralText(self::MODULE, self::LANG, 'items', 2, $this->client_data_dir));
    }

    public function testTheMarkOfAPluralMessageIsDroppedOnceTheBuildServesTheWrittenPoAndTheMessageIsChangedAgain(): void
    {
        $this->seedShippedPlural(['items' => ['Element', 'Elemente']]);
        $this->seedOverlay(['items [0]' => 'Ein Ding', 'items [1]' => 'Dinge']);
        MigratedPoFixture::build($this->manager, ILIAS_ABSOLUTE_PATH);
        $this->merge();
        $this->assertTrue(\ILIAS\Language\ComponentTranslation\LocalChangeComments::isMergedIntoShipped(
            MigratedPoFixture::readPo($this->overlayBase() . '.po')->find(null, 'items')
        ), 'precondition');
        MigratedPoFixture::build($this->manager, ILIAS_ABSOLUTE_PATH);

        $this->reconcile(['items [0]' => 'Ein Ding', 'items [1]' => 'Viele Dinge']);

        $entry = MigratedPoFixture::readPo($this->overlayBase() . '.po')->find(null, 'items');
        $this->assertSame(['Ein Ding', 'Viele Dinge'], $entry?->getPluralTranslations());
        $this->assertFalse(\ILIAS\Language\ComponentTranslation\LocalChangeComments::isMergedIntoShipped($entry));
    }

    /**
     * The mark only keeps the value that was taken over: an administrator who changes it before the
     * next build has a local change of his own - for good, not only until the build.
     */
    public function testAMarkedEntryChangedToAThirdValueBeforeTheBuildIsAPlainLocalChange(): void
    {
        $this->seedShipped(['aaa' => 'A']);
        $this->seedOverlay(['aaa' => 'A (lokal)']);
        MigratedPoFixture::build($this->manager, ILIAS_ABSOLUTE_PATH);
        $this->merge();

        $this->reconcile(['aaa' => 'A (dritte)']);

        $entry = MigratedPoFixture::readPo($this->overlayBase() . '.po')->find(null, 'aaa');
        $this->assertSame('A (dritte)', $entry?->getTranslation());
        $this->assertFalse(\ILIAS\Language\ComponentTranslation\LocalChangeComments::isMergedIntoShipped($entry), 'a changed value is no taken over one any more - already before the build');
        $this->assertSame('A (dritte)', (new TranslationGettextRepository())->getText(self::MODULE, self::LANG, 'aaa', $this->client_data_dir));

        MigratedPoFixture::build($this->manager, ILIAS_ABSOLUTE_PATH);
        $this->reconcile(['aaa' => 'A (dritte)']);

        $entry = MigratedPoFixture::readPo($this->overlayBase() . '.po')->find(null, 'aaa');
        $this->assertSame('A (dritte)', $entry?->getTranslation(), 'still a local change against the written shipped value');
        $this->assertFalse(\ILIAS\Language\ComponentTranslation\LocalChangeComments::isMergedIntoShipped($entry), 'the mark is gone once the build serves the written .po');
        $this->assertSame('A (dritte)', (new TranslationGettextRepository())->getText(self::MODULE, self::LANG, 'aaa', $this->client_data_dir));
    }

    public function testMarkMergedIntoShippedMarksOnlyTheNamedEntriesAndChangesNothingServed(): void
    {
        $this->seedShipped(['aaa' => 'A', 'bbb' => 'B']);
        $this->seedOverlay(['aaa' => 'A (lokal)', 'bbb' => 'B (lokal)']);
        $revision_before = (string) file_get_contents(MigratedPoFixture::overlayCurrent($this->overlayBase()));

        MigratedLanguageFileSync::markMergedIntoShipped($this->manager, self::LANG, self::MODULE, ['aaa', 'unknown'], $this->client_data_dir, ILIAS_ABSOLUTE_PATH);

        $overlay = MigratedPoFixture::readPo($this->overlayBase() . '.po');
        $this->assertTrue(\ILIAS\Language\ComponentTranslation\LocalChangeComments::isMergedIntoShipped($overlay->find(null, 'aaa')));
        $this->assertFalse(\ILIAS\Language\ComponentTranslation\LocalChangeComments::isMergedIntoShipped($overlay->find(null, 'bbb')));
        $this->assertSame('B (lokal)', $overlay->find(null, 'bbb')?->getTranslation());
        $this->assertSame($revision_before, (string) file_get_contents(MigratedPoFixture::overlayCurrent($this->overlayBase())), 'only the .po is written');
    }

    public function testMarkMergedIntoShippedWithoutAnOverlayWritesNothing(): void
    {
        $this->seedShipped(['aaa' => 'A']);

        MigratedLanguageFileSync::markMergedIntoShipped($this->manager, self::LANG, self::MODULE, ['aaa'], $this->client_data_dir, ILIAS_ABSOLUTE_PATH);
        MigratedLanguageFileSync::markMergedIntoShipped($this->manager, self::LANG, self::MODULE, ['aaa'], null, ILIAS_ABSOLUTE_PATH);

        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $this->assertDirectoryDoesNotExist(dirname($this->overlayBase()), 'neither the overlay directory nor a lock file is created');
    }

    public function testMarkMergedIntoShippedWithAnUnreadableOverlayThrowsAndLeavesItAsItIs(): void
    {
        $this->seedShipped(['aaa' => 'A']);
        $this->seedOverlay(['aaa' => 'A (lokal)']);
        file_put_contents($this->overlayBase() . '.po', 'msgid "broken');

        try {
            MigratedLanguageFileSync::markMergedIntoShipped($this->manager, self::LANG, self::MODULE, ['aaa'], $this->client_data_dir, ILIAS_ABSOLUTE_PATH);
            $this->fail('an overlay that cannot be read must be reported');
        } catch (\RuntimeException) {
        }

        $this->assertSame('msgid "broken', file_get_contents($this->overlayBase() . '.po'));
    }

    /**
     * Makes the overlay directory read-only (the marking cannot write its temporary file there) and
     * returns the restore callback - or skips the test if the permission does not bind (root).
     */
    private function makeOverlayDirectoryReadOnly(): void
    {
        $directory = dirname($this->overlayBase());
        chmod($directory, 0555);
        clearstatcache();
        if (is_writable($directory)) {
            chmod($directory, 0775);
            $this->markTestSkipped('The file permissions do not bind in this environment (root).');
        }
    }

    private function restoreOverlayDirectory(): void
    {
        chmod(dirname($this->overlayBase()), 0775);
        clearstatcache();
    }

    public function testAFailingMarkingOfTheOverlayIsReportedAndTheFilesStayWrittenAndTheNextMergeMakesUpForIt(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => 'Servus']);
        $this->makeOverlayDirectoryReadOnly();

        try {
            $calls = [];
            $result = ShippedPoMerger::merge(
                $this->manager,
                ILIAS_ABSOLUTE_PATH,
                self::LANG,
                $this->client_data_dir,
                $this->backup_directory,
                static function (string $module) use (&$calls): void {
                    $calls[] = $module;
                }
            );
        } finally {
            $this->restoreOverlayDirectory();
        }

        $this->assertSame(['mtest' => $this->shippedPo()], $result['written']);
        $this->assertSame([], $result['skipped']);
        $this->assertSame([self::MODULE], $calls, 'the database callback runs regardless');
        $this->assertSame([self::MODULE], array_keys($result['unmarked_overlay']));
        $this->assertNotSame('', $result['unmarked_overlay'][self::MODULE]);
        $this->assertSame([], $result['unwritten_database']);
        $this->assertSame('Servus', $this->readShippedPo()->find(self::MODULE, 'greeting')?->getTranslation());
        $this->assertFalse(
            \ILIAS\Language\ComponentTranslation\LocalChangeComments::isMergedIntoShipped(MigratedPoFixture::readPo($this->overlayBase() . '.po')->find(null, 'greeting')),
            'not marked yet'
        );

        // cause removed: the shipped .po is already written, the marking is made up for
        $second = $this->merge();

        $this->assertSame([], $second['written']);
        $this->assertSame([], $second['unmarked_overlay']);
        $this->assertSame([], $second['skipped']);
        $overlay_entry = MigratedPoFixture::readPo($this->overlayBase() . '.po')->find(null, 'greeting');
        $this->assertTrue(\ILIAS\Language\ComponentTranslation\LocalChangeComments::isMergedIntoShipped($overlay_entry));
        $this->assertSame('Servus', $overlay_entry->getTranslation());
    }

    public function testARepeatedMergeDoesNotRewriteTheAlreadyMarkedOverlay(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => 'Servus']);
        $this->merge();
        $overlay_po = $this->overlayBase() . '.po';
        $content = (string) file_get_contents($overlay_po);
        $in_the_past = time() - 86400;
        touch($overlay_po, $in_the_past);

        $this->merge();
        MigratedLanguageFileSync::markMergedIntoShipped($this->manager, self::LANG, self::MODULE, ['greeting'], $this->client_data_dir, ILIAS_ABSOLUTE_PATH);

        clearstatcache();
        $this->assertSame($in_the_past, filemtime($overlay_po), 'not written again');
        $this->assertSame($content, file_get_contents($overlay_po));
    }

    /**
     * A module whose shipped directory is reached through a symbolic link pointing outside
     * ILIAS_ABSOLUTE_PATH is skipped entirely - AtomicFileWriter refuses to write there (CWE-59), the
     * shipped `.po` stays exactly as it was.
     */
    public function testMergeSkipsAModuleWhoseShippedDirectoryIsASymlinkOutsideTheIliasRoot(): void
    {
        $outside = sys_get_temp_dir() . '/ilias_spm_outside_' . bin2hex(random_bytes(4));
        mkdir($outside, 0775, true);
        MigratedPoFixture::writePo($outside . '/' . self::MODULE . '_' . self::LANG . '.po', MigratedPoFixture::catalog(self::MODULE, ['aaa' => 'A']));
        $linked_relative = 'components/ILIAS/Language/tests/ComponentTranslation/' . basename($this->fixture_directory) . '-link/';
        symlink($outside, rtrim(ILIAS_ABSOLUTE_PATH, '/') . '/' . rtrim($linked_relative, '/'));
        $linked_directory = MigratedPoFixture::directory(self::MODULE, $linked_relative);
        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $linked_directory);
        MigratedLanguageFileSync::sync($manager, ILIAS_ABSOLUTE_PATH, self::LANG, self::MODULE, ['aaa' => 'A (lokal)'], $this->client_data_dir);

        try {
            $result = ShippedPoMerger::merge($manager, ILIAS_ABSOLUTE_PATH, self::LANG, $this->client_data_dir, $this->backup_directory);

            $this->assertSame([], $result['written']);
            $this->assertArrayHasKey(self::MODULE, $result['skipped']);
            $this->assertSame(
                'A',
                TranslationCatalog::fromPoFile($outside . '/' . self::MODULE . '_' . self::LANG . '.po')->find(self::MODULE, 'aaa')?->getTranslation()
            );
        } finally {
            @unlink(rtrim(ILIAS_ABSOLUTE_PATH, '/') . '/' . rtrim($linked_relative, '/'));
            MigratedPoFixture::removeDirectory($outside);
        }
    }
}
