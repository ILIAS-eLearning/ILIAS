<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * TableGUI class for title/description translations
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectTranslation2TableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    protected $fallback_mode = false;
    protected $fallback_lang = "";

    /**
    * Constructor
    */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_incl_desc = true,
        $a_base_cmd = "HeaderTitle",
        $a_master_lang = "",
        $a_fallback_mode = false,
        $a_fallback_lang = ""
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->incl_desc = $a_incl_desc;
        $this->base_cmd = $a_base_cmd;
        $this->master_lang = $a_master_lang;
        $this->fallback_mode = $a_fallback_mode;
        $this->fallback_lang = $a_fallback_lang;

        $this->setLimit(9999);
        
        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("language"), "", "");
        $this->addColumn($this->lng->txt("default"), "", "");
        $this->addColumn($this->lng->txt("title"), "", "");
        if ($a_incl_desc) {
            $this->addColumn($this->lng->txt("description"), "", "");
        }

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.obj_translation2_row.html", "Services/Object");
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
        if ($this->fallback_mode) {
            $this->addMultiCommand("setFallback", $lng->txt("obj_set_fallback_lang"));
        }
        if ($this->dataExists()) {
            $this->addCommandButton("save" . $this->base_cmd . "s", $lng->txt("save"));
        }
    }
    
    /**
    * Fill table row
    */
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;

        $this->nr++;

        if (!$a_set["default"] && $a_set["lang"] != $this->master_lang) {
            $this->tpl->setCurrentBlock("cb");
            $this->tpl->setVariable("CB_NR", $this->nr);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->master_lang == "") {
            $this->tpl->setCurrentBlock("rb");
            $this->tpl->setVariable("RB_NR", $this->nr);
            if ($a_set["default"]) {
                $this->tpl->setVariable("DEF_CHECKED", "checked=\"checked\"");
            }
            $this->tpl->parseCurrentBlock();
        } elseif ($a_set["lang"] == $this->master_lang) {
            $this->tpl->setVariable("MASTER_LANG", $lng->txt("obj_master_lang"));
        }
        if ($this->master_lang != "" && $a_set["lang"] == $this->fallback_lang) {
            $this->tpl->setVariable("FALLBACK_LANG", $lng->txt("obj_fallback_lang"));
        }

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


        $this->tpl->setVariable("VAL_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($a_set["title"]));
    }
}
