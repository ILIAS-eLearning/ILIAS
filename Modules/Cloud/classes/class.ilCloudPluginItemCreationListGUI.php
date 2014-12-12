<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("class.ilCloudPluginListGUI.php");

/**
 * Class ilCloudPluginItemCreationListGUI
 *
 * Class for the drawing of the list "add new item". Can be extended by the plugin if needed.
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 * @extends ilCloudPluginListGUI
 * @ingroup ModulesCloud
 */
class ilCloudPluginItemCreationListGUI extends ilCloudPluginListGUI
{
    /**
     * @var ilGroupedListGUI
     */
    protected $gl = null;

    /**
     * @param bool $showUpload
     * @param bool $showCreateFolders
     * @return string
     */
    public function getGroupedListItemsHTML($showUpload = false, $showCreateFolders = false)
    {
        $gl = $this->getGroupedListItems($showUpload, $showCreateFolders);
        return $gl->getHTML();

    }

    /**
     * @param bool $showUpload
     * @param bool $showCreateFolders
     * @return ilGroupedListGUI
     */
    public function getGroupedListItems($showUpload = false, $showCreateFolders = false){
        global $lng;

        include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
        $this->gl = new ilGroupedListGUI();

        $this->addItemsBefore();
        $this->gl->setAsDropDown(true);

        if ($showUpload)
        {
            include_once("Services/FileUpload/classes/class.ilFileUploadGUI.php");
            ilFileUploadGUI::initFileUpload();
            $icon_path = "./Modules/Cloud/templates/images/icon_file_s.png";
            $this->gl->addEntry(ilUtil::img($icon_path) . " " .$lng->txt("cld_add_file"), "javascript:il.CloudFileList.uploadFile();",
                "_top", "", "", "il_cld_add_file", $lng->txt("cld_info_add_file_to_current_directory"), "bottom center", "top center", false);
        }

        if ($showCreateFolders)
        {
            $icon_path = "./Modules/Cloud/templates/images/icon_folder_s.png";
            $this->gl->addEntry(ilUtil::img($icon_path)." ".$lng->txt("cld_add_folder"), "javascript:il.CloudFileList.createFolder();",
                "_top", "", "", "il_cld_add_file", $lng->txt("cld_info_add_folder_to_current_directory"), "bottom center", "top center", false);
        }

        $this->addItemsAfter();

        return $this->gl;
    }

    protected function addItemsBefore()
    {
    }

    protected function addItemsAfter()
    {
    }
}
?>