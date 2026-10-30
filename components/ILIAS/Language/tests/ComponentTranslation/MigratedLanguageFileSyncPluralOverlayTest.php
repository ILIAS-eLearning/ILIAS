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
 * MigratedLanguageFileSync::sync() for a plural message: what the overlay `.po` holds when only some forms are locally
 * changed - silently losing a form or writing a phantom entry are the risks. Same fixture layout as
 * MigratedLanguageFileSyncTest.
 */
class MigratedLanguageFileSyncPluralOverlayTest extends TestCase
{
    use RestoresProcessState;

    private const string MODULE = 'ptest';

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

        $this->fixture_directory = __DIR__ . '/tmp-plural-sync-fixtures-' . bin2hex(random_bytes(4));
        mkdir($this->fixture_directory, 0775, true);
        $this->client_data_dir = sys_get_temp_dir() . '/ilias_mlfs_plural_test_' . bin2hex(random_bytes(4));
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
     * @param array<string, string> $entries identifier => value, form keys (PluralFormKey::of())
     *        included for a plural message
     */
    private function sync(array $entries, bool $refresh = false): void
    {
        MigratedLanguageFileSync::sync(
            $this->manager,
            ILIAS_ABSOLUTE_PATH,
            'de',
            self::MODULE,
            $entries,
            $this->client_data_dir,
            $refresh
        );
    }

    private function overlayEntry(string $identifier): ?TranslationEntry
    {
        return $this->overlayPo()->find(null, $identifier);
    }

    /**
     * @param array<string, string> $dates identifier or form key => database timestamp
     */
    private function syncWithLocalChangeDates(array $dates): void
    {
        MigratedLanguageFileSync::sync(
            $this->manager,
            ILIAS_ABSOLUTE_PATH,
            'de',
            self::MODULE,
            ['item [0]' => 'Eintrag NEU', 'item [1]' => 'Einträge NEU'],
            $this->client_data_dir,
            false,
            null,
            null,
            $dates
        );
    }

    /**
     * A new plural overlay entry whose dates are known only per form (rows "item [0]", "item [1]" of
     * lng_data) gets the latest of them as "local_change" - neither "now" nor the first form's date;
     * a form of another message ("itemx [0]") is not considered.
     */
    public function testANewPluralEntryTakesTheLatestLocalChangeDateOfItsForms(): void
    {
        $this->seedShipped(['item' => ['Eintrag', 'Einträge']]);

        $this->syncWithLocalChangeDates([
            'item [0]' => '2021-05-06 07:08:09',
            'item [1]' => '2020-01-02 03:04:05',
            'itemx [0]' => '2023-01-01 00:00:00',
        ]);

        $this->assertSame('2021-05-06T07:08:09Z', LocalChangeComments::getLocalChange($this->overlayEntry('item')));
    }

    /**
     * A date under the identifier itself wins over the dates of the forms; an unreadable date of a
     * form is ignored.
     */
    public function testADateUnderThePluralIdentifierWinsOverTheFormDates(): void
    {
        $this->seedShipped(['item' => ['Eintrag', 'Einträge']]);

        $this->syncWithLocalChangeDates(['item' => '2019-02-03 04:05:06', 'item [0]' => '2021-05-06 07:08:09']);

        $this->assertSame('2019-02-03T04:05:06Z', LocalChangeComments::getLocalChange($this->overlayEntry('item')));
    }

    public function testAnUnreadableFormDateIsIgnoredAndWithoutAnyReadableDateTheEntryGetsTheCurrentTime(): void
    {
        $this->seedShipped(['item' => ['Eintrag', 'Einträge']]);
        $before = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $this->syncWithLocalChangeDates(['item [0]' => 'garbage', 'item [1]' => '2021-05-06 07:08:09']);
        $this->assertSame('2021-05-06T07:08:09Z', LocalChangeComments::getLocalChange($this->overlayEntry('item')));

        $this->tearDownOverlay();
        $this->syncWithLocalChangeDates(['item [0]' => 'garbage']);
        $change = LocalChangeComments::getLocalChange($this->overlayEntry('item'));
        $this->assertNotNull($change);
        $this->assertGreaterThanOrEqual($before->format('Y-m-d\TH:i:s\Z'), $change);
    }

    private function tearDownOverlay(): void
    {
        MigratedPoFixture::removeDirectory($this->client_data_dir);
        mkdir($this->client_data_dir, 0775, true);
    }

