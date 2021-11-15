<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilImprintGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilImprintGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilImprintGUI: ilPublicUserProfileGUI, ilPageObjectGUI
 */
class ilImprintGUI extends ilPageObjectGUI implements ilCtrlBaseClassInterface
{
    /**
     * @var ilLocatorGUI
     */
    protected $locator;

    /**
     * @var ilMainMenuGUI
     */
    protected $main_menu;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->locator = $DIC["ilLocator"];
        $this->lng = $DIC->language();
        $this->main_menu = $DIC["ilMainMenu"];
        $tpl = $DIC["tpl"];
        
        if (!ilImprint::_exists("impr", 1)) {
            $page = new ilImprint("impr");
            $page->setId(1);
            $page->create(false);
        }

        // there is only 1 imprint page
        parent::__construct("impr", 1);
        
        // content style (using system defaults)
        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $tpl->parseCurrentBlock();
        
        $tpl->setCurrentBlock("ContentStyle");
        $tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(0)
        );
        $tpl->parseCurrentBlock();
    }
    
    /**
    * execute command
    */
    public function executeCommand() : string
    {
        $ilCtrl = $this->ctrl;
        $ilLocator = $this->locator;
        $lng = $this->lng;
        
        if ($_REQUEST["baseClass"] == "ilImprintGUI") {
            $this->renderFullscreen();
        }
        
        $next_class = $ilCtrl->getNextClass($this);
            
        $title = $lng->txt("adm_imprint");
        
        switch ($next_class) {

            default:
                $this->setPresentationTitle($title);

                $ilLocator->addItem(
                    $title,
                    $ilCtrl->getLinkTarget($this, "preview")
                );
            
                return parent::executeCommand();
        }
    }
    
    public function postOutputProcessing(string $a_output) : string
    {
        $lng = $this->lng;
        
        if ($this->getOutputMode() == ilPageObjectGUI::PREVIEW) {
            if (!$this->getPageObject()->getActive()) {
                ilUtil::sendInfo($lng->txt("adm_imprint_inactive"));
            }
        }
        
        return $a_output;
    }

    protected function renderFullscreen()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        if (!ilImprint::isActive()) {
            ilUtil::redirect("ilias.php?baseClass=ilDashboardGUI");
        }
        $tpl->setTitle($lng->txt("imprint"));
        $tpl->loadStandardTemplate();

        $this->setRawPageContent(true);
        $html = $this->showPage();
        $tpl->setContent($html);

        $tpl->printToStdout("DEFAULT", true, false);
        exit();
    }
}
