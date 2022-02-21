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

    /**
     * @var
     */
    private $langSetupObj;

    /**
     * @var
     */
    private $newLangSetupDe;

    /**
     * @var
     */
    private $newLangSetupEs;

    /**
     * @var
     */
    private $langInstalled;

    public function setUp(): void
    {
        $this->langSetupObj = $this->getMockBuilder(ilSetupLanguage::class)->disableOriginalConstructor()->getMock();

        $this->newLangSetupDe = new $this->langSetupObj('de');
        $this->newLangSetupEs = new $this->langSetupObj('es');

        $this->langInstalled[] = $this->newLangSetupDe;
        $this->langInstalled[] = $this->newLangSetupEs;
    }

    /**
     *
     */
    public function testRetrieveLanguageKey(): void
    {
        $this->assertEquals('de', $this->newLangSetupDe->lang_key);
    }

    /**
     *
     */
    public function testRetrieveInstalledLanguage(): void
    {
        foreach($this->langInstalled as $languageAsKey) {
            $languagesAsKeys[] = $languageAsKey->lang_key;
        }
        $this->assertContains('es', $languagesAsKeys);
    }
}
