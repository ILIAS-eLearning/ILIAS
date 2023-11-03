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
 * TableGUI class for
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageMultiLangTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $lng = $DIC->language();

        $lng->loadLanguageModule("meta");

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($lng->txt("cont_languages"));

        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("cont_language"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.page_ml_row.html", "Services/COPage");

        //if (count($this->getData()) > 1)
        //{
        $this->addMultiCommand("confirmRemoveLanguages", $lng->txt("remove"));
        //}
        //$this->addCommandButton("", $lng->txt(""));
    }

    /**
     * Fill table row
     */
    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;

        if (!$a_set["master"]) {
            $this->tpl->setCurrentBlock("cb");
            $this->tpl->setVariable("CB_LANG", $a_set["lang"]);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setVariable("ML", "(" . $lng->txt("cont_master_lang") . ")");
        }
        $this->tpl->setVariable("LANG", $lng->txt("meta_l_" . $a_set["lang"]));
    }
}
