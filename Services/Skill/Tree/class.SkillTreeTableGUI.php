<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Skill\Tree;

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
     * @var SkillTreeManager
     */
    protected $manager;

    /**
     * @var \ILIAS\Skill\Tree\SkillTreeFactory
     */
    protected $skill_tree_factory;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * Constructor
     */
    function __construct(object $a_parent_obj, string $a_parent_cmd, SkillTreeManager $manager)
    {
        global $DIC;

        $this->id = "";
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();

        $this->manager = $manager;
        $this->skill_tree_factory = $DIC->skills()->internal()->factory()->tree();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData($this->getItems());
        $this->setTitle($this->lng->txt(""));

        $this->addColumn("", "", "", true);
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($this->ctrl->getFormActionByClass("ilobjskilltreegui"));
        $this->setRowTemplate("tpl.skill_tree_row.html", "Services/Skill/Tree");

        $this->addMultiCommand("delete", $this->lng->txt("delete"));
    }

    /**
     * Get items
     * @return array[]
     */
    protected function getItems()
    {
        return array_map(function ($i) {
            return [
                "title" => $i->getTitle(),
                "tree" => $i
            ];
        },
            iterator_to_array($this->manager->getTrees()));
    }

    /**
     * Fill table row
     */
    protected function fillRow($row)
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $ui = $this->ui;

        //var_dump($row);
        $tree_obj = $row["tree"];
        $tree = $this->skill_tree_factory->getById($tree_obj->getId());

        $tpl->setVariable("ID", $tree->readRootId());
        $tpl->setVariable("TITLE", $tree_obj->getTitle());

        // actions
        $actions = [];
        $ctrl->setParameterByClass("ilobjskilltreegui", "ref_id", $tree_obj->getRefId());
        $actions[] = $ui->factory()->link()->standard(
            $lng->txt("edit"),
            $ctrl->getLinkTargetByClass("ilobjskilltreegui", "editSkills")
        );
        $dd = $ui->factory()->dropdown()->standard($actions);
        $tpl->setVariable("ACTIONS", $ui->renderer()->render($dd));
    }
}