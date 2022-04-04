<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('class.ilCloudPluginListGUI.php');
require_once('./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php');
require_once('./Services/FileUpload/classes/class.ilFileUploadGUI.php');
require_once('./Modules/Cloud/classes/class.ilCloudGroupedListGUI.php');

/**
 * Class ilCloudPluginItemCreationListGUI
 * Class for the drawing of the list 'add new item'. Can be extended by the plugin if needed.
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @author  Martin Studer martin@fluxlabs.ch
 */
class ilCloudPluginItemCreationListGUI extends ilCloudPluginListGUI
{
    protected ?ilGroupedListGUI $gl = null;

    public function getGroupedListItemsHTML(bool $showUpload = false, bool $showCreateFolders = false): string
    {
        $gl = $this->getGroupedListItems($showUpload, $showCreateFolders);

        return $gl->getHTML();
    }

    public function getGroupedListItems(bool $show_upload = false, bool $show_create_folders = false): ilCloudGroupedListGUI
    {
        global $DIC;
        $lng = $DIC['lng'];

        $this->gl = new ilCloudGroupedListGUI();

        $this->addItemsBefore();
        $this->gl->setAsDropDown(true);

        if ($show_upload) {
            ilFileUploadGUI::initFileUpload();
            $icon_path = ilUtil::getImagePath('icon_dcl_file.svg');
            $img = ilUtil::img($icon_path);
            $a_ttip = $lng->txt('cld_info_add_file_to_current_directory');
            $this->gl->addEntry($img . ' '
                . $lng->txt('cld_add_file'), '#', '_top', 'javascript:il.CloudFileList.uploadFile();', '',
                'il_cld_add_file', $a_ttip, 'bottom center', 'top center', false);
        }

        if ($show_create_folders) {
            $icon_path = ilUtil::getImagePath('icon_dcl_fold.svg');
            $img1 = ilUtil::img($icon_path);
            $a_ttip1 = $lng->txt('cld_info_add_folder_to_current_directory');
            $this->gl->addEntry($img1 . ' '
                . $lng->txt('cld_add_folder'), '#', '_top', 'javascript:il.CloudFileList.createFolder();', '',
                'il_cld_add_file', $a_ttip1, 'bottom center', 'top center', false);
        }

        $this->addItemsAfter();

        return $this->gl;
    }

    protected function addItemsBefore(): void
    {
    }

    protected function addItemsAfter(): void
    {
    }
}
