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
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\TestCase;

/**
 * MigratedLanguageFileSync's admin remarks (sync()'s $remarks, loadRemarks(), setRemarks()): a remark is kept in the
 * overlay independent of the value - even for a shipped value, as a "remark only" entry with empty msgstr (the compiled
 * `.mo` and served value never change) - and never leaks into loadModuleTranslations() or loadLocalChanges().
 */
class MigratedLanguageFileSyncRemarksTest extends TestCase
{
    private const string MODULE = 'rtest';

    private string $fixture_directory;
    private string $client_data_dir;
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

        $this->fixture_directory = __DIR__ . '/tmp-remarks-sync-fixtures-' . bin2hex(random_bytes(4));
        mkdir($this->fixture_directory, 0775, true);
        $this->client_data_dir = sys_get_temp_dir() . '/ilias_mlfs_remarks_test_' . bin2hex(random_bytes(4));
        mkdir($this->client_data_dir, 0775, true);

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

        parent::tearDown();
    }

    private function shippedPo(string $lang_key = 'de'): string
    {
        return $this->fixture_directory . '/' . self::MODULE . '_' . $lang_key . '.po';
    }

    private function overlayBase(string $lang_key = 'de'): string
    {
        return MigratedPoFixture::overlayBase($this->client_data_dir, $this->directory, $lang_key);
    }

    private function overlayPo(): TranslationCatalog
    {
        return MigratedPoFixture::readPo($this->overlayBase() . '.po');
    }

    private function overlayEntry(string $identifier): ?TranslationEntry
    {
        return $this->overlayPo()->find(null, $identifier);
    }

    /**
     * @param array<string, string|list<string>> $entries plain value, or the forms of a plural
     *        message (msgstr[0], msgstr[1], ...) of the identifier
     */
    private function seedShipped(array $entries, string $header = 'nplurals=2; plural=(n != 1);'): void
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $catalog->setHeader('Plural-Forms', $header);
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
     * @param array<string, string> $entries identifier => value
     * @param array<string, string>|null $remarks identifier => remark, `null` keeps the overlay's own
     */
    private function sync(array $entries, ?array $remarks = null): void
    {
        MigratedLanguageFileSync::sync(
            $this->manager,
            ILIAS_ABSOLUTE_PATH,
            'de',
            self::MODULE,
            $entries,
            $this->client_data_dir,
            false,
            null,
            $remarks
        );
    }

    /**
     * A remark on a key whose value is exactly the shipped one still belongs in the overlay - as a
     * "remark only" entry with an empty msgstr in the `.po`, absent from readMoTranslations()'s
     * result (the compiled `.mo` leaves it out entirely, so nothing served changes).
     */
    public function testARemarkOnAnUnchangedValueCreatesARemarkOnlyEntryAbsentFromTheCompiledMo(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->sync(['greeting' => 'Hallo'], ['greeting' => 'Bitte prüfen']);

        $entry = $this->overlayEntry('greeting');
        $this->assertNotNull($entry, 'the remark-only entry must be in the overlay .po');
        $this->assertSame('', $entry->getTranslation(), 'empty msgstr - the value itself is unchanged');
        $this->assertSame('Bitte prüfen', LocalChangeComments::getRemark($entry));
        $this->assertArrayNotHasKey('greeting', MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    /**
     * loadModuleTranslations() (what ilLanguage ultimately reads) and loadLocalChanges() (the admin
     * GUI's per-key overlay state) both skip a remark-only entry entirely - it carries no value.
     */
    public function testARemarkOnlyEntryIsSkippedByLoadModuleTranslationsAndLoadLocalChanges(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->sync(['greeting' => 'Hallo'], ['greeting' => 'Bitte prüfen']);

        $translations = MigratedLanguageFileSync::loadModuleTranslations($this->manager, 'de', self::MODULE, $this->client_data_dir);
        $this->assertSame('Hallo', $translations['greeting']['value'] ?? null);
        $this->assertFalse($translations['greeting']['local_change']);

        $local_changes = MigratedLanguageFileSync::loadLocalChanges($this->manager, 'de', self::MODULE, $this->client_data_dir);
        $this->assertArrayNotHasKey('greeting', $local_changes, 'a remark-only entry is not a local change of the value');
    }

    public function testLoadRemarksReadsTheRemarkOfARemarkOnlyEntry(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->sync(['greeting' => 'Hallo'], ['greeting' => 'Bitte prüfen']);

        $remarks = MigratedLanguageFileSync::loadRemarks($this->manager, 'de', self::MODULE, $this->client_data_dir);

        $this->assertSame(['greeting' => 'Bitte prüfen'], $remarks);
    }

    public function testLoadRemarksIsEmptyWithoutAnyOverlayAtAll(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->assertSame([], MigratedLanguageFileSync::loadRemarks($this->manager, 'de', self::MODULE, $this->client_data_dir));
    }

    /**
     * $remarks = null keeps the overlay's existing remarks: a value-only sync() (ordinary GUI save,
     * writePluralRows()/sync() of ilObjLanguageExt) must not wipe a remark.
     */
    public function testNullRemarksKeepsTheExistingOverlayRemark(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->sync(['greeting' => 'Hallo'], ['greeting' => 'Bitte prüfen']);

        $this->sync(['greeting' => 'Servus']); // remarks omitted (null) - only the value changes

        $entry = $this->overlayEntry('greeting');
        $this->assertNotNull($entry);
        $this->assertSame('Servus', $entry->getTranslation());
        $this->assertSame('Bitte prüfen', LocalChangeComments::getRemark($entry), 'the remark must survive a remarks-less sync()');
    }

    /**
     * $remarks = [] removes every remark of the module - and, once the value is back to shipped too,
     * the overlay disappears entirely (nothing left to keep).
     */
    public function testEmptyArrayRemarksRemovesEveryRemarkAndDropsTheOverlayIfNothingElseIsLeft(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->sync(['greeting' => 'Hallo'], ['greeting' => 'Bitte prüfen']);
        $this->assertNotNull($this->overlayEntry('greeting'), 'precondition');

        $this->sync(['greeting' => 'Hallo'], []);

        $this->assertFalse(is_file($this->overlayBase() . '.po'), 'nothing left to keep - the whole overlay is removed');
    }

    /**
     * A later real value change on the very same key keeps its remark - the "remark only" entry is
     * simply replaced by an ordinary one carrying both the new value and the remark.
     */
    public function testARealValueChangeOnTheSameKeyKeepsItsRemark(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->sync(['greeting' => 'Hallo'], ['greeting' => 'Bitte prüfen']);

        $this->sync(['greeting' => 'Servus'], ['greeting' => 'Bitte prüfen']);

        $entry = $this->overlayEntry('greeting');
        $this->assertNotNull($entry);
        $this->assertSame('Servus', $entry->getTranslation());
        $this->assertSame('Bitte prüfen', LocalChangeComments::getRemark($entry));
        $this->assertSame('Servus', MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase()))['greeting'] ?? null);
    }

    /**
     * A plural message's remark belongs to its base identifier (never a form key) - even when only
     * the remark, not any form's value, changed.
     */
    public function testAPluralMessagesRemarkBelongsToItsBaseIdentifier(): void
    {
        $this->seedShipped(['item' => ['Eintrag', 'Einträge']]);

        $this->sync(
            ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'],
            ['item' => 'Bitte prüfen']
        );

        $entry = $this->overlayEntry('item');
        $this->assertNotNull($entry);
        $this->assertTrue($entry->isPlural());
        $this->assertSame(['', ''], $entry->getPluralTranslations(), 'remark only - both forms stay empty');
        $this->assertSame('Bitte prüfen', LocalChangeComments::getRemark($entry));
        $this->assertArrayNotHasKey('item', MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    /**
     * setRemarks() changes exactly the given identifiers' remarks under the overlay lock, leaving
     * every value and every other remark untouched.
     */
    public function testSetRemarksChangesOnlyTheGivenIdentifiersRemarks(): void
    {
        $this->seedShipped(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->sync(['greeting' => 'Hallo', 'farewell' => 'Tschüss'], ['greeting' => 'Alte Bemerkung']);

        MigratedLanguageFileSync::setRemarks(
            $this->manager,
            'de',
            self::MODULE,
            ['greeting' => 'Neue Bemerkung', 'farewell' => 'Auch geprüft'],
            $this->client_data_dir
        );

        $remarks = MigratedLanguageFileSync::loadRemarks($this->manager, 'de', self::MODULE, $this->client_data_dir);
        // order is not a guaranteed contract of loadRemarks() - only the content is
        ksort($remarks);
        $this->assertSame(['farewell' => 'Auch geprüft', 'greeting' => 'Neue Bemerkung'], $remarks);
    }

    public function testSetRemarksWithNullOrEmptyStringRemovesTheRemark(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->sync(['greeting' => 'Hallo'], ['greeting' => 'Alte Bemerkung']);

        MigratedLanguageFileSync::setRemarks($this->manager, 'de', self::MODULE, ['greeting' => null], $this->client_data_dir);

        $this->assertSame([], MigratedLanguageFileSync::loadRemarks($this->manager, 'de', self::MODULE, $this->client_data_dir));
    }

    /**
     * A remark that is not valid UTF-8 (e.g. stale lng_data content from before remarks were
     * validated) is dropped with a warning instead of making sync() throw or the overlay unreadable -
     * every other entry (here: a second, valid remark) is still written.
     */
    public function testSyncDropsAnInvalidUtf8RemarkButWritesEveryOtherEntry(): void
    {
        $this->seedShipped(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);

        // no logger is registered in this test process - the warning falls back to error_log(),
        // which would otherwise print to this test's own output and mark it risky
        $previous_error_log = ini_set('error_log', sys_get_temp_dir() . '/ilias_mlfs_remarks_test_error.log');
        try {
            $this->sync(
                ['greeting' => 'Hallo', 'farewell' => 'Tschüss'],
                ['greeting' => "Ung\xFCltig", 'farewell' => 'Gültige Bemerkung']
            );
        } finally {
            ini_set('error_log', (string) $previous_error_log);
        }

        $remarks = MigratedLanguageFileSync::loadRemarks($this->manager, 'de', self::MODULE, $this->client_data_dir);
        $this->assertArrayNotHasKey('greeting', $remarks);
        $this->assertSame('Gültige Bemerkung', $remarks['farewell'] ?? null);
    }

    /**
     * A safety net independent of the remark check above: if the compiled overlay content would not
     * be valid UTF-8 for any other reason (here: an invalid value, not a remark), sync() throws
     * instead of writing an unreadable file - fromPoFile() would refuse to read it back, losing every
     * local change of the module.
     */
    public function testSyncThrowsInsteadOfWritingAnOverlayThatWouldNotBeValidUtf8(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/would not be valid UTF-8/');
        $this->sync(['greeting' => "Ung\xFCltig"]);
    }

    /**
     * Only entries actually flagged "fuzzy" in the shipped `.po` are listed - a plural message's every
     * form key, a singular message its plain identifier.
     */
    public function testLoadShippedFuzzyIdentifiersListsFuzzyEntriesOnly(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $fuzzy_singular = new TranslationEntry(self::MODULE, 'fuzzy_one');
        $fuzzy_singular->translate('Noch nicht übersetzt');
        $fuzzy_singular->addFlag('fuzzy');
        $catalog->add($fuzzy_singular);
        $clean_singular = new TranslationEntry(self::MODULE, 'clean');
        $clean_singular->translate('Übersetzt');
        $catalog->add($clean_singular);
        $fuzzy_plural = new TranslationEntry(self::MODULE, 'item');
        $fuzzy_plural->setPlural('items', ['Eintrag', 'Einträge']);
        $fuzzy_plural->addFlag('fuzzy');
        $catalog->add($fuzzy_plural);
        MigratedPoFixture::writePo($this->shippedPo(), $catalog);

        $fuzzy = MigratedLanguageFileSync::loadShippedFuzzyIdentifiers($this->shippedPo(), self::MODULE);

        $this->assertSame(
            ['fuzzy_one' => true, 'item [0]' => true, 'item [1]' => true],
            $fuzzy
        );
    }

    public function testLoadShippedFuzzyIdentifiersIsEmptyWithoutAnyFuzzyEntry(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->assertSame([], MigratedLanguageFileSync::loadShippedFuzzyIdentifiers($this->shippedPo(), self::MODULE));
    }
}
