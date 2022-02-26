<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * TableGUI class for title/description translations
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectTranslationTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_incl_desc = true, $a_base_cmd = "HeaderTitle")
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
        $this->incl_desc = $a_incl_desc;
        $this->base_cmd = $a_base_cmd;
        
        $this->setLimit(9999);
        
        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("language"), "", "");
        $this->addColumn($this->lng->txt("default"), "", "");
        $this->addColumn($this->lng->txt("title"), "", "");
        if ($a_incl_desc) {
            $this->addColumn($this->lng->txt("description"), "", "");
        }
        //		$this->addColumn($this->lng->txt("actions"), "", "");
        
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.obj_translation_row.html", "Services/Object");
        $this->disable("footer");
        $this->setEnableTitle(true);

        $this->nr = 0;
    }
    
    /**
    * Prepare output
    */
    protected function prepareOutput() : void
    {
        $lng = $this->lng;

        $this->addMultiCommand("delete" . $this->base_cmd . "s", $lng->txt("remove"));
        if ($this->dataExists()) {
            $this->addCommandButton("save" . $this->base_cmd . "s", $lng->txt("save"));
        }
        $this->addCommandButton("add" . $this->base_cmd, $lng->txt("add"));
    }
    
    /**
    * Fill table row
    */
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;

        $this->nr++;
        
        if ($this->incl_desc) {
            $this->tpl->setCurrentBlock("desc_row");
            $this->tpl->setVariable("VAL_DESC", ilLegacyFormElementsUtil::prepareFormOutput($a_set["desc"]));
            $this->tpl->setVariable("DNR", $this->nr);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("NR", $this->nr);
        
        // lang selection
        $languages = ilMDLanguageItem::_getLanguages();
        $this->tpl->setVariable(
            "LANG_SELECT",
            ilLegacyFormElementsUtil::formSelect(
                $a_set["lang"],
                "lang[" . $this->nr . "]",
                $languages,
                false,
                true
            )
        );

        if ($a_set["default"]) {
            $this->tpl->setVariable("DEF_CHECKED", "checked=\"checked\"");
        }

        $this->tpl->setVariable("VAL_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($a_set["title"]));
    }
}
