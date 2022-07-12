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

/**
 * Handles user interface for wikis
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilWikiHandlerGUI: ilObjWikiGUI
 */
class ilWikiHandlerGUI implements ilCtrlBaseClassInterface
{
    protected string $requested_page;
    protected int $requested_ref_id;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ilGlobalTemplateInterface $tpl;
    protected ilNavigationHistory $nav_history;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tpl = $DIC["tpl"];
        $this->nav_history = $DIC["ilNavigationHistory"];
        $ilCtrl = $DIC->ctrl();


        // initialisation stuff
        $this->ctrl = $ilCtrl;

        $request = $DIC
            ->wiki()
            ->internal()
            ->gui()
            ->editing()
            ->request();
        $this->requested_ref_id = $request->getRefId();
        $this->requested_page = $request->getPage();

        $DIC->globalScreen()->tool()->context()->claim()->repository();
    }
    
    public function executeCommand() : void
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilNavigationHistory = $this->nav_history;
        
        $next_class = $this->ctrl->getNextClass($this);
        if ($next_class == "") {
            $this->ctrl->setCmdClass("ilobjwikigui");
            $next_class = $this->ctrl->getNextClass($this);
        }

        // add entry to navigation history
        if ($ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            $obj_id = ilObject::_lookupObjId($this->requested_ref_id);
            $title = ilObject::_lookupTitle($obj_id);

            if ($this->requested_page !== "") {
                $page = $this->requested_page;
            } else {
                $page = ilObjWiki::_lookupStartPage($obj_id);
            }

            if (ilWikiPage::exists($obj_id, $page)) {
                $add = "_" . rawurlencode($page);

                $page_id = ilWikiPage::getPageIdForTitle($obj_id, $page);
                $ptitle = ilWikiPage::lookupTitle($page_id);
                
                $title .= ": " . $ptitle;
                
                $append = ($this->requested_page !== "")
                    ? "_" . ilWikiUtil::makeUrlTitle($page)
                    : "";
                $goto = ilLink::_getStaticLink(
                    $this->requested_ref_id,
                    "wiki",
                    true,
                    $append
                );
                //var_dump($goto);
                $ilNavigationHistory->addItem(
                    $this->requested_ref_id,
                    "./goto.php?target=wiki_" . $this->requested_ref_id . $add,
                    "wiki",
                    $title,
                    $page_id,
                    $goto
                );
            }
        }

        switch ($next_class) {
            case 'ilobjwikigui':
                $mc_gui = new ilObjWikiGUI(
                    "",
                    $this->requested_ref_id,
                    true,
                    false
                );
                $this->ctrl->forwardCommand($mc_gui);
                break;
        }

        $tpl->printToStdout();
    }
}
