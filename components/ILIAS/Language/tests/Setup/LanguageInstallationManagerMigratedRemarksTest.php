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
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\Language\Setup\LanguageDataNotSavedException;
use ILIAS\Language\Setup\LanguageInstallationManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LanguageInstallationManagerMigratedRemarksTest extends LanguageInstallationManagerMigratedModulesTestCase
{
    /**
     * @param array<string, array<string, string>> $shipped_migrated_modules
     * @param array<string, array<string, string>> $customized_remarks
     * @param array<string, array<string, string>> $shipped_line_comments
     * @return array<string, array<string, string>>
     */
    private function callMigratedModuleRemarks(
        array $shipped_migrated_modules,
        array $customized_remarks = [],
        array $shipped_line_comments = []
    ): array {
        $manager = $this->manager();
        $method = new ReflectionMethod(LanguageInstallationManager::class, 'migratedModuleRemarks');

        return $method->invoke(
            $manager,
            'de',
            $shipped_migrated_modules,
            $customized_remarks,
            $shipped_line_comments,
            $this->root . '/client-data'
        );
    }

    private function seedOverlayWithRemark(string $identifier, string $value, string $remark): void
    {
        $catalog = new TranslationCatalog();
        $entry = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry(self::MODULE, $identifier);
        $entry->translate($value);
        LocalChangeComments::setRemark($entry, $remark);
        $catalog->add($entry);
        MigratedPoFixture::writePair($this->overlayBase(), $catalog);
    }

    /**
     * Merge precedence: lng_data is the base, the overlay overrides it, the customizing file overrides both.
     */
    public function testMigratedModuleRemarksMergesLngDataOverlayAndCustomizingWithCustomizingWinning(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedOverlayWithRemark('greeting', 'Hallo', 'Overlay-Bemerkung');
        $this->collation_rows = [
            ['module' => self::MODULE, 'identifier' => 'greeting', 'remarks' => 'DB-Bemerkung'],
        ];

        $remarks = $this->callMigratedModuleRemarks(
            [self::MODULE => ['greeting' => 'Hallo']],
            [self::MODULE => ['greeting' => 'Customizing-Bemerkung']]
        );

        $this->assertSame(['greeting' => 'Customizing-Bemerkung'], $remarks[self::MODULE]);
    }

    public function testMigratedModuleRemarksOverlayWinsOverLngDataWithoutACustomizingRemark(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedOverlayWithRemark('greeting', 'Hallo', 'Overlay-Bemerkung');
        $this->collation_rows = [
            ['module' => self::MODULE, 'identifier' => 'greeting', 'remarks' => 'DB-Bemerkung'],
        ];

        $remarks = $this->callMigratedModuleRemarks([self::MODULE => ['greeting' => 'Hallo']]);

        $this->assertSame(['greeting' => 'Overlay-Bemerkung'], $remarks[self::MODULE]);
    }

    public function testMigratedModuleRemarksFallsBackToLngDataWithoutAnOverlayOrCustomizingRemark(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->collation_rows = [
            ['module' => self::MODULE, 'identifier' => 'greeting', 'remarks' => 'DB-Bemerkung'],
        ];

        $remarks = $this->callMigratedModuleRemarks([self::MODULE => ['greeting' => 'Hallo']]);

        $this->assertSame(['greeting' => 'DB-Bemerkung'], $remarks[self::MODULE]);
    }

    public function testMigratedModuleRemarksIsEmptyWithoutAnyRemarkAnywhere(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->collation_rows = [];

        $remarks = $this->callMigratedModuleRemarks([self::MODULE => ['greeting' => 'Hallo']]);

        $this->assertSame([], $remarks[self::MODULE] ?? []);
    }

    /**
     * A lng_data remark that is only the carried-over "###" comment of the pre-migration `.lang` line is no remark.
     */
    public function testMigratedModuleRemarksExcludesALngDataRemarkThatMatchesTheShippedLineComment(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->collation_rows = [
            ['module' => self::MODULE, 'identifier' => 'greeting', 'remarks' => 'Alter Zeilenkommentar'],
        ];

        $remarks = $this->callMigratedModuleRemarks(
            [self::MODULE => ['greeting' => 'Hallo']],
            [],
            [self::MODULE => ['greeting' => 'Alter Zeilenkommentar']]
        );

        $this->assertArrayNotHasKey('greeting', $remarks[self::MODULE] ?? []);
    }

    /**
     * Same exclusion for the shipped `.po`'s own "#." note of the identifier.
     */
    public function testMigratedModuleRemarksExcludesALngDataRemarkThatMatchesTheShippedPoNote(): void
    {
        $catalog = new TranslationCatalog();
        $entry = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry(self::MODULE, 'greeting');
        $entry->translate('Hallo');
        $entry->addExtractedComment('PO-Notiz');
        $catalog->add($entry);
        MigratedPoFixture::writePo($this->root . '/components/pilot/lang/pilot_de.po', $catalog);
        $this->collation_rows = [
            ['module' => self::MODULE, 'identifier' => 'greeting', 'remarks' => 'PO-Notiz'],
        ];

        $remarks = $this->callMigratedModuleRemarks([self::MODULE => ['greeting' => 'Hallo']]);

        $this->assertArrayNotHasKey('greeting', $remarks[self::MODULE] ?? []);
    }

    /** A lng_data remark equal to the shipped translator note ("# ") is no remark. */
    public function testMigratedModuleRemarksExcludesALngDataRemarkThatMatchesTheShippedTranslatorNote(): void
    {
        $catalog = new TranslationCatalog();
        $entry = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry(self::MODULE, 'greeting');
        $entry->translate('Hallo');
        $entry->addTranslatorNote('Eigene Notiz');
        $catalog->add($entry);
        MigratedPoFixture::writePo($this->root . '/components/pilot/lang/pilot_de.po', $catalog);
        $this->collation_rows = [
            ['module' => self::MODULE, 'identifier' => 'greeting', 'remarks' => 'Eigene Notiz'],
        ];
        $overlay = MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Hallo']);
        LocalChangeComments::setRemark($overlay->find(self::MODULE, 'greeting'), ' Eigene Notiz ');
        MigratedPoFixture::writePair($this->overlayBase(), $overlay);

        $remarks = $this->callMigratedModuleRemarks([self::MODULE => ['greeting' => 'Hallo']]);

        $this->assertArrayNotHasKey('greeting', $remarks[self::MODULE] ?? []);
    }

    /**
     * A remark taken over by "merge" is a note of the shipped entry now: an overlay remark equal
     * to it (trimmed) is dropped, a differing one is kept.
     */
    public function testMigratedModuleRemarksDropsAnOverlayRemarkTheShippedPoHasAsNoteNow(): void
    {
        $catalog = new TranslationCatalog();
        foreach (['greeting' => 'Merk', 'other' => 'Notiz'] as $identifier => $note) {
            $entry = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry(self::MODULE, $identifier);
            $entry->translate('Wert');
            $entry->addExtractedComment($note);
            $catalog->add($entry);
        }
        MigratedPoFixture::writePo($this->root . '/components/pilot/lang/pilot_de.po', $catalog);
        $overlay = MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Wert', 'other' => 'Wert']);
        LocalChangeComments::setRemark($overlay->find(self::MODULE, 'greeting'), '  Merk ');
        LocalChangeComments::setRemark($overlay->find(self::MODULE, 'other'), 'Eigene Bemerkung');
        MigratedPoFixture::writePair($this->overlayBase(), $overlay);

        $remarks = $this->callMigratedModuleRemarks([self::MODULE => ['greeting' => 'Wert', 'other' => 'Wert']]);

        $this->assertSame(['other' => 'Eigene Bemerkung'], $remarks[self::MODULE]);
    }

    /**
     * Whole way of a local change with a remark: "merge" takes value and remark into the shipped `.po`, "setup build"
     * builds it, "setup update" leaves neither entry nor remark in the overlay (file gone).
     */
    public function testAMergedValueAndRemarkLeaveTheOverlayWithTheUpdateAfterTheBuild(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $directories = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $this->directory);
        MigratedLanguageFileSync::sync($directories, $this->root, 'de', self::MODULE, ['greeting' => 'Servus'], $this->root . '/client-data', false, null, ['greeting' => 'Merk']);
        $this->assertSame('Merk', MigratedLanguageFileSync::loadRemarks($directories, 'de', self::MODULE, $this->root . '/client-data', $this->root)['greeting'] ?? null, 'precondition');

        mkdir($this->root . '/backup');
        $result = \ILIAS\Language\ComponentTranslation\ShippedPoMerger::merge($directories, $this->root, 'de', $this->root . '/client-data', $this->root . '/backup');
        $this->assertSame([], $result['skipped'], 'precondition');
        MigratedPoFixture::build($directories, $this->root);
        $this->manager()->insertLanguageForInstallation('de');

        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $shipped = MigratedPoFixture::readPo($this->root . '/components/pilot/lang/pilot_de.po')->find(self::MODULE, 'greeting');
        $this->assertSame('Servus', $shipped?->getTranslation());
        $this->assertSame(['Merk'], $shipped->getTranslatorComments());
        $this->assertSame('Servus', $this->lngModules()[self::MODULE]['greeting']);
        $this->assertSame('Servus', \ILIAS\Language\ComponentTranslation\MigratedTranslations::text(self::MODULE, 'de', 'greeting', $this->root . '/client-data'));
    }

    /**
     * A dated "... new variable" placeholder (the legacy "not translated yet" marker) is never an administrator's
     * remark, whether or not it matches a shipped comment.
     */
    public function testMigratedModuleRemarksExcludesADatedNewVariableMarkerRemark(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->collation_rows = [
            ['module' => self::MODULE, 'identifier' => 'greeting', 'remarks' => '28 08 2012 new variable'],
        ];

        $remarks = $this->callMigratedModuleRemarks([self::MODULE => ['greeting' => 'Hallo']]);

        $this->assertArrayNotHasKey('greeting', $remarks[self::MODULE] ?? []);
    }

    /**
     * A lng_data remark that differs from every shipped comment is a real edit and is taken over.
     */
    public function testMigratedModuleRemarksKeepsALngDataRemarkThatDiffersFromEveryShippedComment(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->collation_rows = [
            ['module' => self::MODULE, 'identifier' => 'greeting', 'remarks' => 'Echte Admin-Bemerkung'],
        ];

        $remarks = $this->callMigratedModuleRemarks(
            [self::MODULE => ['greeting' => 'Hallo']],
            [],
            [self::MODULE => ['greeting' => 'Ganz anderer Zeilenkommentar']]
        );

        $this->assertSame('Echte Admin-Bemerkung', $remarks[self::MODULE]['greeting'] ?? null);
    }
}
