<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\HTTP\Services;

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

    protected ilSetting $folderSettings;
    protected Services $http;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct($a_data, int $a_id, bool $a_call_by_reference)
    {
        global $DIC;
        $this->type = "facs";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->folderSettings = new ilSetting('fold');
        $this->http = $DIC->http();
    }


    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand() : void
    {
        $this->lng->loadLanguageModule("file");

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->ilias->raiseError(
                $this->lng->txt('no_permission'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = self::CMD_EDIT_SETTINGS;
                }

                $this->$cmd();
                break;
        }
    }


    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs() : void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'file_objects',
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT_SETTINGS),
                array(self::CMD_EDIT_SETTINGS, "view")
            );
        }
        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget("perm_settings", $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"), array(), 'ilpermissiongui');
        }
    }


    protected function addFileObjectsSubTabs() : void
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
    protected function initSettingsForm() : \ilPropertyFormGUI
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

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
    public function editSettings(ilPropertyFormGUI $a_form = null) : void
    {
        global $DIC, $ilErr;

        $this->tabs_gui->setTabActive('file_objects');
        $this->addFileObjectsSubTabs();
        $this->tabs_gui->setSubTabActive('settings');

        if (!$DIC->rbac()->system()->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($DIC->language()->txt("no_permission"), $ilErr->WARNING);
        }

        if ($a_form === null) {
            $a_form = $this->initSettingsForm();
        }

        $DIC->ui()->mainTemplate()->setContent($a_form->getHTML());
    }


    /**
     * Save settings
     */
    public function saveSettings() : void
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $DIC->language()->txt("no_permission"), true);
            $DIC->ctrl()->redirect($this, self::CMD_EDIT_SETTINGS);
        }

        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            // TODO switch to new forms
            $post = (array) $this->http->request()->getParsedBody();
            $this->object->setDownloadWithUploadedFilename(
                ilUtil::stripSlashes($post['download_with_uploaded_filename'] ?? '')
            );
            $this->object->setInlineFileExtensions(
                ilUtil::stripSlashes($post['inline_file_extensions'] ?? '')
            );
            $this->object->update();
            $this->folderSettings->set("bgtask_download_limit", (int) $post["bg_limit"]);
            $enable_preview = (int) ($post["enable_preview"] ?? 0);
            ilPreviewSettings::setPreviewEnabled($enable_preview === 1);
            ilPreviewSettings::setMaximumPreviews($post["max_previews_per_object"]);

            $this->tpl->setOnScreenMessage('success', $DIC->language()->txt('settings_saved'), true);
            $DIC->ctrl()->redirect($this, self::CMD_EDIT_SETTINGS);
        }

        $form->setValuesByPost();
        $this->editSettings($form);
    }


    protected function showPreviewRenderers() : void
    {
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
            $this->tpl->setOnScreenMessage('info', $lng->txt("ghostscript_not_configured"));
        }

        $factory = new ilRendererFactory();
        $renderers = $factory->getRenderers();
        $array_wrapper = array_map(function (ilFilePreviewRenderer $renderer) : array {
            return [
                'name' => $renderer->getName(),
                'is_plugin' => $renderer->isPlugin(),
                'supported_repo_types' => $renderer->getSupportedRepositoryTypes(),
                'supported_file_formats' => $renderer->getSupportedFileFormats(),
                'object' => $renderer
            ];
        }, $renderers);


        $table = new ilRendererTableGUI($this, self::CMD_SHOW_PREVIEW_RENDERERS);
        $table->setMaxCount(count($renderers));
        $table->setData($array_wrapper);

        // set content
        $tpl->setContent($table->getHTML());
    }


    /**
     * called by prepare output
     */
    protected function setTitleAndDescription() : void
    {
        parent::setTitleAndDescription();
        $this->tpl->setDescription($this->object->getDescription());
    }
}
