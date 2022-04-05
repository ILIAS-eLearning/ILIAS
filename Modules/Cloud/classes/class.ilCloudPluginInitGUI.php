<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("class.ilCloudPluginGUI.php");

/**
 * Class ilCloudPluginInitGUI
 * GUI Class for initialization of the object. Can be extended if needed.
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @author  Martin Studer martin@fluxlabs.ch
 * @version $Id$
 * @extends ilCloudPluginGUI
 * @ingroup ModulesCloud
 */
class ilCloudPluginInitGUI extends ilCloudPluginGUI
{
    protected ?ilObjCloudGUI $gui_class = null;
    protected bool $perm_upload_items = false;
    protected bool $perm_create_folders = false;
    protected bool $perm_delete_files = false;
    protected bool $perm_delete_folders = false;
    protected bool $perm_download = false;
    protected bool $perm_files_visible = false;
    protected bool $perm_folders_visible = false;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct($plugin_service_class)
    {
        parent::__construct($plugin_service_class);
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    public function setGuiClass(ilObjCloudGUI $gui_class) : void
    {
        $this->gui_class = $gui_class;
    }

    public function getGuiClass() : ilObjCloudGUI
    {
        return $this->gui_class;
    }

    public function setPermCreateFolders(bool $perm_create_folders) : void
    {
        $this->perm_create_folders = $perm_create_folders;
    }

    public function getPermCreateFolders() : bool
    {
        return $this->perm_create_folders;
    }

    public function setPermDeleteFiles(bool $perm_delete_files) : void
    {
        $this->perm_delete_files = $perm_delete_files;
    }

    public function getPermDeleteFiles() : bool
    {
        return $this->perm_delete_files;
    }

    public function setPermDeleteFolders(bool $perm_delete_folders) : void
    {
        $this->perm_delete_folders = $perm_delete_folders;
    }

    public function getPermDeleteFolders() : bool
    {
        return $this->perm_delete_folders;
    }

    public function setPermDownload(bool $perm_download) : void
    {
        $this->perm_download = $perm_download;
    }

    public function getPermDownload() : bool
    {
        return $this->perm_download;
    }

    public function setPermFilesVisible(bool $perm_files_visible) : void
    {
        $this->perm_files_visible = $perm_files_visible;
    }

    public function getPermFilesVisible() : bool
    {
        return $this->perm_files_visible;
    }

    public function setPermFoldersVisible(bool $perm_folders_visible) : void
    {
        $this->perm_folders_visible = $perm_folders_visible;
    }

    public function getPermFoldersVisible() : bool
    {
        return $this->perm_folders_visible;
    }

    public function setPermUploadItems(bool $perm_upload_items)
    {
        $this->perm_upload_items = $perm_upload_items;
    }

    public function getPermUploadItems() : bool
    {
        return $this->perm_upload_items;
    }

    public function setTplFileTree(\ilTemplate $tpl_file_tree) : void
    {
        $this->tpl_file_tree = $tpl_file_tree;
    }

    public function getTplFileTree() : \ilTemplate
    {
        return $this->tpl_file_tree;
    }

    protected ?ilTemplate $tpl_file_tree = null;

    public function initGUI(
        ilObjCloudGUI $gui_class,
        bool $perm_create_folder,
        bool $perm_upload_items,
        bool $perm_delete_files,
        bool $perm_delete_folders,
        bool $perm_download,
        bool $perm_files_visible,
        bool $perm_folders_visible
    ) : void {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];

        $ilTabs->activateTab("content");

        $this->setGuiClass($gui_class);
        $this->setPermUploadItems($perm_upload_items);
        $this->setPermCreateFolders($perm_create_folder);
        $this->setPermDeleteFiles($perm_delete_files);
        $this->setPermDeleteFolders($perm_delete_folders);
        $this->setPermDownload($perm_download);
        $this->setPermFilesVisible($perm_files_visible);
        $this->setPermFoldersVisible($perm_folders_visible);

        try {
            ilCloudConnector::checkServiceActive($this->getGUIClass()->getObject()->getServiceName());
            $this->beforeInitGUI();

            //if($this->getPluginObject()->getAsyncDrawing())
            {
                $tpl->addJavaScript("./Modules/Cloud/js/ilCloudFileList.js");
                $tpl->addJavaScript("./Modules/Cloud/js/jquery.address.js");
                $tpl->addJavascript("./Services/UIComponent/AdvancedSelectionList/js/AdvancedSelectionList.js");
                $tpl->addCss("./Modules/Cloud/templates/css/cloud.css");

                require_once("./Services/YUI/classes/class.ilYuiUtil.php");
                ilYuiUtil::initConnection();

                $this->tpl_file_tree = new ilTemplate("tpl.cloud_file_tree.html", true, true, "Modules/Cloud");

                $file_tree = new ilCloudFileTree(
                    $this->getGUIClass()->getObject()->getRootFolder(),
                    $this->getGUIClass()->getObject()->getRootId(),
                    $this->getGUIClass()->getObject()->getId(),
                    $this->getGUIClass()->getObject()->getServiceName()
                );
                $file_tree->storeFileTreeToSession();

                $this->addToolbar($file_tree->getRootNode());

                $this->tpl_file_tree->setVariable("ASYNC_GET_BLOCK",
                    json_encode($this->getGUIClass()->getCtrl()->getLinkTargetByClass("ilobjcloudgui", "asyncGetBlock",
                        true), JSON_THROW_ON_ERROR));
                $this->tpl_file_tree->setVariable("ASYNC_CREATE_FOLDER",
                    json_encode($this->getGUIClass()->getCtrl()->getLinkTargetByClass("ilcloudplugincreatefoldergui",
                        "asyncCreateFolder", true), JSON_THROW_ON_ERROR));
                $this->tpl_file_tree->setVariable("ASYNC_UPLOAD_FILE",
                    json_encode($this->getGUIClass()->getCtrl()->getLinkTargetByClass("ilcloudpluginuploadgui",
                        "asyncUploadFile", true), JSON_THROW_ON_ERROR));
                $this->tpl_file_tree->setVariable("ASYNC_DELETE_ITEM",
                    json_encode($this->getGUIClass()->getCtrl()->getLinkTargetByClass("ilcloudplugindeletegui",
                        "asyncDeleteItem", true), JSON_THROW_ON_ERROR));
                $this->tpl_file_tree->setVariable("ROOT_ID", json_encode($file_tree->getRootNode()->getId()));
                $this->tpl_file_tree->setVariable("ROOT_PATH", json_encode($file_tree->getRootNode()->getPath()));
                if (isset($_POST["path"])) {
                    $this->tpl_file_tree->setVariable("CURRENT_PATH", json_encode($_POST["path"]));
                    $file_tree->updateFileTree($_POST["path"]);
                    $node = $file_tree->getNodeFromPath($_POST["path"]);
                    $this->tpl_file_tree->setVariable("CURRENT_ID", json_encode($node->getId()));
                } else {
                    $this->tpl_file_tree->setVariable("CURRENT_PATH",
                        json_encode($file_tree->getRootNode()->getPath(), JSON_THROW_ON_ERROR));
                    $this->tpl_file_tree->setVariable("CURRENT_ID", json_encode($file_tree->getRootNode()->getID()));
                }
                $txt_max_file_size = $lng->txt("file_notice") . " "
                    . ilCloudConnector::getPluginClass($this->getGUIClass()->getObject()->getServiceName(),
                        $this->getGUIClass()->getObject()->getId())
                                      ->getMaxFileSize() . " MB";
                $this->tpl_file_tree->setVariable("MAX_FILE_SIZE", json_encode($txt_max_file_size));
                $this->beforeSetContent();
                $tpl->setContent($this->tpl_file_tree->get());
                $tpl->setPermanentLink("cld", $this->getGuiClass()->getObject()->getRefId(), "_path__endPath");
            }
            $this->afterInitGUI();
        } catch (Exception $e) {
            if ($e->getCode() == ilCloudException::AUTHENTICATION_FAILED) {
                $this->getGUIClass()->getObject()->setAuthComplete(false);
                $this->getGUIClass()->getObject()->update();
            }
            $this->main_tpl->setOnScreenMessage('failure', $e->getMessage());
        }
    }

