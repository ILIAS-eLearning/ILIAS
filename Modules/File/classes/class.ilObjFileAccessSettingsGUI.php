<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
 * Class ilObjFileAccessSettingsGUI
 *
 * @author       Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
 *
 * @version      $Id$
 *
 * @ilCtrl_Calls ilObjFileAccessSettingsGUI: ilPermissionGUI
 *
 * @extends      ilObjectGUI
 */
class ilObjFileAccessSettingsGUI extends ilObjectGUI
{
    const CMD_EDIT_SETTINGS = 'editSettings';
    const CMD_SHOW_PREVIEW_RENDERERS = 'showPreviewRenderers';

    /**
     * @var \ilSetting
     */
    protected $folderSettings;


    /**
     * Constructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        $this->type = "facs";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->folderSettings = new ilSetting('fold');
    }


    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilias = $DIC['ilias'];
        $lng = $DIC['lng'];

        $lng->loadLanguageModule("file");

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$ilAccess->checkAccess('read', '', $this->object->getRefId())) {
            $ilias->raiseError($lng->txt('no_permission'), $ilias->error_obj->MESSAGE);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;
            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = self::CMD_EDIT_SETTINGS;
                }

                $this->$cmd();
                break;
        }

        return true;
    }


    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        $GLOBALS['DIC']['lng']->loadLanguageModule('fm');

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'file_objects',
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT_SETTINGS),
                array(self::CMD_EDIT_SETTINGS, "view")
            );
        }
        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget("perm_settings", $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"), array(), 'ilpermissiongui');
        }
    }


    protected function addFileObjectsSubTabs()
    {
        $this->tabs_gui->addSubTabTarget(
            "settings",
            $this->ctrl->getLinkTarget($this, self::CMD_EDIT_SETTINGS),
            array(self::CMD_EDIT_SETTINGS, "view")
        );
        $this->tabs_gui->addSubTabTarget(
            "preview_renderers",
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW_PREVIEW_RENDERERS),
            array(self::CMD_SHOW_PREVIEW_RENDERERS, "view")
        );
    }


    /**
     * Edit settings.
     */
    protected function initSettingsForm()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        require_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
        require_once("./Services/Form/classes/class.ilRadioGroupInputGUI.php");
        require_once("./Services/Form/classes/class.ilRadioOption.php");
        require_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("settings"));

        // Backwards compatibility with ILIAS 3.9: Use the name of the
        // uploaded file as the filename for the downloaded file instead
        // of the title of the file object.
        $dl_prop = new ilCheckboxInputGUI($lng->txt("download_with_uploaded_filename"), "download_with_uploaded_filename");
        $dl_prop->setValue('1');
        // default value should reflect previous behaviour (-> 0)
        $dl_prop->setChecked($this->object->isDownloadWithUploadedFilename() == 1);
        $dl_prop->setInfo($lng->txt('download_with_uploaded_filename_info'));
        $form->addItem($dl_prop);

        // download limit
        $lng->loadLanguageModule("bgtask");
        $dl_prop = new ilNumberInputGUI($lng->txt("bgtask_setting_limit"), "bg_limit");
        $dl_prop->setInfo($lng->txt("bgtask_setting_limit_info"));
        $dl_prop->setRequired(true);
        $dl_prop->setSize(10);
        $dl_prop->setMinValue(1);
        $dl_prop->setSuffix($lng->txt("lang_size_mb"));
        $dl_prop->setValue($this->folderSettings->get("bgtask_download_limit", null));
        $form->addItem($dl_prop);

        // Inline file extensions
        $tai_prop = new ilTextAreaInputGUI($lng->txt('inline_file_extensions'), 'inline_file_extensions');
        $tai_prop->setValue($this->object->getInlineFileExtensions());
        $tai_prop->setInfo($lng->txt('inline_file_extensions_info'));
        $tai_prop->setCols(80);
        $tai_prop->setRows(5);
        $form->addItem($tai_prop);

        require_once("Services/Preview/classes/class.ilPreviewSettings.php");

        // enable preview
        $chk_prop = new ilCheckboxInputGUI($lng->txt("enable_preview"), "enable_preview");
        $chk_prop->setValue('1');
        $chk_prop->setChecked(ilPreviewSettings::isPreviewEnabled());
        $chk_prop->setInfo($lng->txt('enable_preview_info'));
        $form->addItem($chk_prop);

        // max preview images
        $num_prop = new ilNumberInputGUI($lng->txt("max_previews_per_object"), "max_previews_per_object");
        $num_prop->setDecimals(0);
        $num_prop->setMinValue(1);
        $num_prop->setMinvalueShouldBeGreater(false);
        $num_prop->setMaxValue(ilPreviewSettings::MAX_PREVIEWS_MAX);
        $num_prop->setMaxvalueShouldBeLess(false);
        $num_prop->setMaxLength(5);
        $num_prop->setSize(10);
        $num_prop->setValue(ilPreviewSettings::getMaximumPreviews());
        $num_prop->setInfo($lng->txt('max_previews_per_object_info'));
        $form->addItem($num_prop);

        // command buttons
        $form->addCommandButton('saveSettings', $lng->txt('save'));
        $form->addCommandButton('view', $lng->txt('cancel'));

        return $form;
    }


    /**
     * Edit settings.
     */
    public function editSettings(ilPropertyFormGUI $a_form = null)
    {
        global $DIC, $ilErr;

        $this->tabs_gui->setTabActive('file_objects');
        $this->addFileObjectsSubTabs();
        $this->tabs_gui->setSubTabActive('settings');

        if (!$DIC->rbac()->system()->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($DIC->language()->txt("no_permission"), $ilErr->WARNING);
        }

        if (!$a_form) {
            $a_form = $this->initSettingsForm();
        }

        $DIC->ui()->mainTemplate()->setContent($a_form->getHTML());
    }


    /**
     * Save settings
     */
    public function saveSettings()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            ilUtil::sendFailure($DIC->language()->txt("no_permission"), true);
            $DIC->ctrl()->redirect($this, self::CMD_EDIT_SETTINGS);
        }

        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $this->object->setDownloadWithUploadedFilename(ilUtil::stripSlashes($_POST['download_with_uploaded_filename']));
            $this->object->setInlineFileExtensions(ilUtil::stripSlashes($_POST['inline_file_extensions']));
            $this->object->update();
            $this->folderSettings->set("bgtask_download_limit", (int) $_POST["bg_limit"]);

            require_once("Services/Preview/classes/class.ilPreviewSettings.php");
            ilPreviewSettings::setPreviewEnabled($_POST["enable_preview"] == 1);
            ilPreviewSettings::setMaximumPreviews($_POST["max_previews_per_object"]);

            ilUtil::sendSuccess($DIC->language()->txt('settings_saved'), true);
            $DIC->ctrl()->redirect($this, self::CMD_EDIT_SETTINGS);
        }

        $form->setValuesByPost();
        $this->editSettings($form);
    }


    protected function showPreviewRenderers() {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];

        $this->tabs_gui->setTabActive('file_objects');
        $this->addFileObjectsSubTabs();
        $this->tabs_gui->setSubTabActive('preview_renderers');

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($lng->txt("no_permission"), $ilErr->WARNING);
        }

        // set warning if ghostscript not installed
        if (!ilGhostscriptRenderer::isGhostscriptInstalled()) {
            ilUtil::sendInfo($lng->txt("ghostscript_not_configured"));
        }

        // build renderer HTML
        require_once("Services/Preview/classes/class.ilRendererFactory.php");
        require_once("Services/Preview/classes/class.ilRendererTableGUI.php");

        $renderers = ilRendererFactory::getRenderers();

        $table = new ilRendererTableGUI($this, array(self::CMD_SHOW_PREVIEW_RENDERERS, "view"));
        $table->setMaxCount(sizeof($renderers));
        $table->setData($renderers);

        // set content
        $tpl->setContent($table->getHTML());
    }


    /**
     * called by prepare output
     */
    public function setTitleAndDescription()
    {
        parent::setTitleAndDescription();
        $this->tpl->setDescription($this->object->getDescription());
    }
}
