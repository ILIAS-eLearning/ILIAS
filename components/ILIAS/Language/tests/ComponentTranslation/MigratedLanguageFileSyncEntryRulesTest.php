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

use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Entry rules of MigratedLanguageFileSync: delta/reset decisions, database sanitising, overlay presence, msgctxt handling.
 */
class MigratedLanguageFileSyncEntryRulesTest extends MigratedLanguageFileSyncTestCase
{
    /** belongsInDelta() alone decides what enters the overlay; tested without sync()'s file I/O. */
    #[DataProvider('belongsInDeltaCases')]
    public function testBelongsInDelta(?string $shipped_value, string $value, bool $expected): void
    {
        $this->assertSame($expected, MigratedLanguageFileSync::belongsInDelta($shipped_value, $value));
    }

    /**
     * @return array<string, array{0: ?string, 1: string, 2: bool}>
     */
    public static function belongsInDeltaCases(): array
    {
        return [
            'differs from shipped' => ['Hallo', 'Servus', true],
            'same as shipped' => ['Hallo', 'Hallo', false],
            'key not shipped at all - any non-empty value belongs' => [null, 'Eigener Wert', true],
            'empty value resets to shipped, even without a shipped key' => [null, '', false],
            'empty value resets to shipped, even when shipped differs' => ['Hallo', '', false],
            'shipped value itself is empty, value matches' => ['', '', false],
            'shipped value itself is empty, value differs' => ['', 'x', true],
            'the literal string "0" belongs when shipped differs' => ['1', '0', true],
            'the literal string "0" is not "empty" and does not reset' => [null, '0', true],
        ];
    }

    /**
     * Shared by belongsInDelta() and ilObjLanguageExt::_saveValues(): an empty value resets to the shipped one, but
     * only for a key the shipped `.po` carries.
     */
    #[DataProvider('resolveLocalValueCases')]
    public function testResolveLocalValue(?string $shipped_value, string $value, string $expected): void
    {
        $this->assertSame($expected, MigratedLanguageFileSync::resolveLocalValue($shipped_value, $value));
    }

    /**
     * @return array<string, array{0: ?string, 1: string, 2: string}>
     */
    public static function resolveLocalValueCases(): array
    {
        return [
            'empty value of a shipped key resolves to the shipped value' => ['Hallo', '', 'Hallo'],
            'empty value of a key that is not shipped stays empty' => [null, '', ''],
            'a non-empty value is returned unchanged, shipped key' => ['Hallo', 'Servus', 'Servus'],
            'a non-empty value is returned unchanged, not a shipped key' => [null, 'Eigener Wert', 'Eigener Wert'],
            'the shipped value itself is empty - stays empty (nothing to reset to in practice)' => ['', '', ''],
        ];
    }

    /**
     * databaseValues() sanitises only an unmodified value equal to the shipped one (for the database mirror); a locally
     * changed value stays untouched, its markup is checked by the caller.
     */
    public function testDatabaseValuesSanitizesOnlyAnUnmodifiedValueEqualToTheShippedOne(): void
    {
        $bad = '<script>alert(1)</script>Hallo';

        $result = MigratedLanguageFileSync::databaseValues(
            ['unmodified' => $bad, 'changed' => $bad, 'not_shipped_at_all' => $bad],
            ['unmodified' => $bad, 'changed' => 'Anderer Shipped-Wert']
        );

        $this->assertStringNotContainsString('<script', $result['unmodified']);
        $this->assertStringContainsString('Hallo', $result['unmodified']);
        // "changed" differs from its shipped value - databaseValues() does not touch it at all
        $this->assertSame($bad, $result['changed']);
        // no shipped counterpart at all - left untouched, too
        $this->assertSame($bad, $result['not_shipped_at_all']);
    }

    public function testDatabaseValuesLeavesAnAlreadyAllowedUnmodifiedValueByteIdentical(): void
    {
        $result = MigratedLanguageFileSync::databaseValues(['greeting' => 'Hallo'], ['greeting' => 'Hallo']);

        $this->assertSame('Hallo', $result['greeting']);
    }

