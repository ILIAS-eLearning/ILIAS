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
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveySavePhraseTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $this->setFormName('phrases');
        $this->setStyle('table', 'fullwidth');

        $this->addColumn($this->lng->txt("answer"), '', '');
        $this->addColumn($this->lng->txt("use_other_answer"), '', '');
        $this->addColumn($this->lng->txt("scale"), '', '');

        $this->setRowTemplate("tpl.il_svy_qpl_phrase_save_row.html", "Modules/SurveyQuestionPool");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->disable('sort');
        $this->disable('select_all');
        $this->enable('header');
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("ANSWER", $a_set["answer"]);
        $this->tpl->setVariable("OPEN_ANSWER", ($a_set["other"]) ? $this->lng->txt('yes') : $this->lng->txt('no'));
        $this->tpl->setVariable("SCALE", $a_set["scale"]);
    }
}
