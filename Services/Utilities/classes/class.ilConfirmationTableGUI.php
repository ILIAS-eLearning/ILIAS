<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup Services
*/
class ilConfirmationTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    
    /**
    * Constructor
    */
    public function __construct($a_use_icons)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        
        $this->use_icons = $a_use_icons;
        
        parent::__construct(null, "");
        $this->setTitle($lng->txt(""));
        $this->setLimit(9999);
        
        if ($this->use_icons) {
            $this->addColumn($this->lng->txt("type"), "", "1");
        }
        $this->addColumn($this->lng->txt("title"));
        
        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.confirmation_row.html", "Services/Utilities");
        $this->disable("footer");
        $this->setEnableTitle(true);
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($item)
    {
        $lng = $this->lng;

        if ($this->use_icons) {
            if ($item["img"] != "") {
                $this->tpl->setCurrentBlock("img_cell");
                $this->tpl->setVariable("IMG_ITEM", $item["img"]);
                $this->tpl->setVariable("ALT_ITEM", $item["alt"]);
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->touchBlock("blank_cell");
            }
        }
        $this->tpl->setVariable("TXT_ITEM", $item["text"]);
        if (isset($item['var'])  && $item['var']) {
            $this->tpl->setVariable('VAR_ITEM', $item['var']);
            $this->tpl->setVariable('ID', $item['id']);
        }
    }
}
