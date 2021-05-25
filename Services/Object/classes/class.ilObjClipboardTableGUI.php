<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * TableGUI class for
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjClipboardTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($lng->txt("clipboard"));
        
        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("action"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.obj_cliboard_row.html", "Services/Object");
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        //var_dump($a_set);
        $this->tpl->setVariable("ICON", ilUtil::img(
            ilObject::_getIcon($a_set["obj_id"], "tiny"),
            $a_set["type_txt"]
        ));
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("CMD", $a_set["cmd"]);
    }
}
