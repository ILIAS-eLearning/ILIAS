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
 * Class ilSCORM2004TrackingItemsTableGUI
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004TrackingItemsTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;

    protected ilRbacSystem $rbacsystem;

    private int $obj_id = 0;
    private int $user_id = 0;
    private bool $bySCO = false;
    private array $scosSelected = array();
    private array $userSelected = array();
    private bool $allowExportPrivacy = false;
    private string $lmTitle = "";
    private string $report = "";

    /**
     * @param mixed[] $a_userSelected
     * @param mixed[] $a_scosSelected
     */
    public function __construct(int $a_obj_id, ?object $a_parent_obj, string $a_parent_cmd, array $a_userSelected, array $a_scosSelected, string $a_report)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $lng = $DIC->language();
        $lng->loadLanguageModule("scormtrac");
        $this->lng = $lng;
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();

        $this->obj_id = $a_obj_id;
        $this->report = $a_report;
        $this->scosSelected = $a_scosSelected;
        $this->userSelected = $a_userSelected;
        if ($a_parent_cmd === "showTrackingItemsBySco") {
            $this->bySCO = true;
        }
        if ($a_parent_obj !== null) {
            $this->lmTitle = $a_parent_obj->getObject()->getTitle();
            $this->setId('2004' . $this->report);
            parent::__construct($a_parent_obj, $a_parent_cmd);
        }
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

        $this->setRowTemplate('tpl.scorm2004_tracking_items.html', 'Modules/Scorm2004');
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));

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
     * @return mixed[]
     */
    public function getSelectableColumns(): array
    {
        // default fields
        $cols = array();

        switch ($this->report) {
            case "exportSelectedCore":
                $cols = ilSCORM2004TrackingItems::exportSelectedCoreColumns($this->bySCO, $this->allowExportPrivacy);
            break;
            case "exportSelectedInteractions":
                $cols = ilSCORM2004TrackingItems::exportSelectedInteractionsColumns();
            break;
            case "exportSelectedObjectives":
                $cols = ilSCORM2004TrackingItems::exportSelectedObjectivesColumns();
            break;
            case "exportObjGlobalToSystem":
                $cols = ilSCORM2004TrackingItems::exportObjGlobalToSystemColumns();
            break;
            case "tracInteractionItem":
                $cols = ilSCORM2004TrackingItems::tracInteractionItemColumns($this->bySCO, $this->allowExportPrivacy);
            break;
            case "tracInteractionUser":
                $cols = ilSCORM2004TrackingItems::tracInteractionUserColumns($this->bySCO, $this->allowExportPrivacy);
            break;
            case "tracInteractionUserAnswers":
                $cols = ilSCORM2004TrackingItems::tracInteractionUserAnswersColumns((array) $this->userSelected, (array) $this->scosSelected, $this->bySCO, $this->allowExportPrivacy);
            break;
            case "exportSelectedSuccess":
                $cols = ilSCORM2004TrackingItems::exportSelectedSuccessColumns();
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
        $this->determineOffsetAndOrder(true);
        $this->determineLimit();

        $ilSCORM2004TrackingItems = new ilSCORM2004TrackingItems();
        switch ($this->report) {
            case "exportSelectedCore":
                $tr_data = $ilSCORM2004TrackingItems->exportSelectedCore((array) $this->userSelected, (array) $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
            break;
            case "exportSelectedInteractions":
                $tr_data = $ilSCORM2004TrackingItems->exportSelectedInteractions((array) $this->userSelected, (array) $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
            break;
            case "exportSelectedObjectives":
                $tr_data = $ilSCORM2004TrackingItems->exportSelectedObjectives((array) $this->userSelected, (array) $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
            break;
            case "exportObjGlobalToSystem":
                $tr_data = $ilSCORM2004TrackingItems->exportObjGlobalToSystem((array) $this->userSelected, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
            break;
            case "tracInteractionItem":
                $tr_data = $ilSCORM2004TrackingItems->tracInteractionItem((array) $this->userSelected, (array) $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
            break;
            case "tracInteractionUser":
                $tr_data = $ilSCORM2004TrackingItems->tracInteractionUser((array) $this->userSelected, (array) $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
            break;
            case "tracInteractionUserAnswers":
                $tr_data = $ilSCORM2004TrackingItems->tracInteractionUserAnswers((array) $this->userSelected, (array) $this->scosSelected, $this->bySCO, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
            break;
            case "exportSelectedSuccess":
                $tr_data = $ilSCORM2004TrackingItems->exportSelectedSuccess((array) $this->userSelected, $this->allowExportPrivacy, $this->getObjId(), $this->lmTitle);
            break;
        }
        // $this->setMaxCount($tr_data["cnt"]);
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
        if ($id === "status") {
            $icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SCORM);
            $path = $icons->getImagePathForStatus((int) $value);
            $text = ilLearningProgressBaseGUI::_getStatusText((int) $value);
            $value = ilUtil::img($path, $text);
        }
        //BLUM round
        elseif ($id === "launch_data" || $id === "suspend_data") {
            return $value;
        }
        if (is_numeric($value)) {
            return round((float) $value, 2);
        }
        return $value;
    }

    /**
     * Fill table row
     * @throws ilTemplateException
     */
    protected function fillRow(array $a_set): void
    {
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
//        $lng = $this->lng;
//        $lng->loadLanguageModule("trac");
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
//        $lng = $this->lng;
//        $lng->loadLanguageModule("trac");
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
