<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Paste style overview table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesStyle
 */
class ilPasteStyleCharacteristicTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;


    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($lng->txt("sty_paste_characteristics"));
        $this->setLimit(9999);
        $st_c = explode(":::", $_SESSION["sty_copy"]);
        $this->from_style_id = $st_c[0];
        $this->from_style_type = $st_c[1];
        $this->setData(explode("::", $st_c[2]));
        $this->addColumn($this->lng->txt("name"));
        $this->addColumn($this->lng->txt("type"));
        $this->addColumn($this->lng->txt("sty_if_style_class_already_exists"));

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.paste_style_row.html", "Services/Style/Content");
        $this->disable("footer");
        $this->setEnableTitle(true);

        //$this->addMultiCommand("", $lng->txt(""));
        $this->addCommandButton("pasteCharacteristics", $lng->txt("paste"));
        $this->addCommandButton("edit", $lng->txt("cancel"));
        $this->addHiddenInput("from_style_id", $this->from_style_id);
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;

        $char = explode(".", $a_set);
        $this->tpl->setVariable("CHAR", $a_set);
        $this->tpl->setVariable("SEL_OVERWRITE", 'checked="checked"');
        $this->tpl->setVariable("VAL_TYPE", $lng->txt("sty_type_" . $char[0]));
        $this->tpl->setVariable("VAL_TITLE", $char[2]);
        $this->tpl->setVariable("TXT_OVERWRITE", $lng->txt("sty_overwrite"));
        $this->tpl->setVariable("TXT_IGNORE", $lng->txt("sty_keep_existing"));
    }
}
