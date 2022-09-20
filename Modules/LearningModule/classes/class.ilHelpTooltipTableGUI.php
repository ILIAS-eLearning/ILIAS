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
 * Help mapping
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHelpTooltipTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        string $a_comp
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->setId("lm_help_tooltips");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData(ilHelp::getAllTooltips($a_comp));

        $this->setTitle($lng->txt("help_tooltips"));

        $this->addColumn("", "", "1px", true);
        $this->addColumn($this->lng->txt("help_tooltip_id"));
        $this->addColumn($this->lng->txt("help_tt_text"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.help_tooltip.html", "Modules/LearningModule");
        $this->setDefaultOrderField("tt_id");
        $this->setDefaultOrderDirection("asc");

        $this->addCommandButton("saveTooltips", $lng->txt("save"));
        $this->addMultiCommand("deleteTooltips", $lng->txt("delete"));
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("ID", $a_set["id"]);
        $this->tpl->setVariable("TEXT", ilLegacyFormElementsUtil::prepareFormOutput($a_set["text"]));
        $this->tpl->setVariable("TT_ID", ilLegacyFormElementsUtil::prepareFormOutput($a_set["tt_id"]));
    }
}
