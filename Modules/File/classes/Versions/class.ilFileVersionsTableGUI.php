<?php

use ILIAS\Data\DataSize;
use ILIAS\DI\Container;
use ILIAS\Filesystem\Util\LegacyPathHelper;

/**
 * Class ilFileVersionsTableGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileVersionsTableGUI extends ilTable2GUI
{

    /**
     * @var Container
     */
    private $dic;
    /**
     * @var int
     */
    private $max_version;
    /**
     * @var int
     */
    private $current_version;
    /**
     * @var ilObjFile
     */
    private $file;


    /**
     * ilFileVersionsTableGUI constructor.
     *
     * @param ilFileVersionsGUI $calling_gui_class
     * @param string            $a_parent_cmd
     */
    public function __construct(ilFileVersionsGUI $calling_gui_class, $a_parent_cmd = ilFileVersionsGUI::CMD_DEFAULT)
    {
        global $DIC;
        $this->dic = $DIC;

        $this->setId(self::class);
        parent::__construct($calling_gui_class, $a_parent_cmd, "");
        $this->file = $calling_gui_class->getFile();
        $this->current_version = (int) $this->file->getVersion();
        $this->max_version = (int) $this->file->getMaxVersion();

        // General
        $this->setPrefix("versions");
        $this->dic->language()->loadLanguageModule('file');

        // Appearance
        $this->setRowTemplate("tpl.file_version_row.html", "Modules/File");
        $this->setLimit(9999);
        $this->setEnableHeader(true);
        $this->disable("footer");
        $this->setTitle($this->dic->language()->txt("versions"));

        // Form
        $this->setFormAction($this->dic->ctrl()->getFormAction($calling_gui_class));
        $this->setSelectAllCheckbox("hist_id[]");
        $this->addColumn("", "", "1", true);
        $this->addMultiCommand(ilFileVersionsGUI::CMD_DELETE_VERSIONS, $this->dic->language()->txt("delete"));
        $this->addMultiCommand(ilFileVersionsGUI::CMD_ROLLBACK_VERSION, $this->dic->language()->txt("file_rollback"));

        // Columns
        $this->addColumn($this->dic->language()->txt("version"), "", "1");
        $this->addColumn($this->dic->language()->txt("date"));
        $this->addColumn($this->dic->language()->txt("file_uploaded_by"));
        $this->addColumn($this->dic->language()->txt("filename"));
        $this->addColumn($this->dic->language()->txt("filesize"), "", "", false, "ilRight");
        $this->addColumn($this->dic->language()->txt("type"));
        $this->addColumn($this->dic->language()->txt("action"));
        $this->addColumn("", "", "1");

        $this->initData();
    }


    private function initData()
    {
        $versions = $this->file->getVersions();
        $this->setData($versions);
        $this->setMaxCount(is_array($versions) ? count($versions) : 0);
    }


    protected function fillRow($a_set)
    {
        $hist_id = $a_set["hist_entry_id"];

        // split params
        $filename = $a_set["filename"];
        $version = $a_set["version"];
        $rollback_version = $a_set["rollback_version"];
        $rollback_user_id = $a_set["rollback_user_id"];

        // get user name
        $name = ilObjUser::_lookupName($a_set["user_id"]);
        $username = trim($name["title"] . " " . $name["firstname"] . " " . $name["lastname"]);

        // get file size
        $directory = LegacyPathHelper::createRelativePath($this->file->getDirectory($version));
        $filepath = ilFileUtils::getValidFilename(rtrim($directory, "/") . "/" . $filename); // TODO remove after migration to filesystem
        $filesize = 0;
        if ($this->dic->filesystem()->storage()->has($filepath)) {
            $size = $filesize = $this->dic->filesystem()->storage()->getSize($filepath, DataSize::Byte);
            $filesize = $size->getSize();
        }

        // get action text
        $action = $this->dic->language()->txt("file_version_" . $a_set["action"]); // create, replace, new_version, rollback
        if ($a_set["action"] == "rollback") {
            $name = ilObjUser::_lookupName($rollback_user_id);
            $rollback_username = trim($name["title"] . " " . $name["firstname"] . " " . $name["lastname"]);
            $action = sprintf($action, $rollback_version, $rollback_username);
        }

        // get download link
        $this->dic->ctrl()->setParameter($this->parent_obj, ilFileVersionsGUI::HIST_ID, $hist_id);
        $link = $this->dic->ctrl()->getLinkTarget($this->parent_obj, ilFileVersionsGUI::CMD_DOWNLOAD_VERSION);

        // build actions
        $actions = new ilAdvancedSelectionListGUI();
        $actions->setId($hist_id);
        $actions->setListTitle($this->dic->language()->txt("actions"));
        $actions->addItem($this->dic->language()->txt("delete"), "", $this->dic->ctrl()->getLinkTarget($this->parent_obj, ilFileVersionsGUI::CMD_DELETE_VERSIONS));
        if ($this->current_version !== (int) $version) {
            $actions->addItem($this->dic->language()->txt("file_rollback"), "", $this->dic->ctrl()->getLinkTarget($this->parent_obj, ilFileVersionsGUI::CMD_ROLLBACK_VERSION));
        }

        // reset history parameter
        $this->dic->ctrl()->setParameter($this->parent_obj, ilFileVersionsGUI::HIST_ID, "");

        // fill template
        $this->tpl->setVariable("TXT_VERSION", $version);
        $this->tpl->setVariable("TXT_DATE", ilDatePresentation::formatDate(new ilDateTime($a_set['date'], IL_CAL_DATETIME)));
        $this->tpl->setVariable("TXT_UPLOADED_BY", $username);
        $this->tpl->setVariable("DL_LINK", $link);
        $this->tpl->setVariable("TXT_FILENAME", $filename);
        $this->tpl->setVariable("TXT_FILESIZE", ilUtil::formatSize($filesize));

        // columns depending on confirmation

        $this->tpl->setCurrentBlock("version_selection");
        $this->tpl->setVariable("OBJ_ID", $hist_id);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("version_txt_actions");
        $this->tpl->setVariable("TXT_ACTION", $action);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("version_actions");
        $this->tpl->setVariable("ACTIONS", $actions->getHTML());
        $this->tpl->parseCurrentBlock();
    }
}
