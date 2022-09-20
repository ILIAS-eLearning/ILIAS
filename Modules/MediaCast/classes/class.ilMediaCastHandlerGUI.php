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

use ILIAS\MediaCast\StandardGUIRequest;

/**
 * Handles user interface for media casts
 * auth
 * @ilCtrl_Calls ilMediaCastHandlerGUI: ilObjMediaCastGUI
 */
class ilMediaCastHandlerGUI implements ilCtrlBaseClassInterface
{
    protected StandardGUIRequest $request;
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
        $this->request = $DIC->mediaCast()
            ->internal()
            ->gui()
            ->standardRequest();

        // initialisation stuff
        $this->ctrl = $ilCtrl;
    }

    public function executeCommand(): void
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilNavigationHistory = $this->nav_history;

        $next_class = $this->ctrl->getNextClass($this);
        if ($next_class == "") {
            $this->ctrl->setCmdClass("ilobjmediacastgui");
            $next_class = $this->ctrl->getNextClass($this);
        }

        // add entry to navigation history
        if ($ilAccess->checkAccess("read", "", $this->request->getRefId())) {
            $ilNavigationHistory->addItem(
                $this->request->getRefId(),
                "ilias.php?baseClass=ilMediaCastHandlerGUI&cmd=showContent&ref_id=" .
                    $this->request->getRefId(),
                "mcst"
            );
        }

        switch ($next_class) {
            case 'ilobjmediacastgui':
                $mc_gui = new ilObjMediaCastGUI(
                    "",
                    $this->request->getRefId(),
                    true,
                    false
                );
                $this->ctrl->forwardCommand($mc_gui);
                break;
        }

        $tpl->printToStdout();
    }
}
