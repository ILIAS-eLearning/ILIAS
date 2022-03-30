<?php declare(strict_types=1);

/* Copyright (c) 1998-2022 ILIAS open source, Extended GPL, see docs/LICENSE */

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
