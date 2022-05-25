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
 ********************************************************************
 */

namespace ILIAS\Skill\Tree;

use ILIAS\DI\UIServices;
use ILIAS\Skill\Access\SkillManagementAccess;
use ILIAS\Skill\Service\SkillInternalManagerService;
use ILIAS\Skill\Service\SkillAdminGUIRequest;

/**
 * Skill tree objects table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillTreeTableGUI extends \ilTable2GUI
{
    protected SkillInternalManagerService $internal_manager;
    protected SkillTreeManager $tree_manager;
    protected SkillManagementAccess $management_access_manager;
    protected SkillTreeFactory $tree_factory;
    protected UIServices $ui;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected int $requested_ref_id = 0;

    public function __construct(object $a_parent_obj, string $a_parent_cmd, SkillInternalManagerService $manager)
    {
        global $DIC;

        $this->id = "";
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();

        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        $this->requested_ref_id = $this->admin_gui_request->getRefId();

        $this->internal_manager = $manager;
        $this->tree_manager = $this->internal_manager->getTreeManager();
        $this->management_access_manager = $this->internal_manager->getManagementAccessManager($this->requested_ref_id);
        $this->tree_factory = $DIC->skills()->internal()->factory()->tree();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData($this->getItems());
        $this->setTitle($this->lng->txt(""));

        if ($this->management_access_manager->hasCreateTreePermission()) {
            $this->addColumn("", "", "", true);
        }
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($this->ctrl->getFormActionByClass("ilobjskilltreegui"));
        $this->setRowTemplate("tpl.skill_tree_row.html", "Services/Skill/Tree");

        if ($this->management_access_manager->hasCreateTreePermission()) {
            $this->addMultiCommand("delete", $this->lng->txt("delete"));
        }
    }

    /**
     * @return array({title: string, tree: \ilObjSkillTree}|array{})[]
     */
    protected function getItems() : array
    {
        return array_filter(array_map(
            function (\ilObjSkillTree $skillTree) : array {
                $tree_access_manager = $this->internal_manager->getTreeAccessManager($skillTree->getRefId());
                if ($tree_access_manager->hasVisibleTreePermission()) {
                    return [
                        "title" => $skillTree->getTitle(),
                        "tree" => $skillTree
                    ];
                }
                return [];
            },
            iterator_to_array($this->tree_manager->getTrees())
        ));
    }

    /**
     * @param array{tree: \ilObjSkillTree}
     */
    protected function fillRow(array $a_set) : void
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $tree_obj = $a_set["tree"];
        $tree = $this->tree_factory->getTreeById($tree_obj->getId());

        if ($this->management_access_manager->hasCreateTreePermission()) {
            $tpl->setCurrentBlock("checkbox");
            $tpl->setVariable("ID", $tree->readRootId());
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable("TITLE", $tree_obj->getTitle());

        // action
        $ctrl->setParameterByClass("ilobjskilltreegui", "ref_id", $tree_obj->getRefId());
        $tpl->setVariable("TXT_CMD", $lng->txt("edit"));
        $tpl->setVariable("HREF_CMD", $ctrl->getLinkTargetByClass("ilobjskilltreegui", "editSkills"));
    }
}
