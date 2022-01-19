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

/**
 * User action administration GUI class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserActionAdminGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilUserActionContext $action_context;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->ref_id = (int) $_GET["ref_id"];
        $this->rbabsystem = $DIC->rbac()->system();

        $this->lng->loadLanguageModule("usr");
    }
    
    public function setActionContext(ilUserActionContext $a_val = null) : void
    {
        $this->action_context = $a_val;
    }
    
    public function getActionContext() : ilUserActionContext
    {
        return $this->action_context;
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("show");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("show", "save"))) {
                    $this->$cmd();
                }
        }
    }

    public function show() : void
    {
        ilUtil::sendInfo($this->lng->txt("user_actions_activation_info"));

        include_once("./Services/User/Actions/classes/class.ilUserActionAdminTableGUI.php");
        $tab = new ilUserActionAdminTableGUI(
            $this,
            "show",
            $this->getActions(),
            $this->rbabsystem->checkAccess("write", $this->ref_id)
        );
        $this->tpl->setContent($tab->getHTML());
    }

    /**
     * Save !!!! note in the future this must depend on the context, currently we only have one
     */
    public function save() : void
    {
        if (!$this->rbabsystem->checkAccess("write", $this->ref_id)) {
            $this->ctrl->redirect($this, "show");
        }

        //var_dump($_POST); exit;
        include_once("./Services/User/Actions/classes/class.ilUserActionAdmin.php");
        foreach ($this->getActions() as $a) {
            ilUserActionAdmin::activateAction(
                $this->action_context->getComponentId(),
                $this->action_context->getContextId(),
                $a["action_comp_id"],
                $a["action_type_id"],
                (int) $_POST["active"][$a["action_comp_id"] . ":" . $a["action_type_id"]]
            );
        }
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "show");
    }

    /**
     * Get actions, !!!! note in the future this must depend on the context, currently we only have one
     */
    public function getActions() : array
    {
        include_once("./Services/User/Actions/classes/class.ilUserActionProviderFactory.php");
        include_once("./Services/User/Actions/classes/class.ilUserActionAdmin.php");
        $data = array();
        foreach (ilUserActionProviderFactory::getAllProviders() as $p) {
            foreach ($p->getActionTypes() as $id => $name) {
                $data[] = array(
                    "action_comp_id" => $p->getComponentId(),
                    "action_type_id" => $id,
                    "action_type_name" => $name,
                    "active" => ilUserActionAdmin::lookupActive(
                        $this->action_context->getComponentId(),
                        $this->action_context->getContextId(),
                        $p->getComponentId(),
                        $id
                    )
                );
            }
        }
        return $data;
    }
}
