<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Classic table for rep object lists, including checkbox
 *
 * @author killing@leifos.de
 */
class ilDashObjectsTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, int $sub_id)
    {
        global $DIC;

        $this->id = "dash_obj_" . $sub_id;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        //$this->setData($this->getItems());
        $this->setTitle($this->lng->txt(""));

        $this->addColumn("", "", "", true);

        $this->setEnableNumInfo(false);
        $this->setEnableHeader(false);

        //$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.dash_obj_row.html", "Services/Dashboard");

        //$this->addMultiCommand("", $this->lng->txt(""));
        //$this->addCommandButton("", $this->lng->txt(""));
        $this->setLimit(9999);
    }

    /**
     * Get items
     *
     * @return array[]
     */
    /*
    protected function getItems()
    {
        $items = [];

        return $items;
    }*/

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $tpl->setVariable("ID", $a_set["ref_id"]);
        $tpl->setVariable("ICON", ilObject::_getIcon($a_set["obj_id"]));
        $tpl->setVariable("TITLE", $a_set["title"]);
    }
}
