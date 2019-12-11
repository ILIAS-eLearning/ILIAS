<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for file system
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesFileSystemStorage
*/
class ilFileSystemTableGUI extends ilTable2GUI
{
    protected $has_multi; // [bool]
    protected $row_commands = array();
    
    /**
    * Constructor
    */
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
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];

        $this->setId($a_table_id);
        $this->cur_dir = $a_cur_dir;
        $this->cur_subdir = $a_cur_subdir;
        $this->label_enable = $a_label_enable;
        $this->label_header = $a_label_header;
        $this->file_labels = $a_file_labels;
        $this->post_dir_path = $a_post_dir_path;
        $this->lng = $lng;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($lng->txt("cont_files") . " " . $this->cur_subdir);
        
        $this->has_multi = false;
        for ($i=0; $i < count($a_commands); $i++) {
            if (!$a_commands[$i]["single"]) {
                // does also handle internal commands
                $this->addMultiCommand("extCommand_" . $i, $a_commands[$i]["name"]);
                $this->has_multi = true;
            } else {
                $this->row_commands[] = array(
                    "cmd" => "extCommand_" . $i,
                    "caption" => $a_commands[$i]["name"],
                    "allow_dir" => $a_commands[$i]["allow_dir"]
                );
            }
        }

        $this->addColumns();

        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");
        
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.directory_row.html",
            "Services/FileSystem"
        );
        $this->setEnableTitle(true);
    }
    
    public function numericOrdering($a_field)
    {
        if ($a_field == "size") {
            return true;
        }
        return false;
    }

    /**
    * Get data just before output
    */
    public function prepareOutput()
    {
        $this->determineOffsetAndOrder(true);
        $this->setData($this->getEntries());
    }
    
    
    /**
    * Get entries
    */
    public function getEntries()
    {
        if (is_dir($this->cur_dir)) {
            $entries = ilUtil::getDir($this->cur_dir);
        } else {
            $entries = array(array("type" => "dir", "entry" => ".."));
        }
        $items = array();

        foreach ($entries as $e) {
            if (($e["entry"] == ".") || ($e["entry"] == ".." && empty($this->cur_subdir))) {
                continue;
            }
            $cfile = (!empty($this->cur_subdir))
                ? $this->cur_subdir . "/" . $e["entry"]
                : $e["entry"];
                
            if ($this->label_enable) {
                $label = (is_array($this->file_labels[$cfile]))
                    ? implode($this->file_labels[$cfile], ", ")
                    : "";
            }

            $pref = ($e["type"] == "dir")
                ? ($this->getOrderDirection() != "desc" ? "1_" : "9_")
                : "5_";
            $items[] = array("file" => $cfile, "entry" => $e["entry"],
                "type" => $e["type"], "label" => $label, "size" => $e["size"],
                "name" => $pref . $e["entry"]);
        }
        return $items;
    }

    public function addColumns()
    {
        if ($this->has_multi) {
            $this->setSelectAllCheckbox("file[]");
            $this->addColumn("", "", "1", true);
        }
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
     * @param array $entry
     * @return bool
     */
    private function isDoubleDotDirectory(array $entry)
    {
        return $entry['entry'] === '..';
    }

    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        $hash = $this->post_dir_path
            ? md5($a_set["file"])
            : md5($a_set["entry"]);

        if ($this->has_multi) {
            if ($this->isDoubleDotDirectory($a_set)) {
                $this->tpl->touchBlock('no_checkbox');
            } else {
                $this->tpl->setVariable("CHECKBOX_ID", $hash);
            }
        }

        // label
        if ($this->label_enable) {
            $this->tpl->setCurrentBlock("Label");
            $this->tpl->setVariable("TXT_LABEL", $a_set["label"]);
            $this->tpl->parseCurrentBlock();
        }
        
        $ilCtrl->setParameter($this->parent_obj, "cdir", $this->cur_subdir);

        //$this->tpl->setVariable("ICON", $obj["title"]);
        if ($a_set["type"] == "dir") {
            $this->tpl->setCurrentBlock("FileLink");
            $ilCtrl->setParameter($this->parent_obj, "newdir", $a_set["entry"]);
            $ilCtrl->setParameter($this->parent_obj, "resetoffset", 1);
            $this->tpl->setVariable(
                "LINK_FILENAME",
                $ilCtrl->getLinkTarget($this->parent_obj, "listFiles")
            );
            $ilCtrl->setParameter($this->parent_obj, "newdir", "");
            $this->tpl->setVariable("TXT_FILENAME", $a_set["entry"]);
            $this->tpl->parseCurrentBlock();

            $this->tpl->setVariable("ICON", "<img src=\"" .
                ilUtil::getImagePath("icon_cat.svg") . "\">");
            $ilCtrl->setParameter($this->parent_obj, "resetoffset", "");
        } else {
            $this->tpl->setCurrentBlock("File");
            $this->tpl->setVariable("TXT_FILENAME2", $a_set["entry"]);
            $this->tpl->parseCurrentBlock();
        }
        
        if ($a_set["type"] != "dir") {
            $this->tpl->setVariable("TXT_SIZE", ilUtil::formatSize($a_set["size"]));
        }

        // single item commands
        if (sizeof($this->row_commands) &&
            !($a_set["type"] == "dir" && $a_set["entry"] == "..")) {
            $advsel = new ilAdvancedSelectionListGUI();
            //$advsel->setListTitle($this->lng->txt("actions"));
            foreach ($this->row_commands as $rcom) {
                if ($rcom["allow_dir"] || $a_set["type"] != "dir") {
                    include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");

                    if (($rcom["caption"] == "Unzip" && ilMimeTypeUtil::getMimeType($this->cur_dir . $a_set['entry']) == "application/zip") || $rcom["caption"] != "Unzip") {
                        $ilCtrl->setParameter($this->parent_obj, "fhsh", $hash);
                        $url = $ilCtrl->getLinkTarget($this->parent_obj, $rcom["cmd"]);
                        $ilCtrl->setParameter($this->parent_obj, "fhsh", "");

                        $advsel->addItem($rcom["caption"], "", $url);
                    }
                }
            }
            $this->tpl->setVariable("ACTIONS", $advsel->getHTML());
        }
    }
}
