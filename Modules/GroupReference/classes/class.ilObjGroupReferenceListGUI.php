<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Group/classes/class.ilObjGroupListGUI.php";

/**
 * @author Fabian Wolf <wolf@leifos.com>
 * @extends ilObjGroupListGUI
 *
 * @ingroup ModulesGroupReference
*/
class ilObjGroupReferenceListGUI extends ilObjGroupListGUI
{
    protected ?int $reference_obj_id = null;
    protected int $reference_ref_id;
    protected bool $deleted = false;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getIconImageType(): string
    {
        return 'grpr';
    }

    /**
     * @inheritdoc
     */
    public function getTypeIcon(): string
    {
        $reference_obj_id = ilObject::_lookupObjId($this->getCommandId());
        return ilObject::_getIcon(
            $reference_obj_id,
            'small'
        );
    }


    /**
     * get command id
     *
     * @access public
     */
    public function getCommandId(): int
    {
        return $this->reference_ref_id;
    }

    /**
     * no activation for links
     */
    public function insertTimingsCommand(): void
    {
        return;
    }



    /**
    * initialisation
    */
    public function init(): void
    {
        $this->copy_enabled = true;
        $this->static_link_enabled = false;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->type = "grp";
        $this->gui_class_name = "ilobjgroupgui";

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }
    }



    /**
     * @inheritdoc
     */
    public function initItem(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title = "",
        string $description = ""
    ): void {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $tree = $DIC['tree'];

        $this->reference_ref_id = $ref_id;
        $this->reference_obj_id = $obj_id;


        include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
        $target_obj_id = ilContainerReference::_lookupTargetId($obj_id);

        $target_ref_ids = ilObject::_getAllReferences($target_obj_id);
        $target_ref_id = current($target_ref_ids);
        $target_title = ilContainerReference::_lookupTitle($obj_id);
        $target_description = ilObject::_lookupDescription($target_obj_id);

        $this->deleted = $tree->isDeleted($target_ref_id);

        $this->conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($target_ref_id, $target_obj_id);


        parent::initItem($target_ref_id, $target_obj_id, $type, $target_title, $target_description);

        // general commands array
        include_once('./Modules/GroupReference/classes/class.ilObjGroupReferenceAccess.php');
        $this->commands = ilObjGroupReferenceAccess::_getCommands($this->reference_ref_id);

        if ($ilAccess->checkAccess('write', '', $this->reference_ref_id) or $this->deleted) {
            $this->info_screen_enabled = false;
        } else {
            $this->info_screen_enabled = true;
        }
    }

    public function getProperties(): array
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $tree = $DIC['tree'];

        $props = parent::getProperties();

        // offline
        if ($this->deleted) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("reference_deleted"));
        }

        return $props ? $props : array();
    }

    /**
     *
     * @param
     * @return
     */
    public function checkCommandAccess(
        string $permission,
        string $cmd,
        int $ref_id,
        string $type,
        ?int $obj_id = null
    ): bool {
        // Check edit reference against reference edit permission
        switch ($cmd) {
            case 'editReference':
                return parent::checkCommandAccess($permission, $cmd, $this->getCommandId(), $type, $obj_id);
        }

        switch ($permission) {
            case 'copy':
            case 'delete':
                // check against target ref_id
                return parent::checkCommandAccess($permission, $cmd, $this->getCommandId(), $type, $obj_id);

            default:
                // check against reference
                return parent::checkCommandAccess($permission, $cmd, $ref_id, $type, $obj_id);
        }
    }

    /**
     * get command link
     *
     * @access public
     */
    public function getCommandLink(string $cmd): string
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        switch ($cmd) {
            case 'editReference':
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->getCommandId());
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $cmd);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                return $cmd_link;

            default:
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $cmd);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                return $cmd_link;
        }
    }
}
