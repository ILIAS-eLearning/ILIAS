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
 * TableGUI class for poll answers
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPollAnswerTableGUI extends ilTable2GUI
{
    public function __construct(?object $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();

        $this->setId("ilobjpollaw");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn($this->lng->txt("poll_sortorder"), "pos");
        $this->addColumn($this->lng->txt("poll_answer"), "answer");
        $this->addColumn($this->lng->txt("poll_absolute"), "votes");
        $this->addColumn($this->lng->txt("poll_percentage"), "percentage");

        $total = $this->getItems();

        $this->setTitle(
            $this->lng->txt("poll_question") . ": \"" .
                $a_parent_obj->getObject()->getQuestion() . "\""
        );
        $this->setDescription(sprintf($this->lng->txt("poll_population"), $total));

        if ($total) {
            $this->addCommandButton("confirmDeleteAllVotes", $this->lng->txt("poll_delete_votes"));
        }

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.answer_row.html", "Modules/Poll");
        $this->setDefaultOrderField("pos");
        $this->setDefaultOrderDirection("asc");

        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
    }

    public function numericOrdering(string $a_field): bool
    {
        return $a_field !== "answer";
    }

    public function getItems(): int
    {
        $data = $this->parent_obj->getObject()->getAnswers();
        $perc = $this->parent_obj->getObject()->getVotePercentages();
        $total = (int) ($perc["total"] ?? 0);
        $perc = (array) ($perc["perc"] ?? []);

        // add current percentages
        foreach ($data as $idx => $item) {
            $item_id = (int) ($item['id'] ?? 0);
            if (!isset($perc[$item_id])) {
                $data[$idx]["percentage"] = 0;
                $data[$idx]["votes"] = 0;
            } else {
                $data[$idx]["percentage"] = round((float) ($perc[$item_id]["perc"] ?? 0));
                $data[$idx]["votes"] = (int) ($perc[$item_id]["abs"] ?? 0);
            }
        }

        $this->setData($data);

        return $total;
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("VALUE_POS", (int) ($a_set["pos"] ?? 10) / 10);
        $this->tpl->setVariable("TXT_ANSWER", nl2br((string) ($a_set["answer"] ?? '')));
        $this->tpl->setVariable("VALUE_VOTES", (int) ($a_set["votes"] ?? 0));
        $this->tpl->setVariable("VALUE_PERCENTAGE", (int) ($a_set["percentage"] ?? 0));
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set): void
    {
        $a_csv->addColumn((string) ((int) ($a_set["pos"] ?? 10) / 10));
        $a_csv->addColumn((string) ($a_set["answer"] ?? ''));
        $a_csv->addColumn((string) ((int) ($a_set["votes"] ?? 0)));
        $a_csv->addColumn((string) ((int) ($a_set["percentage"] ?? 0)));
        $a_csv->addRow();
    }

    protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set): void
    {
        $a_excel->setCell($a_row, 0, (int) ($a_set["pos"] ?? 10) / 10);
        $a_excel->setCell($a_row, 1, (string) ($a_set["answer"] ?? ''));
        $a_excel->setCell($a_row, 2, (int) ($a_set["votes"] ?? 0));
        $a_excel->setCell($a_row, 3, (int) ($a_set["percentage"] ?? 0) . "%");
    }
}
