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

declare(strict_types=1);

namespace ILIAS\Category\Permission;

use ILIAS\Repository\Permission\CmdPermission;
use ILIAS\Category\StandardGUIRequest;
use ILIAS\Repository\Permission\CmdEntity;
use ilObjUserGUI;
use ilObjectMetaDataGUI;

class CategoryCmdPermission extends CmdPermission
{
    protected const CAT = "cat";

    public function __construct(
        protected \ilLanguage $lng,
        protected \ilAccessHandler $access,
        ?\ilGlobalTemplateInterface $tpl = null,
        protected ?\ilCtrlInterface $ctrl = null,
        protected ?StandardGUIRequest $request = null
    ) {
        parent::__construct($lng, $tpl, $ctrl);
    }

    public function isCommandPermitted(
        string $cmd,                // derived or from table
        int $node_id,
        string $entity,
        string $entity_id = ""
    ): bool {
        switch ($entity) {
            case self::CAT:
                return $this->hasCatCmdPerm($cmd, $node_id);
        }
        return false;
    }

    protected function hasCatCmdPerm(string $cmd, int $node_id): bool
    {
        // administrate users
        if (in_array($cmd, [
            "resetFilter", "applyFilter", "listUsers", "addUserAutoComplete", "performDeleteUsers",
            "deleteUsers", "assignRoles", "assignSave"
        ])) {
            return $this->access->checkAccess("cat_administrate_users", "", $node_id);
        }

        // write permission
        if (in_array($cmd, [
            "editInfo", "updateInfo", "update"
        ])) {
            return $this->access->checkAccess("write", "", $node_id);
        }

        // read permission
        if (in_array($cmd, [
            "render", "view", "showTaxAsSideBlock", "hideTaxAsSideBlock"
        ])) {
            return $this->access->checkAccess("read", "", $node_id);
        }

        // visible permission
        if (in_array($cmd, [
            "infoScreen"
        ])) {
            return $this->access->checkAccess("visible", "", $node_id);
        }


        return false;
    }

    public function getDefaultCommand(): string
    {
        if ($this->isClass(\ilObjCategoryGUI::class)) {
            return "render";
        }
        return "";
    }

    public function getRequestEntity(): ?CmdEntity
    {
        if ($this->isClass(\ilObjCategoryGUI::class)) {
            return $this->cmdEntity(
                self::CAT
            );
        }
        return null;
    }

    public function getRequestNodeId(): int
    {
        return $this->request->getRefId();
    }

    public function isForwardPermitted(
        string $from_class,
        string $to_class
    ): bool {
        $node_id = $this->getRequestNodeId();
        if ($from_class === \ilObjCategoryGUI::class) {

            // write permission
            if (in_array($to_class, [
                \ilRepositoryTrashGUI::class,
                \ilContainerFilterAdminGUI::class,
                \ilObjectContentStyleSettingsGUI::class,
                \ilDidacticTemplateGUI::class,
                \ilExportGUI::class,
                \ilObjectTranslationGUI::class,
                \ilTaxonomySettingsGUI::class,
                \ilObjectMetaDataGUI::class,
                \ilContainerNewsSettingsGUI::class,
            ])) {
                return $this->access->checkAccess("write", "", $node_id);
            }

            // administration users
            if (in_array($to_class, [
                \ilObjUserGUI::class,
                \ilObjUserFolderGUI::class,
                \ilUserTableGUI::class,
            ])) {
                return $this->access->checkAccess("cat_administrate_users", "", $node_id);
            }

            // edit permission
            if ($to_class === \ilPermissionGUI::class) {
                return $this->access->checkAccess("edit_permission", "", $node_id);
            }

            // read permission
            // note on ilObjectCopyGUI: This class performs the copy permission checks and
            // can act on multiple source ids
            if ($to_class === \ilObjectCopyGUI::class) {
                return $this->access->checkAccess("read", "", $node_id);
            }

            // visible or read
            if ($to_class === \ilCommonActionDispatcherGUI::class) {
                return $this->access->checkAccess("visible", "", $node_id) ||
                    $this->access->checkAccess("read", "", $node_id);
            }

            // visible
            if ($to_class === \ilInfoScreenGUI::class) {
                return $this->access->checkAccess("visible", "", $node_id);
            }
        }
        return false;
    }

}