    public function testDatabaseValuesOfSanitizesAgainstTheRealShippedPo(): void
    {
        $bad = '<script>alert(1)</script>Hallo';
        $this->seedShipped(['greeting' => $bad]);

        $result = MigratedLanguageFileSync::databaseValuesOf(
            $this->manager,
            $this->ilias_root,
            'de',
            self::MODULE,
            ['greeting' => $bad]
        );

        $this->assertStringNotContainsString('<script', $result['greeting']);
        $this->assertStringContainsString('Hallo', $result['greeting']);
    }

    /** A module not migrated for the language (no shipped `.po`): $entries come back unchanged. */
    public function testDatabaseValuesOfLeavesEntriesUnchangedWhenTheModuleIsNotMigratedForTheLanguage(): void
    {
        $bad = '<script>alert(1)</script>Hallo';

        $result = MigratedLanguageFileSync::databaseValuesOf(
            $this->manager,
            $this->ilias_root,
            'de',
            'not_a_contributed_module',
            ['greeting' => $bad]
        );

        $this->assertSame($bad, $result['greeting']);
    }

    /**
     * An unreadable shipped `.po` must not abort the database write: $entries come back unchanged (the overlay write
     * reports the file).
     */
    public function testDatabaseValuesOfLeavesEntriesUnchangedWhenTheShippedPoCannotBeRead(): void
    {
        file_put_contents($this->shippedPo(), "msgid \"kaputt\n");
        $bad = '<script>alert(1)</script>Hallo';

        $result = MigratedLanguageFileSync::databaseValuesOf(
            $this->manager,
            $this->ilias_root,
            'de',
            self::MODULE,
            ['greeting' => $bad]
        );

        $this->assertSame($bad, $result['greeting']);
    }

