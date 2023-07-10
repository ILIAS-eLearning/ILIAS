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
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilHTLMPresentationGUI: ilObjFileBasedLMGUI
 */
class ilHTLMPresentationGUI implements ilCtrlBaseClassInterface
{
    protected \ILIAS\HTMLLearningModule\StandardGUIRequest $request;
    protected ilCtrl $ctrl;
    protected ilAccessHandler $access;
    protected ilNavigationHistory $nav_history;
    public ilGlobalTemplateInterface $tpl;
    public ilLanguage $lng;
    public ilObjectDefinition $objDefinition;
    public int $ref_id;

    public function __construct()
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $objDefinition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();
        $ilAccess = $DIC->access();
        $this->request = $DIC->htmlLearningModule()
            ->internal()
            ->gui()
            ->standardRequest();
        $this->ref_id = $this->request->getRefId();

        $lng->loadLanguageModule("content");

        // check write permission
        if (!$ilAccess->checkAccess("read", "", $this->ref_id)) {
            throw new ilPermissionException($lng->txt("permission_denied"));
        }


        $this->ctrl = $ilCtrl;

        //$this->ctrl->saveParameter($this, array("ref_id", "obj_id"));
        $this->ctrl->saveParameter($this, array("ref_id"));

        // initiate variables
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->objDefinition = $objDefinition;
    }

    public function executeCommand(): void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $ilNavigationHistory = $this->nav_history;

        // add entry to navigation history
        if ($ilAccess->checkAccess("read", "", $this->ref_id)) {
            $ilCtrl->setParameterByClass("ilobjfilebasedlmgui", "ref_id", $this->ref_id);
            $ilNavigationHistory->addItem(
                $this->ref_id,
                $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjfilebasedlmgui"), "infoScreen"),
                "htlm"
            );
        }

        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case "ilobjfilebasedlmgui":
                $fblm_gui = new ilObjFileBasedLMGUI("", $this->ref_id, true, false);
                $ilCtrl->forwardCommand($fblm_gui);
                $tpl->printToStdout();
                break;

            default:
                $this->ctrl->setCmdClass("ilobjfilebasedlmgui");
                $this->ctrl->setCmd("showLearningModule");
                $this->executeCommand();
                break;
        }
    }
}
