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

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData($this->getItems());
        $this->setTitle($this->lng->txt(""));

        $this->addColumn("", "", "", true);
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_tree_row.html", "Services/Skill/Tree");

        $this->addMultiCommand("", $this->lng->txt(""));
        $this->addCommandButton("", $this->lng->txt(""));
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

        $tree = $row["tree"];

        $tpl->setVariable("REF_ID", $tree->getRefId());
        $tpl->setVariable("TITLE", $tree->getTitle());

        // actions
        $actions = [];
        $ctrl->setParameterByClass("ilobjskilltreegui", "ref_id", $tree->getRefId());
        $actions[] = $ui->factory()->link()->standard(
            $lng->txt("edit"),
            $ctrl->getLinkTargetByClass("ilobjskilltreegui", "edit")
        );
        $dd = $ui->factory()->dropdown()->standard($actions);
        $tpl->setVariable("ACTIONS", $ui->renderer()->render($dd));
    }
}