<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once('./Services/FileSystem/classes/class.ilFileSystemTableGUI.php');

/**
 * File System Explorer GUI class
 *
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 */
class ilExAssignmentFileSystemTableGUI extends ilFileSystemTableGUI
{
    //this property will define if the table needs order column.
    protected $add_order_column = true;
    protected $child_class_name = 'ilExAssignmentFileSystemTableGUI';

    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_cur_dir,
        $a_cur_subdir,
        $a_label_enable = false,
        $a_file_labels,
        $a_label_header = "",
        $a_commands = array(),
        $a_post_dir_path = false,
        $a_table_id = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();

        parent::__construct(
            $a_parent_obj,
            $a_parent_cmd,
            $a_cur_dir,
            $a_cur_subdir,
            $a_label_enable,
            $a_file_labels,
            $a_label_header,
            $a_commands,
            $a_post_dir_path,
            "exc_instr_files"
        );

        $this->setLimit(9999);

        //default template with order block
        //$this->setRowTemplate("tpl.exc_ass_instruction_file_row.html", "Modules/Exercise");
        $this->setDefaultOrderField("order_val");
        $this->setDefaultOrderDirection("asc");
    }

    /**
     * Add Order Values (extension of ilFilesystemgui getEntries)
     * @param array $a_entries
     * @return array items
     */
    public function getEntries()
    {
        $entries = parent::getEntries();
        if (count($entries) > 0) {
            $this->addCommandButton("saveFilesOrder", $this->lng->txt("exc_save_order"));
        }
        $ass = new ilExAssignment((int) $_GET['ass_id']);
        return $ass->fileAddOrder($entries);
    }

    /**
     *
     *
     * @param
     * @return
     */
    public function numericOrdering($a_field)
    {
        if ($a_field == "order_val") {
            return true;
        }
        return false;
    }



    public function addColumns()
    {
        if ($this->has_multi) {
            $this->setSelectAllCheckbox("file[]");
            $this->addColumn("", "", "1", true);
        }

        $this->addColumn($this->lng->txt("exc_presentation_order"), "order_val", "", false, $this->child_class_name);

        $this->addColumn("", "", "1", true); // icon

        $this->addColumn($this->lng->txt("cont_dir_file"), "name");
        $this->addColumn($this->lng->txt("cont_size"), "size");

        if ($this->label_enable) {
            $this->addColumn($this->label_header, "label");
        }

        if (sizeof($this->row_commands)) {
            $this->addColumn($this->lng->txt("actions"));
            include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
        }
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $this->tpl->setCurrentBlock("Order");
        if ($a_set['order_id']) {
            $this->tpl->setVariable("ID", $a_set['order_id']);
        }
        if ($a_set["order_val"]) {
            $this->tpl->setVariable("ORDER_VAL", $a_set["order_val"]);
        }
        $this->tpl->parseCurrentBlock();

        parent::fillRow($a_set);
    }
}
