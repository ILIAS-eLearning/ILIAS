<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Skill\Tree;

use ILIAS\Skill\Access\SkillManagementAccess;
use ILIAS\Skill\Service\SkillInternalManagerService;

/**
 * Skill tree objects table
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillTreeTableGUI extends \ilTable2GUI
{
    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var SkillInternalManagerService
     */
    protected $internal_manager;

    /**
     * @var SkillTreeManager
     */
    protected $tree_manager;

    /**
     * @var SkillManagementAccess
     */
    protected $management_access_manager;

    /**
     * @var SkillTreeFactory
     */
    protected $tree_factory;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var int
     */
    protected $requested_ref_id;

    /**
     * Constructor
     */
    function __construct(object $a_parent_obj, string $a_parent_cmd, SkillInternalManagerService $manager)
    {
        global $DIC;

        $this->id = "";
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->request = $DIC->http()->request();

        $params = $this->request->getQueryParams();
        $this->requested_ref_id = (int) ($params["ref_id"] ?? 0);

        $this->internal_manager = $manager;
        $this->tree_manager = $manager->getTreeManager();
        $this->management_access_manager = $manager->getManagementAccessManager($this->requested_ref_id);
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
     * Get items
     * @return array[]
     */
    protected function getItems()
    {
        return array_filter(array_map(function ($i) {
            $tree_access_manager = $this->internal_manager->getTreeAccessManager($i->getRefId());
            if ($tree_access_manager->hasVisibleTreePermission()) {
                return [
                    "title" => $i->getTitle(),
                    "tree" => $i
                ];
            }
        },
            iterator_to_array($this->tree_manager->getTrees())));
    }

    /**
     * Fill table row
     */
    protected function fillRow($row)
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $tree_obj = $row["tree"];
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