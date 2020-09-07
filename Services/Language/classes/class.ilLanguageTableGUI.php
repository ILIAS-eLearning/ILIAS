<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup Services
*/
class ilLanguageTableGUI extends ilTable2GUI
{
    /** @var ilObjLanguageFolder */
    protected $folder;
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_folder)
    {
        global $DIC;
        $ilAccess = $DIC->access();

        $this->folder = $a_folder;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setLimit(9999);

        if ($ilAccess->checkAccess('write', '', $this->folder->getRefId())) {
            $this->addColumn("", "", "1", 1);
        }
        $this->addColumn($this->lng->txt("language"));
        $this->addColumn($this->lng->txt("status"));
        $this->addColumn($this->lng->txt("users"));
        $this->addColumn($this->lng->txt("last_refresh"));
        $this->addColumn($this->lng->txt("last_change"));

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.lang_list_row_extended.html", "Services/Language");
        $this->disable("footer");
        $this->setEnableTitle(true);

        if ($ilAccess->checkAccess('write', '', $this->folder->getRefId())) {
            $this->setSelectAllCheckbox("id[]");
            $this->addMultiCommand("confirmRefreshSelected", $this->lng->txt("refresh"));
            $this->addMultiCommand("install", $this->lng->txt("install"));
            $this->addMultiCommand("installLocal", $this->lng->txt("install_local"));
            $this->addMultiCommand("confirmUninstall", $this->lng->txt("uninstall"));
            $this->addMultiCommand("confirmUninstallChanges", $this->lng->txt("lang_uninstall_changes"));
            $this->addMultiCommand("setSystemLanguage", $this->lng->txt("setSystemLanguage"));
            $this->addMultiCommand("setUserLanguage", $this->lng->txt("setUserLanguage"));
        }

        $this->getItems();
    }
    
    /**
    * Get language data
    */
    public function getItems()
    {
        $languages = $this->folder->getLanguages();
        $data = array();
        foreach ($languages as $k => $l) {
            $data[] = array_merge($l, array("key" => $k));
        }

        // sort alphabetically but shoe installed languages first
        $data = ilUtil::stableSortArray($data, 'name', 'asc', false);
        $data = ilUtil::stableSortArray($data, 'desc', 'asc', false);

        $this->setData($data);
    }
    
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        global $DIC;
        $ilSetting = $DIC->settings();
        $ilAccess = $DIC->access();

        // set status info (in use or systemlanguage)
        if ($a_set["status"]) {
            $status = "<span class=\"small\"> (" . $this->lng->txt($a_set["status"]) . ")</span>";
        }

        // set remark color
        switch ($a_set["info"]) {
            case "file_not_found":
                $remark = "<span class=\"smallred\"> " . $this->lng->txt($a_set["info"]) . "</span>";
                break;
            case "new_language":
                //$remark = "<span class=\"smallgreen\"> ".$lng->txt($a_set["info"])."</span>";
                break;
            default:
                $remark = "";
                break;
        }
        
        // show page translation
        if ($ilSetting->get("lang_translate_" . $a_set['key'], false)) {
            $remark .= $remark ? '<br />' : '';
            $remark .= "<span class=\"smallgreen\"> " . $this->lng->txt('language_translation_enabled') . "</span>";
        }

        if ($a_set["desc"] != "not_installed") {
            $this->tpl->setVariable(
                "LAST_REFRESH",
                ilDatePresentation::formatDate(new ilDateTime($a_set["last_update"], IL_CAL_DATETIME))
            );

            $last_change = ilObjLanguage::_getLastLocalChange($a_set['key']);
            $this->tpl->setVariable(
                "LAST_CHANGE",
                ilDatePresentation::formatDate(new ilDateTime($last_change, IL_CAL_DATETIME))
            );
        }

        $this->tpl->setVariable("NR_OF_USERS", ilObjLanguage::countUsers($a_set["key"]));

        // make language name clickable
        if ($ilAccess->checkAccess("write", "", $this->folder->getRefId())) {
            if (substr($a_set["description"], 0, 9) == "installed") {
                $this->ctrl->setParameterByClass("ilobjlanguageextgui", "obj_id", $a_set["obj_id"]);
                $url = $this->ctrl->getLinkTargetByClass("ilobjlanguageextgui", "");
                $a_set["name"] = '<a href="' . $url . '">' . $a_set["name"] . '</a>';
            }
        }

        $this->tpl->setVariable("VAL_LANGUAGE", $a_set["name"] . $status);
        $this->tpl->setVariable("VAL_STATUS", $this->lng->txt($a_set["desc"]) . "<br/>" . $remark);

        if ($ilAccess->checkAccess('write', '', $this->folder->getRefId())) {
            $this->tpl->setVariable("OBJ_ID", $a_set["obj_id"]);
        }
    }
}
