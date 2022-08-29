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
 ********************************************************************
 */

/**
 * TableGUI class for poll users
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPollUserTableGUI extends ilTable2GUI
{
    protected array $answer_ids = [];

    public function __construct(object $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->setId("ilobjpollusr");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn($lng->txt("login"), "login");
        $this->addColumn($lng->txt("lastname"), "lastname");
        $this->addColumn($lng->txt("firstname"), "firstname");

        foreach ($this->getParentObject()->getObject()->getAnswers() as $answer) {
            $this->answer_ids[] = (int) ($answer["id"] ?? 0);
            $this->addColumn((string) ($answer["answer"] ?? ''), "answer" . (int) ($answer["id"] ?? 0));
        }

        $this->getItems($this->answer_ids);

        $this->setTitle(
            $this->lng->txt("poll_question") . ": \"" .
                $this->getParentObject()->getObject()->getQuestion() . "\""
        );

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.user_row.html", "Modules/Poll");
        $this->setDefaultOrderField("login");
        $this->setDefaultOrderDirection("asc");

        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
    }

    protected function getItems(array $a_answer_ids): void
    {
        $data = [];

        foreach ($this->getParentObject()->getObject()->getVotesByUsers() as $user_id => $vote) {
            $answers = (array) ($vote["answers"] ?? []);
            unset($vote["answers"]);

            foreach ($a_answer_ids as $answer_id) {
                $vote["answer" . $answer_id] = in_array($answer_id, $answers);
            }

            $data[] = $vote;
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setCurrentBlock("answer_bl");
        foreach ($this->answer_ids as $answer_id) {
            if ($a_set["answer" . $answer_id]) {
                $this->tpl->setVariable("ANSWER", '<img src="' . ilUtil::getImagePath("icon_ok.svg") . '" />');
            } else {
                $this->tpl->setVariable("ANSWER", "&nbsp;");
            }
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("LOGIN", (string) ($a_set["login"] ?? ''));
        $this->tpl->setVariable("FIRSTNAME", (string) ($a_set["firstname"] ?? ''));
        $this->tpl->setVariable("LASTNAME", (string) ($a_set["lastname"] ?? ''));
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set): void
    {
        $a_csv->addColumn((string) ($a_set["login"] ?? ''));
        $a_csv->addColumn((string) ($a_set["lastname"] ?? ''));
        $a_csv->addColumn((string) ($a_set["firstname"] ?? ''));
        foreach ($this->answer_ids as $answer_id) {
            if ($a_set["answer" . $answer_id]) {
                $a_csv->addColumn('1');
            } else {
                $a_csv->addColumn('');
            }
        }
        $a_csv->addRow();
    }

    protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set): void
    {
        $a_excel->setCell($a_row, 0, (string) ($a_set["login"] ?? ''));
        $a_excel->setCell($a_row, 1, (string) ($a_set["lastname"] ?? ''));
        $a_excel->setCell($a_row, 2, (string) ($a_set["firstname"] ?? ''));

        $col = 2;
        foreach ($this->answer_ids as $answer_id) {
            if ($a_set["answer" . $answer_id]) {
                $a_excel->setCell($a_row, ++$col, true);
            } else {
                $a_excel->setCell($a_row, ++$col, false);
            }
        }
    }
}
