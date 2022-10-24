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
class ilSurveyPhrasesTableGUI extends ilTable2GUI
{
    protected bool $confirmdelete;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        bool $confirmdelete = false
    ) {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->confirmdelete = $confirmdelete;

        $this->setFormName('phrases');
        $this->setStyle('table', 'fullwidth');
        if (!$confirmdelete) {
            $this->addColumn('', 'f', '1%');
        }
        $this->addColumn($this->lng->txt("phrase"), 'phrase', '');
        $this->addColumn($this->lng->txt("answers"), 'answers', '');

        if ($confirmdelete) {
            $this->addCommandButton('confirmDeletePhrase', $this->lng->txt('confirm'));
            $this->addCommandButton('cancelDeletePhrase', $this->lng->txt('cancel'));
        } else {
            $this->addMultiCommand('editPhrase', $this->lng->txt('edit'));
            $this->addMultiCommand('deletePhrase', $this->lng->txt('delete'));
        }

        $this->setRowTemplate("tpl.il_svy_qpl_phrase_row.html", "Modules/SurveyQuestionPool");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("phrase");
        $this->setDefaultOrderDirection("asc");

        if ($confirmdelete) {
            $this->disable('sort');
            $this->disable('select_all');
        } else {
            $this->setPrefix('phrase');
            $this->setSelectAllCheckbox('phrase');
            $this->enable('sort');
            $this->enable('select_all');
        }
        $this->enable('header');
    }

    protected function fillRow(array $a_set): void
    {
        if (!$this->confirmdelete) {
            $this->tpl->setCurrentBlock('checkbox');
            $this->tpl->setVariable('CB_PHRASE_ID', $a_set["phrase_id"]);
        } else {
            $this->tpl->setCurrentBlock('hidden');
            $this->tpl->setVariable('HIDDEN_PHRASE_ID', $a_set["phrase_id"]);
        }
        $this->tpl->parseCurrentBlock();
        $this->tpl->setVariable('PHRASE_ID', $a_set["phrase_id"]);
        $this->tpl->setVariable("PHRASE", $a_set["phrase"]);
        $this->tpl->setVariable("ANSWERS", $a_set["answers"]);
    }
}
