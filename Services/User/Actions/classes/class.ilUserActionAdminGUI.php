<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User action administration GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserActionAdminGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilUserActionContext
     */
    protected $action_context;

    /**
     * Constructor
     *
     * @param
     * @return
     */
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
    
    /**
     * Set action context
     *
     * @param ilUserActionContext $a_val action context
     */
    public function setActionContext(ilUserActionContext $a_val = null)
    {
        $this->action_context = $a_val;
    }
    
    /**
     * Get action context
     *
     * @return ilUserActionContext action context
     */
    public function getActionContext()
    {
        return $this->action_context;
    }

    /**
     * Execute command
     */
    public function executeCommand()
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

    /**
     * Show
     *
     * @param
     * @return
     */
    public function show()
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
    public function save()
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
     *
     * @param
     * @return
     */
    public function getActions()
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
        //var_dump($data); exit;
        return $data;
    }
}
