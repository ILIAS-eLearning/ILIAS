<?php

declare(strict_types=1);

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
class ilObjectTranslation2TableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;

    protected bool $incl_desc;
    protected string $base_cmd;
    protected string $master_lang;
    protected bool $fallback_mode;
    protected string $fallback_lang;
    protected int $nr;

    public function __construct(
        ?object $parent_obj,
        string $parent_cmd,
        bool $incl_desc = true,
        string $base_cmd = "HeaderTitle",
        string $master_lang = "",
        bool $fallback_mode = false,
        string $fallback_lang = ""
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();

        parent::__construct($parent_obj, $parent_cmd);

        $this->incl_desc = $incl_desc;
        $this->base_cmd = $base_cmd;
        $this->master_lang = $master_lang;
        $this->fallback_mode = $fallback_mode;
        $this->fallback_lang = $fallback_lang;

        $this->setLimit(9999);

        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("language"));
        $this->addColumn($this->lng->txt("default"));
        $this->addColumn($this->lng->txt("title"));
        if ($incl_desc) {
            $this->addColumn($this->lng->txt("description"));
        }

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($parent_obj));
        $this->setRowTemplate("tpl.obj_translation2_row.html", "Services/Object");
        $this->disable("footer");
        $this->setEnableTitle(true);

        $this->nr = 0;
    }

    protected function prepareOutput(): void
    {
        $this->addMultiCommand("delete" . $this->base_cmd . "s", $this->lng->txt("remove"));
        if ($this->fallback_mode) {
            $this->addMultiCommand("setFallback", $this->lng->txt("obj_set_fallback_lang"));
        }
        if ($this->dataExists()) {
            $this->addCommandButton("save" . $this->base_cmd . "s", $this->lng->txt("save"));
        }
    }

    protected function fillRow(array $set): void
    {
        $this->nr++;

        if (!$set["default"] && $set["lang"] != $this->master_lang) {
            $this->tpl->setCurrentBlock("cb");
            $this->tpl->setVariable("CB_NR", $this->nr);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->master_lang == "") {
            $this->tpl->setCurrentBlock("rb");
            $this->tpl->setVariable("RB_NR", $this->nr);
            if ($set["default"]) {
                $this->tpl->setVariable("DEF_CHECKED", "checked=\"checked\"");
            }
            $this->tpl->parseCurrentBlock();
        } elseif ($set["lang"] == $this->master_lang) {
            $this->tpl->setVariable("MASTER_LANG", $this->lng->txt("obj_master_lang"));
        }
        if ($this->master_lang != "" && $set["lang"] == $this->fallback_lang) {
            $this->tpl->setVariable("FALLBACK_LANG", $this->lng->txt("obj_fallback_lang"));
        }

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


        $this->tpl->setVariable("VAL_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($set["title"]));
    }
}
