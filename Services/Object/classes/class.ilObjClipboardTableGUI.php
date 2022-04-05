<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * TableGUI class for
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjClipboardTableGUI extends ilTable2GUI
{
    public function __construct(?object $parent_obj, string $parent_cmd)
    {
        parent::__construct($parent_obj, $parent_cmd);

        $this->setTitle($this->lng->txt("clipboard"));
        
        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("action"));
        
        $this->setFormAction($this->ctrl->getFormAction($parent_obj));
        $this->setRowTemplate("tpl.obj_cliboard_row.html", "Services/Object");
    }
    
    protected function fillRow(array $set) : void
    {
        $this->tpl->setVariable(
            "ICON",
            ilUtil::img(ilObject::_getIcon((int) $set["obj_id"], "tiny"), $set["type_txt"])
        );
        $this->tpl->setVariable("TITLE", $set["title"]);
        $this->tpl->setVariable("CMD", $set["cmd"]);
    }
}
