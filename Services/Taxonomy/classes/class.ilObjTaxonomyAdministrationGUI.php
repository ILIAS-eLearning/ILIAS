<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
 * Taxonomy Administration Settings
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ilCtrl_Calls ilObjTaxonomyAdministrationGUI: ilPermissionGUI
 *
 * @ingroup ServicesTaxonomy
 */
class ilObjTaxonomyAdministrationGUI extends ilObjectGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * Contructor
     *
     * @return self
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->type = "taxs";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("tax");
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "listRepository";
                }

                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Get tabs
     */
    public function getAdminTabs()
    {
        $rbacsystem = $this->rbacsystem;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("tax_admin_settings_repository"),
                $this->ctrl->getLinkTarget($this, "listRepository")
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }
    
    /**
     * List taxonomies of repository objects
     */
    public function listRepository()
    {
        $this->tabs_gui->activateTab('settings');
        
        require_once "Services/Taxonomy/classes/class.ilTaxonomyAdministrationRepositoryTableGUI.php";
        $tbl = new ilTaxonomyAdministrationRepositoryTableGUI($this, "listRepository", $this->object);
        $this->tpl->setContent($tbl->getHTML());
    }
}
