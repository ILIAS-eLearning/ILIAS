<?php declare(strict_types=1);

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
 ********************************************************************
 */

/**
 * Class ilSetupLanguageTest
 * @author  Sílvia Mariné <silvia.marine@kroepelin-projekte.de>
 */
//is this necessary?
include_once "Services/Language/classes/Setup/class.ilSetupLanguage.php";

class ilSetupLanguageTest extends ilLanguageBaseTest
{
    private ilSetupLanguage $newLangSetupDe;
    private ilSetupLanguage $newLangSetupEs;

    /**
     * @var ilSetupLanguage[]
     */
    private array $langInstalled;

    protected function setUp() : void
    {
        $this->newLangSetupDe = new ilSetupLanguage('de');
        $this->newLangSetupEs = new ilSetupLanguage('es');

        $this->langInstalled[] = $this->newLangSetupDe;
        $this->langInstalled[] = $this->newLangSetupEs;
    }

    public function testRetrieveLanguageKey() : void
    {
        $this->assertEquals('de', $this->newLangSetupDe->getLangKey());
    }

    public function testRetrieveInstalledLanguage() : void
    {
        $languagesAsKeys = [];
        foreach ($this->langInstalled as $languageAsKey) {
            $languagesAsKeys[] = $languageAsKey->getLangKey();
        }

        $this->assertContains('es', $languagesAsKeys);
    }
}
