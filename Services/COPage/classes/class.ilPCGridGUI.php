<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCGrid.php");
require_once("./Services/COPage/classes/class.ilPCGridCell.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
 * Responsive Grid UI class
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ingroup ServicesCOPage
 */
class ilPCGridGUI extends ilPageContentGUI
{
    protected $toolbar;
    protected $tabs;

    /**
     * Constructor
     * @param $a_pg_obj
     * @param $a_content_obj
     * @param $a_hier_id
     * @param string $a_pc_id
     */
    public function __construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $ilToolbar = $DIC->toolbar();
        $ilTabs = $DIC->tabs();

        $this->toolbar = $ilToolbar;
        $this->tabs = $ilTabs;
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
     * Insert new grid
     */
    public function insert()
    {
        $this->displayValidationError();
        $form = $this->initCreationForm();
        if ($this->ctrl->getCmd() == "create") {
            $form->setValuesByPost();
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Edit grid cells
     */
    public function editProperties()
    {
        $this->displayValidationError();
        $this->setTabs();
        
        $form = $this->initForm();
        $this->getFormValues($form);
        $html = $form->getHTML();
        $this->tpl->setContent($html);
    }

    /**
     * Init creation form
     * @return ilPropertyFormGUI
     */
    public function initCreationForm()
    {
        // edit form
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("cont_ed_insert_grid"));
        $form->setDescription($this->lng->txt("cont_ed_insert_grid_info"));

        // number of cells
        $ni = new ilNumberInputGUI($this->lng->txt("cont_grid_nr_cells"), "number_of_cells");
        $ni->setMaxLength(2);
        $ni->setSize(2);
        $form->addItem($ni);

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_ed_grid_col_width"));
        $sh->setInfo($this->lng->txt("cont_ed_grid_col_width_info"));
        $form->addItem($sh);

        $options = array("" => "") + ilPCGrid::getWidths();

        // widths
        foreach (ilPCGrid::getSizes() as $s) {
            $si = new ilSelectInputGUI($this->lng->txt("cont_grid_width_" . $s), $s);
            $si->setInfo($this->lng->txt("cont_grid_width_" . $s . "_info"));
            $si->setOptions($options);
            $form->addItem($si);
        }

        // save/cancel buttons
        $form->addCommandButton("create_grid", $this->lng->txt("save"));
        $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * Create new grid element
     */
    public function create()
    {
        $form = $this->initCreationForm();
        if ($form->checkInput()) {
            $this->content_obj = new ilPCGrid($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);

            for ($i = 0; $i < (int) $_POST["number_of_cells"]; $i++) {
                $this->content_obj->addGridCell($_POST["s"], $_POST["m"], $_POST["l"], $_POST["xl"]);
            }

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
    
    /**
     * After creation processing
     */
    public function afterCreation()
    {
        $this->pg_obj->stripHierIDs();
        $this->pg_obj->addHierIDs();
        $this->ctrl->setParameter($this, "hier_id", $this->content_obj->readHierId());
        $this->ctrl->setParameter($this, "pc_id", $this->content_obj->readPCId());
        $this->content_obj->setHierId($this->content_obj->readHierId());
        $this->setHierId($this->content_obj->readHierId());
        $this->content_obj->setPCId($this->content_obj->readPCId());
        $this->edit();
    }


    //
    // Edit Grid cells
    //
    
    /**
    * List all cells
    */
    public function edit()
    {
        $this->toolbar->addButton(
            $this->lng->txt("cont_add_cell"),
            $this->ctrl->getLinkTarget($this, "addCell")
        );

        $this->setTabs();
        $this->tabs->activateTab("settings");
        include_once("./Services/COPage/classes/class.ilPCGridCellTableGUI.php");
        $table_gui = new ilPCGridCellTableGUI($this, "edit", $this->content_obj);
        $this->tpl->setContent($table_gui->getHTML());
    }
    
    /**
     * Save cell properties
     */
    public function saveCells()
    {
        if (is_array($_POST["position"])) {
            $positions = ilUtil::stripSlashesArray($_POST["position"]);
            $this->content_obj->savePositions($positions);
        }
        $this->updated = $this->pg_obj->update();
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "edit");
    }

    /**
     * Add cell
     */
    public function addCell()
    {
        $this->content_obj->addCell("", "", "", "");
        $this->updated = $this->pg_obj->update();

        ilUtil::sendSuccess($this->lng->txt("cont_added_cell"), true);
        $this->ctrl->redirect($this, "edit");
    }
    
    /**
     * Confirm cell deletion
     */
    public function confirmCellDeletion()
    {
        $this->setTabs();

        if (!is_array($_POST["tid"]) || count($_POST["tid"]) == 0) {
            ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "edit");
        } else {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("cont_grid_cell_confirm_deletion"));
            $cgui->setCancel($this->lng->txt("cancel"), "cancelCellDeletion");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteCells");
            
            foreach ($_POST["tid"] as $k => $i) {
                $id = explode(":", $k);
                $id = explode("_", $id[0]);
                $cgui->addItem("tid[]", $k, $this->lng->txt("cont_grid_cell") . " " . $id[count($id) - 1]);
            }
            
            $this->tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Cancel cell deletion
     */
    public function cancelCellDeletion()
    {
        $this->ctrl->redirect($this, "edit");
    }
    
    /**
     * Delete Cells
     */
    public function deleteCells()
    {
        $ilCtrl = $this->ctrl;
        
        if (is_array($_POST["tid"])) {
            foreach ($_POST["tid"] as $tid) {
                $ids = explode(":", $tid);
                $this->content_obj->deleteGridCell($ids[0], $ids[1]);
            }
        }
        $this->updated = $this->pg_obj->update();
        
        $ilCtrl->redirect($this, "edit");
    }
    
    
    /**
     * Set tabs
     */
    public function setTabs()
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
    public function saveCellData()
    {
        $width_s = ilUtil::stripSlashesArray($_POST["width_s"]);
        $width_m = ilUtil::stripSlashesArray($_POST["width_m"]);
        $width_l = ilUtil::stripSlashesArray($_POST["width_l"]);
        $width_xl = ilUtil::stripSlashesArray($_POST["width_xl"]);
        $this->content_obj->saveWidths($width_s, $width_m, $width_l, $width_xl);

        if (is_array($_POST["position"])) {
            $positions = ilUtil::stripSlashesArray($_POST["position"]);
            $this->content_obj->savePositions($positions);
        }
        $this->updated = $this->pg_obj->update();
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "edit");
    }
}
