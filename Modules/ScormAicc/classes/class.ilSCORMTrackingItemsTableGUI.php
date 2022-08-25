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
 * Class ilSCORMTrackingItemsTableGUI
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @ingroup ModulesScormAicc
 */
class ilSCORMTrackingItemsTableGUI extends ilTable2GUI
{
    private int $obj_id;
    private int $user_id = 0;
    private bool $bySCO = false;
    private array $scosSelected;
    private array $userSelected;
    private bool $allowExportPrivacy;
    private string $lmTitle = "";
    private string $report;

    /**
     * @throws ilCtrlException
     * @param mixed[] $a_userSelected
     * @param mixed[] $a_scosSelected
     */
    public function __construct(int $a_obj_id, ?object $a_parent_obj, string $a_parent_cmd, array $a_userSelected, array $a_scosSelected, string $a_report)
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $rbacsystem = $DIC->rbac();
        $lng->loadLanguageModule("scormtrac");

        $this->obj_id = $a_obj_id;
        $this->report = $a_report;
        $this->scosSelected = $a_scosSelected;
        $this->userSelected = $a_userSelected;
        if ($a_parent_cmd === "showTrackingItemsBySco") {
            $this->bySCO = true;
        }
        if ($a_parent_obj !== null) {
            $this->lmTitle = $a_parent_obj->object->getTitle();
        }

        $this->setId('AICC' . $this->report);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $privacy = ilPrivacySettings::getInstance();
        $this->allowExportPrivacy = $privacy->enabledExportSCORM();


        // if($a_print_view)
        // {
        // $this->setPrintMode(true);
        // }


        foreach ($this->getSelectedColumns() as $c) {
            $l = $c;
            if (in_array($l, array("status", "time", "score"))) {
                $l = "cont_" . $l;
                // } else {
                // $l =
            }
            $s = $this->lng->txt($l);
            if (substr($l, 0, 14) === "interaction_id") {
                $s = $this->lng->txt(substr($l, 0, 14)) . ' ' . substr($l, 14);
            }
            if (substr($l, 0, 17) === "interaction_value") {
                $s = sprintf($this->lng->txt(substr($l, 0, 17)), substr($l, 17, (strpos($l, ' ') - 17))) . substr($l, strpos($l, ' '));
            }
            if (substr($l, 0, 23) === "interaction_description") {
                $s = $this->lng->txt(substr($l, 0, 23)) . ' ' . substr($l, 23);
            }
            $this->addColumn($s, $c);
        }