    public function testHasOverlayIsTrueWhenEitherOverlayFileExists(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => 'Servus']);

        $this->assertTrue(MigratedLanguageFileSync::hasOverlay($this->manager, 'de', self::MODULE, $this->client_data_dir));
    }

    public function testHasOverlayIsFalseWithoutAnyOverlayFile(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->assertFalse(MigratedLanguageFileSync::hasOverlay($this->manager, 'de', self::MODULE, $this->client_data_dir));
    }

    public function testHasOverlayIsFalseForAnUnknownModuleOrWithoutClientDataDir(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => 'Servus']);

        $this->assertFalse(MigratedLanguageFileSync::hasOverlay($this->manager, 'de', 'other_module', $this->client_data_dir));
        $this->assertFalse(MigratedLanguageFileSync::hasOverlay($this->manager, 'de', self::MODULE, null));
    }

    /**
     * hasOverlay() reports a non-regular file (e.g. a directory) as present, unlike is_file(); reading it is covered by
     * assertReadableOverlay().
     */
    public function testHasOverlayIsTrueEvenForAnIrregularFileAtTheOverlayPosition(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        mkdir($this->overlayBase() . '.po', 0775, true);

        $this->assertTrue(MigratedLanguageFileSync::hasOverlay($this->manager, 'de', self::MODULE, $this->client_data_dir));
    }

    /** An entry without msgctxt (`null`) belongs to whichever module reads the catalog. */
    public function testModuleEntriesIncludesAnEntryWithoutAnyContext(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->add(MigratedPoFixture::entry(null, 'greeting', 'Hallo'));

        $entries = MigratedLanguageFileSync::moduleEntries($catalog, 'itest');

        $this->assertArrayHasKey('greeting', $entries);
        $this->assertSame('Hallo', $entries['greeting']->getTranslation());
    }

    /** An entry with an empty msgctxt is a foreign context, not "no context". */
    #[DataProvider('foreignContexts')]
    public function testModuleEntriesIgnoresAnEntryOfAForeignContext(string $context): void
    {
        $catalog = new TranslationCatalog();
        $catalog->add(MigratedPoFixture::entry($context, 'greeting', 'Fremd'));

        $entries = MigratedLanguageFileSync::moduleEntries($catalog, 'itest');

        $this->assertArrayNotHasKey('greeting', $entries);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function foreignContexts(): array
    {
        return ['empty-string msgctxt' => [''], 'context of another module' => ['other_module']];
    }

    /** A key with and without context: the module-context entry wins regardless of catalog order. */
    #[DataProvider('contextOrders')]
    public function testModuleEntriesPrefersTheModuleContextEntryOverTheContextlessOne(bool $module_first): void
    {
        $catalog = new TranslationCatalog();
        $module_entry = MigratedPoFixture::entry('itest', 'greeting', 'Mit Modulkontext');
        $contextless = MigratedPoFixture::entry(null, 'greeting', 'Ohne Kontext');
        foreach ($module_first ? [$module_entry, $contextless] : [$contextless, $module_entry] as $entry) {
            $catalog->add($entry);
        }

        $entries = MigratedLanguageFileSync::moduleEntries($catalog, 'itest');

        $this->assertSame('Mit Modulkontext', $entries['greeting']->getTranslation());
    }

    /**
     * @return array<string, array{0: bool}>
     */
    public static function contextOrders(): array
    {
        return ['module context first' => [true], 'contextless first' => [false]];
    }


    public function testFindModuleEntryFallsBackToTheContextlessEntry(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->add(MigratedPoFixture::entry(null, 'greeting', 'Hallo'));

        $entry = MigratedLanguageFileSync::findModuleEntry($catalog, 'itest', 'greeting');

        $this->assertNotNull($entry);
        $this->assertSame('Hallo', $entry->getTranslation());
    }

    public function testFindModuleEntryPrefersTheModuleContextEntry(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->add(MigratedPoFixture::entry(null, 'greeting', 'Ohne Kontext'));
        $catalog->add(MigratedPoFixture::entry('itest', 'greeting', 'Mit Modulkontext'));

        $entry = MigratedLanguageFileSync::findModuleEntry($catalog, 'itest', 'greeting');

        $this->assertSame('Mit Modulkontext', $entry?->getTranslation());
    }

    public function testFindModuleEntryReturnsNullForAForeignContext(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->add(MigratedPoFixture::entry('other_module', 'greeting', 'Fremd'));

        $this->assertNull(MigratedLanguageFileSync::findModuleEntry($catalog, 'itest', 'greeting'));
    }

    /**
     * BUG (documented, not fixed): unlike moduleEntries(), findModuleEntry()'s fallback `$catalog->find(null,
     * $identifier)` also matches an entry with msgctxt "" - gettext/gettext builds the key as
     * "{$context}\004{$original}", so null and '' collide.
     */
    public function testFindModuleEntryIgnoresAnEmptyStringContextInsteadOfTreatingItAsContextless(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->add(MigratedPoFixture::entry('', 'farewell', 'Leer'));

        $this->assertNull(MigratedLanguageFileSync::findModuleEntry($catalog, 'itest', 'farewell'));
    }

    /** End-to-end: a contextless shipped entry is served for the module via loadShippedModuleEntries(). */
    public function testLoadShippedModuleEntriesIncludesAContextlessEntry(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->add(MigratedPoFixture::entry(null, 'greeting', 'Hallo'));
        MigratedPoFixture::writePo($this->shippedPo(), $catalog);

        $entries = MigratedLanguageFileSync::loadShippedModuleEntries($this->shippedPo(), self::MODULE);

        $this->assertSame('Hallo', $entries['greeting']['value']);
    }

    /**
     * End-to-end: sync() reads a contextless shipped entry as the module's shipped value, so resyncing it creates no
     * overlay.
     */
    public function testSyncTreatsAContextlessShippedEntryAsThisModulesShippedValue(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->add(MigratedPoFixture::entry(null, 'greeting', 'Hallo'));
        MigratedPoFixture::writePo($this->shippedPo(), $catalog);

        $this->sync(['greeting' => 'Hallo']);

        $this->assertFileDoesNotExist($this->overlayBase() . '.po', 'unchanged from the contextless shipped value - no delta at all');
    }
}
