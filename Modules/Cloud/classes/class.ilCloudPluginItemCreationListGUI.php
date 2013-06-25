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
     * @var ilAdvancedSelectionListGUI
     */
    protected $selection_list = null;

    /**
     * @param bool $delete
     * @param ilCloudFileNode $node
     * @return string $html
     */
    public function getSelectionListItemsHTML($showUpload = false, $showCreateFolders = false)
    {
        global $tpl, $lng;
        include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $this->selection_list = new ilAdvancedSelectionListGUI();
        $this->selection_list->setListTitle($lng->txt("cld_add_new_item"));
        $this->selection_list->setId("item_creation");
        $this->selection_list->setHeaderIcon(ilUtil::getImagePath("cmd_add_s.png"));
        $this->selection_list->setItemLinkClass("xsmall");
        $this->selection_list->setUseImages(true);

        $this->addSelectionListItemsBefore();

        if ($showCreateFolders)
        {
            $this->selection_list->addItem($lng->txt("cld_add_folder"), "cld_item_creation_create_folder", "javascript:il.CloudFileList.createFolder();", "./Modules/Cloud/templates/images/icon_cld.png", "title1", "", "", false, "", $lng->txt("cld_info_add_folder_to_current_directory"));
        }

        if ($showUpload)
        {
            include_once("Services/FileUpload/classes/class.ilFileUploadGUI.php");
            ilFileUploadGUI::initFileUpload();
            $this->selection_list->addItem($lng->txt("cld_add_file"), "cld_item_creation_upload", "javascript:il.CloudFileList.uploadFile();", "./Modules/Cloud/templates/images/icon_file.png", "title2", "", "", false, "", $lng->txt("cld_info_add_file_to_current_directory"), "right center", "left center", false);
        }

        $this->addSelectionListItemsAfter();

        if($this->selection_list->getItems() != null)
        {
            return $this->selection_list->getHTML();
        }
        else
        {
            return "";
        }

    }

    protected function addSelectionListItemsBefore(){}
    protected function addSelectionListItemsAfter(){}
}

?>