    /**
     * Only one of two forms is locally changed: the overlay still carries the whole plural entry
     * (both forms - not just the changed one), and gets an "original" comment per form (the shipped
     * value each form is compared against later).
     */
    public function testOnlyOneFormChangedWritesTheWholeEntryWithAnOriginalPerForm(): void
    {
        $this->seedShipped(['item' => ['Eintrag', 'Einträge']]);

        $this->sync(['item [0]' => 'Eintrag', 'item [1]' => 'Einträge NEU']);

        $entry = $this->overlayEntry('item');
        $this->assertNotNull($entry, 'the plural message must be in the overlay');
        $this->assertTrue($entry->isPlural());
        $this->assertSame(['Eintrag', 'Einträge NEU'], $entry->getPluralTranslations());
        $this->assertSame('Eintrag', LocalChangeComments::getOriginal($entry, 0));
        $this->assertSame('Einträge', LocalChangeComments::getOriginal($entry, 1));
    }

    /**
     * Once every form is back to its shipped value, the whole entry disappears from the overlay - a
     * still-locally-changed, unrelated entry of the same module stays untouched.
     */
    public function testAllFormsBackToShippedRemovesTheEntryButKeepsOtherLocalChanges(): void
    {
        $this->seedShipped(['item' => ['Eintrag', 'Einträge'], 'plain' => 'X']);
        $this->sync(['item [0]' => 'Eintrag', 'item [1]' => 'GEÄNDERT', 'plain' => 'Anders']);
        $this->assertNotNull($this->overlayEntry('item'), 'precondition: the plural message is in the overlay');

        $this->sync(['item [0]' => 'Eintrag', 'item [1]' => 'Einträge', 'plain' => 'Anders']);

        $this->assertNull($this->overlayEntry('item'));
        $plain = $this->overlayEntry('plain');
        $this->assertNotNull($plain, 'the unrelated local change must survive');
        $this->assertSame('Anders', $plain->getTranslation());
    }

    /**
     * Submitting an empty value for one form resets exactly that form to its shipped value - even
     * while the overlay already held a different, locally changed value for it - without touching the
     * other, still locally changed form.
     */
    public function testAnEmptyFormValueResetsOnlyThatFormToShippedKeepingTheOtherFormsLocalChange(): void
    {
        $this->seedShipped(['item' => ['Eintrag', 'Einträge']]);
        $this->sync(['item [0]' => 'FORM0-LOKAL', 'item [1]' => 'FORM1-LOKAL']);

        $this->sync(['item [0]' => 'FORM0-LOKAL', 'item [1]' => '']);

        $entry = $this->overlayEntry('item');
        $this->assertNotNull($entry);
        $this->assertSame(['FORM0-LOKAL', 'Einträge'], $entry->getPluralTranslations());
    }

    /**
     * $refresh_original_from_shipped=true (a Setup update) updates every form's "original" to the new
     * shipped value - including the one form that is not locally overridden, whose value itself then
     * simply follows the new shipped value too - while a genuinely locally changed form keeps its own
     * value.
     */
    public function testRefreshOriginalFromShippedUpdatesOriginalOfEveryFormToTheNewShippedValue(): void
    {
        $this->seedShipped(['item' => ['Eintrag', 'Einträge']]);
        $this->sync(['item [0]' => 'FORM0-LOKAL', 'item [1]' => 'Einträge']);
        $entry = $this->overlayEntry('item');
        $this->assertNotNull($entry);
        $this->assertSame('Einträge', LocalChangeComments::getOriginal($entry, 1), 'precondition');

        // the shipped file changes only form 1
        $this->seedShipped(['item' => ['Eintrag', 'Einträge NEU']]);
        $this->sync(['item [0]' => 'FORM0-LOKAL', 'item [1]' => 'Einträge NEU'], refresh: true);

        $entry = $this->overlayEntry('item');
        $this->assertNotNull($entry);
        $this->assertSame(['FORM0-LOKAL', 'Einträge NEU'], $entry->getPluralTranslations());
        $this->assertSame('Eintrag', LocalChangeComments::getOriginal($entry, 0));
        $this->assertSame('Einträge NEU', LocalChangeComments::getOriginal($entry, 1));
    }

    /**
     * Without $refresh_original_from_shipped (an ordinary form save), the "original" of an existing
     * overlay entry is left exactly as it was, even though the shipped file changed in the meantime -
     * only an explicit refresh (Setup update / "remove local changes") may move it.
     */
    public function testWithoutRefreshTheExistingOriginalIsLeftUntouchedWhenTheShippedValueChanges(): void
    {
        $this->seedShipped(['item' => ['Eintrag', 'Einträge']]);
        $this->sync(['item [0]' => 'FORM0-LOKAL', 'item [1]' => 'Einträge']);

        $this->seedShipped(['item' => ['Eintrag', 'Einträge NEU']]);
        // form 1's value must be resubmitted as its (new) current value to stay out of the delta -
        // an ordinary caller always reads currentModuleContent() first, which already reflects it
        $this->sync(['item [0]' => 'FORM0-LOKAL', 'item [1]' => 'Einträge NEU'], refresh: false);

        $entry = $this->overlayEntry('item');
        $this->assertNotNull($entry);
        $this->assertSame('Einträge', LocalChangeComments::getOriginal($entry, 1), 'original stays the OLD shipped value');
    }
}
