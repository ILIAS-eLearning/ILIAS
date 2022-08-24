<?php

declare(strict_types=1);

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

class ilObjStudyProgrammeReferenceListGUI extends ilObjStudyProgrammeListGUI
{
    protected bool $conditions_ok;
    protected ?int $ref_obj_id = null;
    protected ?int $ref_ref_id = null;
    protected bool $deleted = false;
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    public function __construct()
    {
        global $DIC;
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();

        parent::__construct();
    }

    public function getIconImageType(): string
    {
        return 'prgr';
    }

    /**
     * @inheritdoc
     */
    public function getTypeIcon(): string
    {
        $ref_obj_id = ilObject::_lookupObjId($this->getCommandId());
        return ilObject::_getIcon(
            $ref_obj_id,
            'small'
        );
    }

    public function getCommandId(): int
    {
        return $this->ref_ref_id;
    }

    /**
     * no activation for links
     */
    public function insertTimingsCommand(): void
    {
    }

    public function init(): void
    {
        $this->static_link_enabled = false;
        $this->delete_enabled = true;
        $this->cut_enabled = false;
        $this->info_screen_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;

        $this->type = "prgr";
        $this->gui_class_name = "ilobjstudyprogrammegui";

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }
    }

    /**
    * initialize new item
    * Group reference inits the group item
    */
    public function initItem(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title = "",
        string $description = ""
    ): void {
        $this->ref_ref_id = $ref_id;
        $this->ref_obj_id = $obj_id;

        $target_obj_id = ilContainerReference::_lookupTargetId($obj_id);

        $target_ref_ids = ilObject::_getAllReferences($target_obj_id);
        $target_ref_id = current($target_ref_ids);
        $target_title = ilContainerReference::_lookupTitle($obj_id);
        $target_description = ilObject::_lookupDescription($target_obj_id);

        $this->deleted = $this->tree->isDeleted($target_ref_id);

        $this->conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($target_ref_id, $target_obj_id);

        parent::initItem($target_ref_id, $target_obj_id, 'prg', $target_title, $target_description);
        $this->setTitle($target_title);
        $this->commands = ilObjStudyProgrammeReferenceAccess::_getCommands($this->ref_ref_id);

        if ($this->access->checkAccess('write', '', $this->ref_ref_id) || $this->deleted) {
            $this->info_screen_enabled = false;
        } else {
            $this->info_screen_enabled = true;
        }
    }

    public function getProperties(): array
    {
        $props = parent::getProperties();

        // offline
        if ($this->deleted) {
            $props[] = array("alert" => true, "property" => $this->lng->txt("status"),
                "value" => $this->lng->txt("reference_deleted"));
        }

        return $props ?: array();
    }

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

        return parent::checkCommandAccess($permission, $cmd, $this->getCommandId(), 'prgr', null);
    }

    /**
     * get command link
     *
     * @access public
     * @param string $a_cmd
     * @return string
     */
    public function getCommandLink($a_cmd): string
    {
        $ref_id = $this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int());
        switch ($a_cmd) {
            case 'editReference':
                $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->getCommandId());
                $cmd_link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
                return $cmd_link;

            default:
                $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
                return $cmd_link;
        }
    }

    public function getListItemHTML(
        int $a_ref_id,
        int $a_obj_id,
        string $a_title,
        string $a_description,
        bool $a_use_asynch = false,
        bool $a_get_asynch_commands = false,
        string $a_asynch_url = "",
        int $a_context = self::CONTEXT_REPOSITORY
    ): string {
        $target_obj_id = ilContainerReference::_lookupTargetId($a_obj_id);
        $target_ref_id = current(ilObject::_getAllReferences($target_obj_id));
        $prg = new ilObjStudyProgramme($target_ref_id);
        $assignments = $prg->getAssignments();
        if ($this->getCheckboxStatus() && count($assignments) > 0) {
            $this->setAdditionalInformation($this->lng->txt("prg_can_not_manage_in_repo"));
            $this->enableCheckbox(false);
        } else {
            $this->setAdditionalInformation(null);
        }
        return ilObjectListGUI::getListItemHTML(
            $a_ref_id,
            $a_obj_id,
            $a_title,
            $a_description,
            $a_use_asynch,
            $a_get_asynch_commands,
            $a_asynch_url
        );
    }
}
