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

class LanguageInstallationManagerMigratedPluralFormsTest extends LanguageInstallationManagerMigratedModulesTestCase
{
    /**
     * If the plural rule changes with an update (2 -> 3 forms, e.g. CLDR 49), the overlay is served as written until
     * the next reconciling write (its `.mo` is consistent with the old rule), while the admin view already has the new
     * number of forms, with the local value on every form that serves the old quantities.
     */
    public function testAnExistingOverlayIsServedWithItsOwnRuleUntilTheNextReconcilingWrite(): void
    {
        $this->shipPluralPo(['item' => ['Eintrag', 'Einträge']], self::TWO_FORMS);
        $this->writeLocalOverlay(['item [0]' => 'Eintrag', 'item [1]' => 'LOKAL-VIELE']);
        $this->shipPluralPo(['item' => ['Eintrag', 'Einträge-few', 'Einträge-viele']], self::THREE_FORMS);

        $served = (string) file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase()));
        $this->assertSame(self::TWO_FORMS, MigratedPoFixture::moPluralFormsHeader($served));
        $this->assertSame(['item' => ['Eintrag', 'LOKAL-VIELE']], MigratedPoFixture::readMoPluralForms($served));

        $effective = $this->effective();
        $this->assertSame(['item [0]', 'item [1]', 'item [2]'], array_keys($effective));
        $this->assertSame('LOKAL-VIELE', $effective['item [1]']['value']);
        $this->assertTrue($effective['item [1]']['local_change']);
        $this->assertSame('LOKAL-VIELE', $effective['item [2]']['value'], 'by meaning: n = 0 and 5+ were served by the local form');
        $this->assertTrue($effective['item [2]']['local_change']);
        $this->assertSame('Eintrag', $effective['item [0]']['value']);
        $this->assertFalse($effective['item [0]']['local_change']);
    }

    /**
     * After the write the overlay follows the new rule: new header, exactly the new number of forms, local value and
     * unrelated local change kept; lng_data and lng_modules agree; a second run changes nothing.
     */
    public function testInstallAfterTheNumberOfPluralFormsGrewRewritesTheOverlayWithTheNewRuleAndKeepsLocalValues(): void
    {
        $this->shipPluralPo(['item' => ['Eintrag', 'Einträge'], 'plain' => 'X'], self::TWO_FORMS);
        $this->writeLocalOverlay(['item [0]' => 'Eintrag', 'item [1]' => 'LOKAL-VIELE', 'plain' => 'LOKAL-PLAIN']);
        $this->shipPluralPo(['item' => ['Eintrag', 'Einträge-few', 'Einträge-viele'], 'plain' => 'X'], self::THREE_FORMS);

        $this->assertSame([], $this->manager()->insertLanguageForInstallation('de'));

        $overlay = $this->overlayPo();
        $this->assertSame(self::THREE_FORMS, $overlay->getHeader('Plural-Forms'));
        $item = $overlay->find(null, 'item');
        $this->assertNotNull($item);
        $this->assertCount(3, $item->getPluralTranslations());
        $this->assertContains('LOKAL-VIELE', $item->getPluralTranslations(), 'the local value must not be lost');
        $this->assertSame('LOKAL-PLAIN', $overlay->find(null, 'plain')?->getTranslation());
        $served = (string) file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase()));
        $this->assertSame(self::THREE_FORMS, MigratedPoFixture::moPluralFormsHeader($served));
        $this->assertCount(3, MigratedPoFixture::readMoPluralForms($served)['item']);

        $data = $this->lngData();
        $this->assertSame('LOKAL-PLAIN', $data['pilot|plain']['value']);
        $this->assertNotNull($data['pilot|plain']['local_change']);
        $this->assertSame($data['pilot|item']['value'], $this->lngModules()[self::MODULE]['item']);
        $this->assertSame('LOKAL-PLAIN', $this->lngModules()[self::MODULE]['plain']);

        $forms = $item->getPluralTranslations();
        $local_change = LocalChangeComments::getLocalChange($item);
        $this->manager()->insertLanguageForInstallation('de');
        $again = $this->overlayPo()->find(null, 'item');
        $this->assertSame($forms, $again?->getPluralTranslations(), 'the second run changes no form');
        $this->assertSame($local_change, LocalChangeComments::getLocalChange($again), 'nor the local change');
    }

    /**
     * 1 -> 2 forms (CLDR 49 change of "vi"): both new forms carry the local value.
     */
    public function testInstallAfterASingleFormLanguageGotASecondFormGivesTheLocalValueToBothForms(): void
    {
        $this->shipPluralPo(['item' => ['Eintrag']], 'nplurals=1; plural=0;');
        $this->writeLocalOverlay(['item [0]' => 'LOKAL']);
        $this->shipPluralPo(['item' => ['Eintrag', 'Einträge']], self::TWO_FORMS);

        $this->assertSame([], $this->manager()->insertLanguageForInstallation('de'));

        $overlay = $this->overlayPo();
        $this->assertSame(self::TWO_FORMS, $overlay->getHeader('Plural-Forms'));
        $this->assertSame(['LOKAL', 'LOKAL'], $overlay->find(null, 'item')?->getPluralTranslations());
    }

    /**
     * 3 -> 2 forms: the local default form survives, the middle form's local value has no equivalent form and is
     * replaced by the shipped value, with a log entry; a second run neither changes it nor warns again.
     */
    public function testInstallAfterTheNumberOfPluralFormsShrankKeepsTheLocalDefaultFormAndLogsTheLostValue(): void
    {
        $this->expectErrorLog();
        $this->shipPluralPo(['item' => ['Eintrag', 'Einträge-few', 'Einträge-viele'], 'plain' => 'X'], self::THREE_FORMS);
        $this->writeLocalOverlay([
            'item [0]' => 'Eintrag',
            'item [1]' => 'LOKAL-FEW',
            'item [2]' => 'LOKAL-VIELE',
            'plain' => 'LOKAL-PLAIN',
        ]);
        $this->shipPluralPo(['item' => ['Eintrag', 'Einträge'], 'plain' => 'X'], self::TWO_FORMS);

        $this->assertSame([], $this->manager()->insertLanguageForInstallation('de'));

        $overlay = $this->overlayPo();
        $this->assertSame(self::TWO_FORMS, $overlay->getHeader('Plural-Forms'));
        $item = $overlay->find(null, 'item');
        $this->assertSame(['Eintrag', 'LOKAL-VIELE'], $item?->getPluralTranslations(), 'the local default form is not lost');
        $this->assertSame('LOKAL-PLAIN', $overlay->find(null, 'plain')?->getTranslation());
        $this->assertSame('Einträge', LocalChangeComments::getOriginal($item, 1), 'the original is the shipped value of the new rule');

        $served = (string) file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase()));
        $this->assertSame(self::TWO_FORMS, MigratedPoFixture::moPluralFormsHeader($served));
        $this->assertSame(['item' => ['Eintrag', 'LOKAL-VIELE']], MigratedPoFixture::readMoPluralForms($served));

        $data = $this->lngData();
        $this->assertSame('LOKAL-VIELE', $data['pilot|item']['value']);
        $this->assertNotNull($data['pilot|item']['local_change']);
        $this->assertSame('LOKAL-VIELE', $this->lngModules()[self::MODULE]['item']);
        $this->assertSame('LOKAL-PLAIN', $this->lngModules()[self::MODULE]['plain']);

        $log = $this->errorLog();
        $this->assertStringContainsString('item [1]', $log, 'the value that found no form is logged');
        $this->assertStringNotContainsString('item [2]', $log);
        $this->assertStringNotContainsString('item [0]', $log);

        $po_before = (string) file_get_contents($this->overlayBase() . '.po');
        $this->manager()->insertLanguageForInstallation('de');
        $this->assertSame($po_before, (string) file_get_contents($this->overlayBase() . '.po'), 'the second run changes nothing');
        $this->assertSame($log, $this->errorLog(), 'and does not warn again');
    }
}
