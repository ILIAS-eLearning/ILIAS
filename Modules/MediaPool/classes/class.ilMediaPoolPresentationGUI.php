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
 * Top level GUI class for media pools.
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilMediaPoolPresentationGUI: ilObjMediaPoolGUI
 */
class ilMediaPoolPresentationGUI implements ilCtrlBaseClassInterface
{
    protected \ILIAS\MediaPool\StandardGUIRequest $request;
    protected ilCtrl $ctrl;
    protected ilAccessHandler $access;
    protected ilNavigationHistory $nav_history;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilObjectDefinition $objDefinition;

    public function __construct()
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $objDefinition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();

        $lng->loadLanguageModule("content");

        $this->ctrl = $ilCtrl;
        // initiate variables
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->objDefinition = $objDefinition;
        $DIC->globalScreen()->tool()->context()->claim()->repository();
        $DIC->globalScreen()->tool()->context()->current()->addAdditionalData(ilMediaPoolGSToolProvider::SHOW_FOLDERS_TOOL, true);

        $this->request = $DIC->mediaPool()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $ilNavigationHistory = $this->nav_history;

        $next_class = $this->ctrl->getNextClass($this);

        // add entry to navigation history
        if ($ilAccess->checkAccess("read", "", $this->request->getRefId())) {
            $ilNavigationHistory->addItem(
                $this->request->getRefId(),
                "ilias.php?baseClass=ilMediaPoolPresentationGUI&ref_id=" . $this->request->getRefId(),
                "mep"
            );
        }

        switch ($next_class) {
            case "ilobjmediapoolgui":
                $mep_gui = new ilObjMediaPoolGUI($this->request->getRefId());
                $ilCtrl->forwardCommand($mep_gui);
                break;

            default:
                $this->ctrl->setCmdClass("ilobjmediapoolgui");
                $this->executeCommand();
        }
    }
}
