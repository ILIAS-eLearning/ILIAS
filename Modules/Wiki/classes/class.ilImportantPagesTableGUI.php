<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Important pages table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilImportantPagesTableGUI extends ilTable2GUI
{
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
        $data = array("page_id" => 0) +
            ilObjWiki::_lookupImportantPagesList($a_parent_obj->object->getId());
        $this->setData($data);
        $this->setTitle($lng->txt(""));
        $this->setLimit(9999);
        
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("wiki_ordering"), "order");
        $this->addColumn($this->lng->txt("wiki_indentation"));
        $this->addColumn($this->lng->txt("wiki_page"));
        $this->addColumn($this->lng->txt("wiki_purpose"));
        
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.imp_pages_row.html", "Modules/Wiki");
        //$this->disable("footer");
        $this->setEnableTitle(true);
        
        $this->addMultiCommand("confirmRemoveImportantPages", $lng->txt("remove"));
        $this->addMultiCommand("setAsStartPage", $lng->txt("wiki_set_as_start_page"));
        $this->addCommandButton("saveOrderingAndIndent", $lng->txt("wiki_save_ordering_and_indent"));
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;

        if ($a_set["page_id"] > 0) {
            $this->tpl->setCurrentBlock("cb");
            $this->tpl->setVariable("PAGE_ID", $a_set["page_id"]);
            $this->tpl->parseCurrentBlock();
            
            $this->tpl->setCurrentBlock("ord");
            $this->tpl->setVariable("PAGE_ID_ORD", $a_set["page_id"]);
            $this->tpl->setVariable("VAL_ORD", $a_set["ord"]);
            $this->tpl->parseCurrentBlock();
            
            $this->tpl->setVariable(
                "PAGE_TITLE",
                ilWikiPage::lookupTitle($a_set["page_id"])
            );
            $this->tpl->setVariable(
                "SEL_INDENT",
                ilUtil::formSelect(
                    $a_set["indent"],
                    "indent[" . $a_set["page_id"] . "]",
                    array(0 => "0", 1 => "1", 2 => "2"),
                    false,
                    true
                )
            );
        } else {
            $this->tpl->setVariable(
                "PAGE_TITLE",
                ($this->getParentObject()->object->getStartPage())
            );

            $this->tpl->setVariable(
                "PURPOSE",
                $lng->txt("wiki_start_page")
            );
        }
    }
}
