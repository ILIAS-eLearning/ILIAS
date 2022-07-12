<?php declare(strict_types=1);

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
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectTranslationTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;

    protected bool $incl_desc;
    protected string $base_cmd;
    protected int $nr;

    public function __construct(
        ?object $parent_obj,
        string $parent_cmd,
        bool $incl_desc = true,
        string $base_cmd = "HeaderTitle"
    ) {
        global $DIC;
        $this->access = $DIC->access();

        parent::__construct($parent_obj, $parent_cmd);
        $this->incl_desc = $incl_desc;
        $this->base_cmd = $base_cmd;
        
        $this->setLimit(9999);
        
        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("language"));
        $this->addColumn($this->lng->txt("default"));
        $this->addColumn($this->lng->txt("title"));
        if ($incl_desc) {
            $this->addColumn($this->lng->txt("description"));
        }

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($parent_obj));
        $this->setRowTemplate("tpl.obj_translation_row.html", "Services/Object");
        $this->disable("footer");
        $this->setEnableTitle(true);

        $this->nr = 0;
    }
    
    protected function prepareOutput() : void
    {
        $this->addMultiCommand("delete" . $this->base_cmd . "s", $this->lng->txt("remove"));
        if ($this->dataExists()) {
            $this->addCommandButton("save" . $this->base_cmd . "s", $this->lng->txt("save"));
        }
        $this->addCommandButton("add" . $this->base_cmd, $this->lng->txt("add"));
    }
    
    protected function fillRow(array $set) : void
    {
        $this->nr++;
        
        if ($this->incl_desc) {
            $this->tpl->setCurrentBlock("desc_row");
            $this->tpl->setVariable("VAL_DESC", ilLegacyFormElementsUtil::prepareFormOutput($set["desc"]));
            $this->tpl->setVariable("DNR", $this->nr);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("NR", $this->nr);
        
        // lang selection
        $languages = ilMDLanguageItem::_getLanguages();
        $this->tpl->setVariable(
            "LANG_SELECT",
            ilLegacyFormElementsUtil::formSelect(
                $set["lang"],
                "lang[" . $this->nr . "]",
                $languages,
                false,
                true
            )
        );

        if ($set["default"]) {
            $this->tpl->setVariable("DEF_CHECKED", "checked=\"checked\"");
        }

        $this->tpl->setVariable("VAL_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($set["title"]));
    }
}
