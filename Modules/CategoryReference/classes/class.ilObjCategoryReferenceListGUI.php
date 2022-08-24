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

use ILIAS\ContainerReference\StandardGUIRequest;

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjCategoryReferenceListGUI extends ilObjCategoryListGUI
{
    protected ?int $reference_obj_id = null;
    protected int $reference_ref_id;
    protected bool $deleted = false;
    protected StandardGUIRequest $cont_ref_request;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        parent::__construct();
        $this->cont_ref_request = $DIC
            ->containerReference()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    public function getIconImageType(): string
    {
        return 'catr';
    }

    public function getTypeIcon(): string
    {
        $reference_obj_id = ilObject::_lookupObjId($this->getCommandId());
        return ilObject::_getIcon(
            $reference_obj_id,
            'small'
        );
    }


    public function getCommandId(): int
    {
        return $this->reference_ref_id;
    }

    public function insertTimingsCommand(): void
    {
    }

    public function init(): void
    {
        $this->copy_enabled = true;
        $this->static_link_enabled = false;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->type = "cat";
        $this->gui_class_name = "ilobjcategorygui";

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }
    }

    public function initItem(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title = "",
        string $description = ""
    ): void {
        $ilAccess = $this->access;
        $tree = $this->tree;

        $this->reference_ref_id = $ref_id;
        $this->reference_obj_id = $obj_id;

        include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
        $target_obj_id = ilContainerReference::_lookupTargetId($obj_id);

        $target_ref_ids = ilObject::_getAllReferences($target_obj_id);
        $target_ref_id = current($target_ref_ids);
        $target_title = ilContainerReference::_lookupTitle($obj_id);
        $target_description = ilObject::_lookupDescription($target_obj_id);

        $this->deleted = $tree->isDeleted($target_ref_id);

        parent::initItem($target_ref_id, $target_obj_id, $type, $target_title, $target_description);

        // general commands array
        $this->commands = ilObjCategoryReferenceAccess::_getCommands($this->reference_ref_id);

        if ($this->deleted || $ilAccess->checkAccess('write', '', $this->reference_ref_id)) {
            $this->info_screen_enabled = false;
        } else {
            $this->info_screen_enabled = true;
        }
    }


    public function getProperties(): array
    {
        $lng = $this->lng;
        $tree = $this->tree;

        $props = parent::getProperties();

        // offline
        if ($tree->isDeleted($this->ref_id)) {
            $props[] = [
                "alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("reference_deleted")
            ];
        }

        return $props;
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

    public function getCommandLink(string $cmd): string
    {
        $ilCtrl = $this->ctrl;

        switch ($cmd) {
            case 'editReference':
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->getCommandId());
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $cmd);
                $ilCtrl->setParameterByClass(
                    "ilrepositorygui",
                    "ref_id",
                    $this->cont_ref_request->getRefId()
                );
                return $cmd_link;

            default:
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $cmd);
                $ilCtrl->setParameterByClass(
                    "ilrepositorygui",
                    "ref_id",
                    $this->cont_ref_request->getRefId()
                );
                return $cmd_link;
        }
    }
}
