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

use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\Repository\Table\TableAdapterGUI;

/**
 * Responsive Grid UI class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCGridGUI extends ilPageContentGUI
{
    public const TEMPLATE_MANUAL = 0;
    public const TEMPLATE_TWO_COLUMN = 1;
    public const TEMPLATE_THREE_COLUMN = 2;
    public const TEMPLATE_MAIN_SIDE = 3;
    public const TEMPLATE_TWO_BY_TWO = 4;
    protected \ILIAS\COPage\InternalDomainService $domain;

    protected ilToolbarGUI $toolbar;
    protected ilTabsGUI $tabs;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $ilToolbar = $DIC->toolbar();
        $ilTabs = $DIC->tabs();

        $this->toolbar = $ilToolbar;
        $this->tabs = $ilTabs;
        $this->domain = $DIC->copage()->internal()->domain();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand(): void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    public function insert(): void
    {
        $this->displayValidationError();
        $form = $this->initCreationForm();
        if ($this->ctrl->getCmd() == "create") {
            $form->setValuesByPost();
        }
        $this->tpl->setContent($form->getHTML());
    }

    /*
    public function editProperties() : void
    {
        $this->displayValidationError();
        $this->setTabs();

        $form = $this->initForm();
        $this->getFormValues($form);
        $html = $form->getHTML();
        $this->tpl->setContent($html);
    }*/

    /**
     * Init creation form
     */
    public function initCreationForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;

        // edit form
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("cont_ed_insert_grid"));
        $form->setDescription($this->lng->txt("cont_ed_insert_grid_info"));

        //
        $radg = new ilRadioGroupInputGUI($lng->txt("cont_pc_grid"), "layout_template");
        $radg->setValue(self::TEMPLATE_TWO_COLUMN);
        $op1 = new ilRadioOption($lng->txt("cont_grid_t_two_column"), self::TEMPLATE_TWO_COLUMN, $lng->txt("cont_grid_t_two_column_info"));
        $radg->addOption($op1);
        $op2 = new ilRadioOption($lng->txt("cont_grid_t_three_column"), self::TEMPLATE_THREE_COLUMN, $lng->txt("cont_grid_t_three_column_info"));
        $radg->addOption($op2);
        $op3 = new ilRadioOption($lng->txt("cont_grid_t_main_side"), self::TEMPLATE_MAIN_SIDE, $lng->txt("cont_grid_t_main_side_info"));
        $radg->addOption($op3);
        $op4 = new ilRadioOption($lng->txt("cont_grid_t_two_by_two"), self::TEMPLATE_TWO_BY_TWO, $lng->txt("cont_grid_t_two_by_two_info"));
        $radg->addOption($op4);
        $op5 = new ilRadioOption($lng->txt("cont_grid_t_manual"), self::TEMPLATE_MANUAL, $lng->txt("cont_grid_t_manual_info"));
        $radg->addOption($op5);
        $form->addItem($radg);


        // number of cells
        $ni = new ilNumberInputGUI($this->lng->txt("cont_grid_nr_cells"), "number_of_cells");
        $ni->setMaxLength(2);
        $ni->setSize(2);
        $op5->addSubItem($ni);

        /*$sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_ed_grid_col_width"));
        $sh->setInfo($this->lng->txt("cont_ed_grid_col_width_info"));
        $form->addItem($sh);*/

        $options = array("" => "") + ilPCGrid::getWidths();

        // widths
        foreach (ilPCGrid::getSizes() as $s) {
            $si = new ilSelectInputGUI($this->lng->txt("cont_grid_width_" . $s), $s);
            $si->setInfo($this->lng->txt("cont_grid_width_" . $s . "_info"));
            $si->setOptions($options);
            $op5->addSubItem($si);
        }

        // save/cancel buttons
        $form->addCommandButton("create", $this->lng->txt("save"));
        $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * Create new grid element
     */
    public function create(): void
    {
        $form = $this->initCreationForm();
        if ($form->checkInput()) {
            $post_layout_template = (int) $form->getInput("layout_template");
            $this->content_obj = new ilPCGrid($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
            $this->content_obj->applyTemplate(
                $post_layout_template,
                (int) $form->getInput("number_of_cells"),
                (int) $form->getInput("s"),
                (int) $form->getInput("m"),
                (int) $form->getInput("l"),
                (int) $form->getInput("xl")
            );
            $this->updated = $this->pg_obj->update();

            if ($this->updated === true) {
                $this->afterCreation();
                //$this->ctrl->returnToParent($this, "jump".$this->hier_id);
            } else {
                $this->insert();
            }
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }

    public function afterCreation(): void
    {
        $this->pg_obj->stripHierIDs();
        $this->pg_obj->addHierIDs();
        $this->ctrl->setParameter($this, "hier_id", $this->content_obj->readHierId());
        $this->ctrl->setParameter($this, "pc_id", $this->content_obj->readPCId());
        $this->content_obj->setHierId($this->content_obj->readHierId());
        $this->setHierId($this->content_obj->readHierId());
        $this->content_obj->setPcId($this->content_obj->readPCId());
        $this->edit();
    }


    //
    // Edit Grid cells
    //

    /**
     * List all cells
     */
    public function edit(): void
    {
        $this->toolbar->addButton(
            $this->lng->txt("cont_add_cell"),
            $this->ctrl->getLinkTarget($this, "addCell")
        );

        $this->setTabs();
        $this->tabs->activateTab("settings");

        $table = $this->gui->pc()->gridCellTableBuilder(
            $this->content_obj,
            $this,
            "edit"
        )->getTable();

        if ($table->handleCommand()) {
            return;
        }

        $this->tpl->setContent($table->render());
    }

    protected function getGridTable(): TableAdapterGUI
    {
        return $this->gui->pc()->gridCellTableBuilder(
            $this->content_obj,
            $this,
            "edit"
        )->getTable();
    }

    /**
     * Save positions
     */
    public function savePositions(): void
    {
        $table = $this->getGridTable();

        $ordered_ids = $table->getData();

        if (is_array($ordered_ids) && count($ordered_ids) > 0) {
            $this->content_obj->savePositions($ordered_ids);
        }
        $this->updated = $this->pg_obj->update();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "edit");
    }

    public function editWidths(string $id): void
    {
        $form = $this->getEditWidthsForm($id);
        $this->gui->modal($this->lng->txt("cont_ed_grid_col_widths"))
            ->form($form)
            ->send();
    }

    protected function getEditWidthsForm(string $id): FormAdapterGUI
    {
        $this->ctrl->setParameter($this, "tid", $id);
        $options = ["" => ""] + ilPCGrid::getWidths();

        $data = $this->content_obj->getCellData();
        $row_data = [];
        foreach ($data as $row) {
            if ($row["hier_id"] . ":" . $row["pc_id"] === $id) {
                $row_data = $row;
                break;
            }
        }

        $form = $this->gui->form([self::class], "saveWidths");
        foreach (ilPCGrid::getSizes() as $s) {
            $form->select(
                "width_" . $s,
                $this->lng->txt("cont_grid_width_" . $s),
                $options,
                "",
                $row_data[$s] ?? ""
            );
        }

        return $form;
    }

    protected function saveWidths(): void
    {
        $id = $this->request->getString("tid");
        $form = $this->getEditWidthsForm($id);
        if ($form->isValid()) {
            $w_s = [$id => $form->getData("width_s")];
            $w_m = [$id => $form->getData("width_m")];
            $w_l = [$id => $form->getData("width_l")];
            $w_xl = [$id => $form->getData("width_xl")];
            $this->content_obj->saveWidths($w_s, $w_m, $w_l, $w_xl);
            $this->updated = $this->pg_obj->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "edit");
        }
    }


    /**
     * Add cell
     */
    public function addCell(): void
    {
        $this->content_obj->addCell();
        $this->updated = $this->pg_obj->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("cont_added_cell"), true);
        $this->ctrl->redirect($this, "edit");
    }

    /**
     * Confirm cell deletion
     */
    public function confirmCellDeletion(array $tids): void
    {
        $table = $this->getGridTable();
        $ids = $table->getItemIds();

        if (count($ids) === 0) {
            $this->ctrl->redirect($this, "edit");
        }

        $retrieval = $this->domain->pc()->gridCellRetrieval($this->content_obj);
        $data = $retrieval->getData([]);
        $items = [];
        $cnt = 1;
        foreach ($data as $row) {
            if (in_array($row["id"], $ids)) {
                $items[$row["id"]] = $this->lng->txt("cont_grid_cell") . " " . $cnt;
            }
            $cnt++;
        }

        $table->renderDeletionConfirmation(
            $this->lng->txt("delete"),
            $this->lng->txt("cont_grid_cell_confirm_deletion"),
            "deleteCells",
            $items
        );
    }

    /**
     * Cancel cell deletion
     */
    public function cancelCellDeletion(): void
    {
        $this->ctrl->redirect($this, "edit");
    }

    /**
     * Delete Cells
     */
    public function deleteCells(): void
    {
        $ilCtrl = $this->ctrl;

        $table = $this->getGridTable();
        $ids = $table->getItemIds();

        foreach ($ids as $tid) {
            $ids = explode(":", $tid);
            $this->content_obj->deleteGridCell($ids[0], $ids[1]);
        }
        $this->updated = $this->pg_obj->update();

        $ilCtrl->redirect($this, "edit");
    }

    public function setTabs(): void
    {
        $this->tabs->setBackTarget(
            $this->lng->txt("pg"),
            $this->ctrl->getParentReturn($this)
        );

        $this->tabs->addTab(
            "settings",
            $this->lng->txt("settings"),
            $this->ctrl->getLinkTarget($this, "edit")
        );
    }

    /**
     * Save tabs properties in db and return to page edit screen
     */
    public function saveCellData(): void
    {
        $width_s = $this->request->getStringArray("width_s");
        $width_m = $this->request->getStringArray("width_m");
        $width_l = $this->request->getStringArray("width_l");
        $width_xl = $this->request->getStringArray("width_xl");
        $this->content_obj->saveWidths($width_s, $width_m, $width_l, $width_xl);

        $pos = $this->request->getStringArray("position");
        if (count($pos) > 0) {
            $this->content_obj->savePositions($pos);
        }
        $this->updated = $this->pg_obj->update();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "edit");
    }
}