    public function addToolbar(ilCloudFileNode $root_node) : void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilLog = $DIC['ilLog'];

        $create_list_gui = ilCloudConnector::getItemCreationListGUIClass($this->getService());

        $list_gui_html = $create_list_gui->getGroupedListItemsHTML($this->getPermUploadItems(),
            $this->getPermCreateFolders());
        if ($list_gui_html) {
            //toolbar
            $toolbar_locator = new ilLocatorGUI();
            $toolbar_locator->addItem($this->getGuiClass()->getObject()->getTitle(),
                ilCloudPluginFileTreeGUI::getLinkToFolder($root_node));
            $ilToolbar->setId('xcld_toolbar');
            $ilToolbar->addText("<div class='xcld_locator'>" . $toolbar_locator->getHtml() . "</div>");
            $ilToolbar->addSeparator();

            require_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
            $adv = new ilAdvancedSelectionListGUI();
            $adv->setListTitle($lng->txt("cld_add_new_item"));

            $ilCloudGroupedListGUI = $create_list_gui->getGroupedListItems($this->getPermUploadItems(),
                $this->getPermCreateFolders());

            if (is_null($ilCloudGroupedListGUI) === false && $ilCloudGroupedListGUI->hasItems() === true) {
                $adv->setGroupedList($ilCloudGroupedListGUI);
            }

            $adv->setStyle(ilAdvancedSelectionListGUI::STYLE_EMPH);
            $ilToolbar->addText($adv->getHTML());
        }
    }

    public function beforeInitGUI() : void
    {
    }

    public function beforeSetContent() : void
    {
    }

    public function afterInitGUI() : void
    {
    }
}
