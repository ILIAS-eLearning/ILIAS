<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for ordering pages to be printed/exported
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesWiki
 */
class ilWikiExportOrderTableGUI extends ilTable2GUI
{
    protected $order; // [int]
    
    /**
     * Constructor
     *
     * @param object $a_parent_obj
     * @param string $a_parent_cmd
     * @param bool $a_pdf_export
     * @param array &$a_all_pages
     * @param array $a_page_ids
     */
    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_pdf_export, array &$a_all_pages, array $a_page_ids)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        if (!(bool) $a_pdf_export) {
            $title = "wiki_show_print_view";
            $cmd = "printView";
        } else {
            $title = "wiki_show_pdf_export";
            $cmd = "pdfExport";
        }
        
        $this->setTitle($lng->txt($title));
        
        $this->addColumn($lng->txt("wiki_ordering"), "", "1");
        $this->addColumn($lng->txt("wiki_page"));
                
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), $this->getParentCmd()));
        $this->addCommandButton($this->getParentCmd(), $lng->txt("refresh"));
        
        include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
        $button = ilSubmitButton::getInstance();
        $button->setOmitPreventDoubleSubmission((bool) $a_pdf_export);
        $button->setCaption("continue");
        $button->setCommand($cmd);
        $this->addCommandButtonInstance($button);
        
        $this->setRowTemplate("tpl.table_row_export_order.html", "Modules/Wiki");
        $this->setLimit(9999);
        
        $this->getItems($a_all_pages, $a_page_ids);
    }
    
    /**
    * Get contributors of wiki
    */
    protected function getItems(array &$a_all_pages, array $a_page_ids)
    {
        $data = array();
        
        foreach ($a_page_ids as $page_id) {
            $data[] = array(
                "id" => $page_id,
                "title" => $a_all_pages[$page_id]["title"]
            );
        }
        
        $this->setData($data);
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $this->order += 10;
        
        $this->tpl->setVariable("PAGE_ID", $a_set["id"]);
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("ORDER", $this->order);
    }
}
