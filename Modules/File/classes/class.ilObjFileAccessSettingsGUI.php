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
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\File\Icon\ilObjFileIconsOverviewGUI;
use ILIAS\Modules\File\Preview\Settings;
use ILIAS\Modules\File\Settings\General;

/**
 * Class ilObjFileAccessSettingsGUI
 *
 * @author       Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
 *
 * @version      $Id$
 *
 * @ilCtrl_Calls ilObjFileAccessSettingsGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjFileAccessSettingsGUI: ILIAS\File\Icon\ilObjFileIconsOverviewGUI
 *
 * @extends      ilObjectGUI
 */
class ilObjFileAccessSettingsGUI extends ilObjectGUI
{
    public const CMD_EDIT_SETTINGS = 'editSettings';
    public const CMD_SHOW_PREVIEW_RENDERERS = 'showPreviewRenderers';
    public const SUBTAB_SUFFIX_SPECIFIC_ICONS = 'suffix_specific_icons';
    public const CMD_SAVE_SETTINGS = 'saveSettings';
    public const CMD_VIEW = 'view';
    private ilLanguage $language;
    private \ILIAS\Modules\File\Preview\Form $preview_settings;
    private \ILIAS\Modules\File\Settings\Form $file_object_settings;
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $ui_renderer;
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
        $this->preview_settings = new ILIAS\Modules\File\Preview\Form(new Settings());
        $this->file_object_settings = new \ILIAS\Modules\File\Settings\Form(new General()) ;
        $this->http = $DIC->http();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->language = $DIC->language();
    }

    protected function checkAccess(string $permission): void
    {
        if (!$this->access->checkAccess($permission, '', $this->object->getRefId())) {
            $this->ilias->raiseError(
                $this->lng->txt('no_permission'),
                $this->ilias->error_obj->MESSAGE
            );
        }
    }

    private function buildForm(): \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $form = $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getLinkTarget($this, self::CMD_SAVE_SETTINGS),
            [
                $this->file_object_settings->asFormSection(),
                $this->preview_settings->asFormSection(),
            ]
        );
        return $form;
    }

    public function executeCommand(): void
    {
        $this->lng->loadLanguageModule("file");

        $this->prepareOutput();

        $this->checkAccess('read');

        switch ($this->ctrl->getNextClass($this)) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            case strtolower(ilObjFileIconsOverviewGUI::class):
                $this->tabs_gui->setTabActive('file_objects');
                $this->addFileObjectsSubTabs();
                $this->tabs_gui->setSubTabActive(self::SUBTAB_SUFFIX_SPECIFIC_ICONS);
                $icon_overview = new ilObjFileIconsOverviewGUI();
                $this->ctrl->forwardCommand($icon_overview);
                break;
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_EDIT_SETTINGS);
                $this->tabs_gui->setTabActive('file_objects');
                switch ($cmd) {
                    case self::CMD_VIEW:
                    case self::CMD_EDIT_SETTINGS:
                        $this->checkAccess('read');
                        $this->editSettings();
                        break;
                    case self::CMD_SAVE_SETTINGS:
                        $this->checkAccess('write');
                        $this->saveSettings();
                        break;
                    default:
                        throw new ilException("ilObjFileAccessSettingsGUI: Command not found: $cmd");
                }
                break;
        }
    }

    public function getAdminTabs(): void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'file_objects',
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT_SETTINGS),
                [self::CMD_EDIT_SETTINGS, self::CMD_VIEW]
            );
        }
        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                [],
                'ilpermissiongui'
            );
        }
    }

    protected function editSettings(): void
    {
        $form = $this->buildForm();
        $this->tpl->setContent($this->ui_renderer->render($this->buildForm()));
    }

    protected function saveSettings(): void
    {
        $form = $this->buildForm();
        $form = $form->withRequest($this->http->request());

        if ($form->getData() === null) {
            $this->tpl->setContent($this->ui_renderer->render($form));
            return;
        }

        $this->tpl->setOnScreenMessage(
            'success',
            $this->language->txt('settings_saved'),
            true
        );
        $this->ctrl->redirect($this, self::CMD_EDIT_SETTINGS);
    }
}
