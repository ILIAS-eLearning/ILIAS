<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* Select subitems for export
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesExport
*/
class ilSubItemSelectionTableGUI extends ilTable2GUI
{
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_ref_id,
        $a_cmd = "createExportFile",
        $a_cmd_txt = ""
    ) {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $lng->loadLanguageModule("export");

        if ($a_cmd_txt == "") {
            $a_cmd_txt = $lng->txt("export_create_export_file");
        }

        $this->addColumn($lng->txt("export_resource"));
        $this->addColumn($lng->txt("export_last_export"));
        $this->addColumn($lng->txt("export_last_export_file"), "", "20%");
        $this->addColumn($lng->txt("export_create_new_file"), "", "20%");
        $this->addColumn($lng->txt("export_omit_resource"), "", "20%");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->addCommandButton($a_cmd, $a_cmd_txt);
        $this->setRowTemplate(
            "tpl.sub_item_selection_row.html",
            "Services/Export"
        );
        $this->setTitle($lng->txt("export_select_resources"));
        include_once("./Services/Export/classes/class.ilExport.php");
        $this->setData(ilExport::_getValidExportSubItems($a_ref_id));
        $this->setLimit(99999);
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $now = new ilDateTime(time(), IL_CAL_UNIX);
        
        $this->tpl->setVariable("IMG_ALT", $lng->txt("obj_" . $a_set["type"]));
        $this->tpl->setVariable("IMG_SRC", ilObject::_getIcon(
            $a_set["obj_id"],
            "small",
            $a_set["type"]
        ));
        $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
        $this->tpl->setVariable("ID", $a_set["ref_id"]);
        $this->tpl->setVariable("TXT_LAST_EXPORT_FILE", $lng->txt("export_last_file"));
        $this->tpl->setVariable("TXT_OMIT", $lng->txt("export_omit"));
        $this->tpl->setVariable("TXT_CREATE_NEW_EXPORT_FILE", $lng->txt("export_create"));
        $preset = "CREATE";
        if ($a_set["timestamp"] > 0) {
            $last_export = new ilDateTime($a_set["timestamp"], IL_CAL_UNIX);
            $this->tpl->setVariable(
                "VAL_LAST_EXPORT",
                ilDatePresentation::formatDate($last_export)
            );
            if (ilDateTime::_equals($last_export, $now, IL_CAL_DAY)) {
                $preset = "LAST_FILE";
            }
        }
        $this->tpl->setVariable("SEL_" . $preset, ' checked="checked" ');
    }
}
