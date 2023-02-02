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
 ********************************************************************
 */

namespace ILIAS\Skill\Tree;

use ILIAS\Skill\Access\SkillTreeAccess;
use ILIAS\Skill\Access\SkillManagementAccess;
use ILIAS\Skill\Service\SkillAdminGUIRequest;

/**
 * Skill tree manager
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillTreeManager
{
    protected \ilCtrl $ctrl;
    protected \ilErrorHandling $error;
    protected \ilLanguage $lng;
    protected int $skmg_ref_id = 0;
    protected \ilTree $repository_tree;
    protected SkillTreeFactory $tree_factory;
    protected SkillTreeAccess $tree_access_manager;
    protected SkillManagementAccess $management_access_manager;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected int $requested_ref_id = 0;

    public function __construct(
        int $skmg_ref_id,
        \ilTree $repository_tree,
        SkillTreeFactory $tree_factory
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->error = $DIC["ilErr"];
        $this->lng = $DIC->language();
        $this->skmg_ref_id = $skmg_ref_id;
        $this->repository_tree = $repository_tree;
        $this->tree_factory = $tree_factory;
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();

        // TODO: Find a different way for the ref_id, because this is no GUI class
        $this->requested_ref_id = $this->admin_gui_request->getRefId();

        $this->tree_access_manager = $DIC->skills()->internal()->manager()->getTreeAccessManager($this->requested_ref_id);
        $this->management_access_manager = $DIC->skills()->internal()->manager()->getManagementAccessManager($this->requested_ref_id);
    }

    public function createTree(string $title, string $description): void
    {
        if (!$this->management_access_manager->hasCreateTreePermission()) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }
        $tree_obj = new \ilObjSkillTree();
        $tree_obj->setTitle($title);
        $tree_obj->setDescription($description);
        $tree_obj->create();
        $tree_obj->createReference();
        $tree_obj->putInTree($this->skmg_ref_id);
        $tree_obj->setPermissions($this->skmg_ref_id);

        $tree = $this->tree_factory->getTreeById($tree_obj->getId());
        $root_node = new \ilSkillRoot();
        $root_node->setTitle("Skill Tree Root Node");
        $root_node->create();
        $tree->addTree($tree_obj->getId(), $root_node->getId());
        $this->ctrl->setParameterByClass("ilobjskilltreegui", "ref_id", $tree_obj->getRefId());
        $this->ctrl->setParameterByClass("ilobjskilltreegui", "obj_id", $tree->readRootId());
    }

    public function updateTree(\ilObjSkillTree $tree_obj, string $title, string $description): void
    {
        if (!$this->tree_access_manager->hasEditTreeSettingsPermission()) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }
        $tree_obj->setTitle($title);
        $tree_obj->setDescription($description);
        $tree_obj->update();
    }

    public function deleteTree(\ilObjSkillTree $tree_obj): void
    {
        if (!$this->management_access_manager->hasCreateTreePermission()) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }
        $tree_obj->delete();
    }

    public function getTrees(): \Generator
    {
        foreach ($this->repository_tree->getChilds($this->skmg_ref_id) as $c) {
            if ($c["type"] == "skee") {
                yield new \ilObjSkillTree((int) $c["child"]);
            }
        }
    }

    public function getTree(int $tree_id): \ilObjSkillTree
    {
        $ref_id = (int) current(\ilObject::_getAllReferences($tree_id));
        return new \ilObjSkillTree($ref_id);
    }

    /**
     * Get ref id of skill management administration node
     */
    public function getSkillManagementRefId(): int
    {
        return $this->skmg_ref_id;
    }
}
