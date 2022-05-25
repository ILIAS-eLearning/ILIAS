<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * TableGUI class for title/description translations
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMultilingualismTableGUI extends ilTable2GUI
{
    protected int $nr;
    protected string $master_lang;
    protected string $base_cmd;
    protected bool $incl_desc;
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        bool $a_incl_desc = true,
        string $a_base_cmd = "HeaderTitle",
        string $a_master_lang = ""
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
        $this->setRowTemplate("tpl.obj_translation2_row.html", "Services/Object");
        $this->disable("footer");
        $this->setEnableTitle(true);

        $this->nr = 0;
    }
    
    protected function prepareOutput() : void
    {
        $lng = $this->lng;

        $this->addMultiCommand("delete" . $this->base_cmd . "s", $lng->txt("remove"));
        if ($this->dataExists()) {
            $this->addCommandButton("save" . $this->base_cmd . "s", $lng->txt("save"));
        }
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;

        $this->nr++;


        if (!$a_set["default"] && $a_set["lang"] != $this->master_lang) {
            $this->tpl->setCurrentBlock("cb");
            $this->tpl->setVariable("CB_NR", $this->nr);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->master_lang === "") {
            $this->tpl->setCurrentBlock("rb");
            $this->tpl->setVariable("RB_NR", $this->nr);
            if ($a_set["default"]) {
                $this->tpl->setVariable("DEF_CHECKED", "checked=\"checked\"");
            }
            $this->tpl->parseCurrentBlock();
        } elseif ($a_set["lang"] == $this->master_lang) {
            $this->tpl->setVariable("MASTER_LANG", $lng->txt("obj_master_lang"));
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
