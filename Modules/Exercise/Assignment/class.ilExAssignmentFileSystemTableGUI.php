<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * File System Explorer GUI class
 * @author Jesús López <lopez@leifos.com>
 */
class ilExAssignmentFileSystemTableGUI extends ilFileSystemTableGUI
{
    //this property will define if the table needs order column.
    protected bool $add_order_column = true;
    protected string $child_class_name = 'ilExAssignmentFileSystemTableGUI';
    protected int $requested_ass_id;

    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_cur_dir,
        $a_cur_subdir,
        $a_label_enable,
        $a_file_labels,
        $a_label_header = "",
        $a_commands = array(),
        $a_post_dir_path = false
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $request = $DIC->exercise()->internal()->gui()->request();
        $this->requested_ass_id = $request->getAssId();

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
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function getEntries() : array
    {
        $entries = parent::getEntries();
        if (count($entries) > 0) {
            $this->addCommandButton("saveFilesOrder", $this->lng->txt("exc_save_order"));
        }
        $ass = new ilExAssignment($this->requested_ass_id);
        return $ass->fileAddOrder($entries);
    }

    public function numericOrdering(string $a_field) : bool
    {
        if ($a_field == "order_val") {
            return true;
        }
        return false;
    }

    public function addColumns() : void
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
        }
    }

    protected function fillRow(array $a_set) : void
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
