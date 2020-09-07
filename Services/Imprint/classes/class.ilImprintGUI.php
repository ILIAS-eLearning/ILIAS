<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
require_once("./Services/Imprint/classes/class.ilImprint.php");

/**
* Class ilImprintGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @ilCtrl_Calls ilImprintGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
* @ilCtrl_Calls ilImprintGUI: ilPublicUserProfileGUI, ilPageObjectGUI
*
* @ingroup ModulesImprint
*/
class ilImprintGUI extends ilPageObjectGUI
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
            $page->create();
        }

        // there is only 1 imprint page
        parent::__construct("impr", 1);
        
        // content style (using system defaults)
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        
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
    public function executeCommand()
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
            case "ilpageobjectgui":
                die("Deprecated. ilImprintGUI gui forwarding to ilpageobject");
                return;
                
            default:
                $this->setPresentationTitle($title);

                $ilLocator->addItem(
                    $title,
                    $ilCtrl->getLinkTarget($this, "preview")
                );
            
                return parent::executeCommand();
        }
    }
    
    public function postOutputProcessing($a_output)
    {
        $lng = $this->lng;
        
        if ($this->getOutputMode() == IL_PAGE_PREVIEW) {
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
        $ilMainMenu = $this->main_menu;
        
        if (!ilImprint::isActive()) {
            ilUtil::redirect("ilias.php?baseClass=ilPersonalDesktopGUI");
        }
        
        $tpl->getStandardTemplate();
        
        $this->setRawPageContent(true);
        $html = $this->showPage();
        
        $itpl = new ilTemplate("tpl.imprint.html", true, true, "Services/Imprint");
        $itpl->setVariable("PAGE_TITLE", $lng->txt("imprint"));
        $itpl->setVariable("IMPRINT", $html);
        unset($html);
        
        $tpl->setContent($itpl->get());
        
        $ilMainMenu->showLogoOnly(true);
        
        echo $tpl->show("DEFAULT", true, false);
        exit();
    }
}