        $this->setRowTemplate('tpl.scorm_tracking_items.html', 'Modules/ScormAicc');
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));

        $this->setExternalSorting(true);
        //		$this->setExternalSegmentation(true);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        //		$this->setDefaultOrderField("cp_node_id, user_id");
        $this->setDefaultOrderField("");
        $this->setDefaultOrderDirection("asc");
        $this->setShowTemplates(true);

        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
        //		$this->initFilter();
        $this->getItems();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getSelectableColumns(): array
    {
        // default fields
        $cols = array();

        switch ($this->report) {
            case "exportSelectedCore":
                $cols = ilSCORMTrackingItems::exportSelectedCoreColumns($this->bySCO, $this->allowExportPrivacy);
                break;
            case "exportSelectedRaw":
                $cols = ilSCORMTrackingItems::exportSelectedRawColumns();
                break;
            case "exportSelectedInteractions":
                $cols = ilSCORMTrackingItems::exportSelectedInteractionsColumns();
                break;
            case "exportSelectedObjectives":
                $cols = ilSCORMTrackingItems::exportSelectedObjectivesColumns();
                break;
//            case "tracInteractionItem":
//                $cols = ilSCORMTrackingItems::tracInteractionItemColumns($this->bySCO, $this->allowExportPrivacy);
//            break;
//            case "tracInteractionUser":
//                $cols = ilSCORMTrackingItems::tracInteractionUserColumns($this->bySCO, $this->allowExportPrivacy);
//            break;
//            case "tracInteractionUserAnswers":
//                $cols = ilSCORMTrackingItems::tracInteractionUserAnswersColumns($this->userSelected, $this->scosSelected, $this->bySCO, $this->allowExportPrivacy);
//            break;
            case "exportSelectedSuccess":
                $cols = ilSCORMTrackingItems::exportSelectedSuccessColumns();
                break;
        }

        return $cols;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getItems(): void
    {
        global $DIC;
        $lng = $DIC->language();

        $this->determineOffsetAndOrder(true);
        $this->determineLimit();

        $ilSCORMTrackingItems = new ilSCORMTrackingItems();
        switch ($this->report) {
            case "exportSelectedCore":
                $tr_data = $ilSCORMTrackingItems->exportSelectedCore($this->userSelected, $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
                break;
            case "exportSelectedRaw":
                $tr_data = $ilSCORMTrackingItems->exportSelectedRaw($this->userSelected, $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
                break;
            case "exportSelectedInteractions":
                $tr_data = $ilSCORMTrackingItems->exportSelectedInteractions($this->userSelected, $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
                break;
            case "exportSelectedObjectives":
                $tr_data = $ilSCORMTrackingItems->exportSelectedObjectives($this->userSelected, $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
                break;
            case "tracInteractionItem":
                $tr_data = $ilSCORMTrackingItems->tracInteractionItem($this->userSelected, $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
                break;
            case "tracInteractionUser":
                $tr_data = $ilSCORMTrackingItems->tracInteractionUser($this->userSelected, $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
                break;
            case "tracInteractionUserAnswers":
                $tr_data = $ilSCORMTrackingItems->tracInteractionUserAnswers($this->userSelected, $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
                break;
            case "exportSelectedSuccess":
                $tr_data = $ilSCORMTrackingItems->exportSelectedSuccess($this->userSelected, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
                break;
        }
//        $this->setMaxCount($tr_data["cnt"]);
        if (ilUtil::stripSlashes($this->getOrderField()) != "") {
            $tr_data = ilArrayUtil::stableSortArray(
                $tr_data,
                ilUtil::stripSlashes($this->getOrderField()),
                ilUtil::stripSlashes($this->getOrderDirection())
            );
        }

        $this->setData($tr_data);
    }

    /**
     * @param string|float|int|null $value
     * @return string|float|int|null
     */
    protected function parseValue(string $id, $value, string $type)
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("trac");
        if ($id === "status") {
            $icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SCORM);
            $path = $icons->getImagePathForStatus($value);
            $text = ilLearningProgressBaseGUI::_getStatusText((int) $value);
            $value = ilUtil::img($path, $text);
        }
        //BLUM round
        if ($id === "launch_data" || $id === "suspend_data") {
            return $value;
        }
        if (is_numeric($value)) {
            return round($value, 2);
        }
        return $value;
    }

    /**
     * Fill table row
     * @throws ilTemplateException
     */
    protected function fillRow(array $a_set): void
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        foreach ($this->getSelectedColumns() as $c) {
            $this->tpl->setCurrentBlock("user_field");
            $val = $this->parseValue($c, $a_set[$c], "scormtrac");
            $this->tpl->setVariable("VAL_UF", $val);
            $this->tpl->parseCurrentBlock();
        }
    }

    protected function fillHeaderExcel(ilExcel $a_excel, int &$a_row): void
    {
        $labels = $this->getSelectableColumns();
        $cnt = 0;
        foreach ($this->getSelectedColumns() as $c) {
            $a_excel->setCell($a_row, $cnt, $labels[$c]["txt"]);
            $cnt++;
        }
    }

    protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set): void
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("trac");
        $cnt = 0;
        foreach ($this->getSelectedColumns() as $c) {
            if ($c !== 'status') {
                $val = $this->parseValue($c, $a_set[$c], "user");
            } else {
                $val = ilLearningProgressBaseGUI::_getStatusText((int) $a_set[$c]);
            }
            $a_excel->setCell($a_row, $cnt, $val);
            $cnt++;
        }
    }

    protected function fillHeaderCSV(ilCSVWriter $a_csv): void
    {
        $labels = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $c) {
            $a_csv->addColumn($labels[$c]["txt"]);
        }

        $a_csv->addRow();
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set): void
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("trac");
        foreach ($this->getSelectedColumns() as $c) {
            if ($c !== 'status') {
                $val = $this->parseValue($c, $a_set[$c], "user");
            } else {
                $val = ilLearningProgressBaseGUI::_getStatusText((int) $a_set[$c]);
            }
            $a_csv->addColumn($val);
        }

        $a_csv->addRow();
    }
}
