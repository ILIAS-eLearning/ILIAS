<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\Imprint\StandardGUIRequest;

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
    protected StandardGUIRequest $imprint_request;
    protected ilLocatorGUI $locator;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->locator = $DIC["ilLocator"];
        $this->lng = $DIC->language();

        $this->imprint_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
        
        if (!ilImprint::_exists("impr", 1)) {
            $page = new ilImprint("impr");
            $page->setId(1);
            $page->create(false);
        }

        // there is only 1 imprint page
        parent::__construct("impr", 1);
        
        // content style (using system defaults)
        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $this->tpl->parseCurrentBlock();
    
        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(0)
        );
        $this->tpl->parseCurrentBlock();
    }
    
    public function executeCommand() : string
    {
        if (strtolower($this->imprint_request->getBaseClass()) ===
            strtolower(ilImprintGUI::class)) {
            $this->renderFullscreen();
        }
        
        $next_class = $this->ctrl->getNextClass($this);
        
        $title = $this->lng->txt("adm_imprint");
        
        switch ($next_class) {
            default:
                $this->setPresentationTitle($title);
    
                $this->locator->addItem(
                    $title,
                    $this->ctrl->getLinkTarget($this, "preview")
                );
                return parent::executeCommand();
        }
    }
    
    public function postOutputProcessing(string $a_output) : string
    {
        $lng = $this->lng;
        
        if ($this->getOutputMode() == ilPageObjectGUI::PREVIEW) {
            if (!$this->getPageObject()->getActive()) {
                $this->tpl->setOnScreenMessage('info', $lng->txt("adm_imprint_inactive"));
            }
        }
        
        return $a_output;
    }

    protected function renderFullscreen() : void
    {
        if (!ilImprint::isActive()) {
            $this->ctrl->redirectToURL("ilias.php?baseClass=ilDashboardGUI");
        }
        $this->tpl->setTitle($this->lng->txt("imprint"));
        $this->tpl->loadStandardTemplate();

        $this->setRawPageContent(true);
        $this->tpl->setContent($this->showPage());
    
        $this->tpl->printToStdout("DEFAULT", true, false);
        exit();
    }
}
