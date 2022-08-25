<?php

declare(strict_types=1);

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

use ILIAS\Refinery\Factory;
use ILIAS\HTTP\Wrapper\WrapperFactory;

/**
 * Class ilObjFileServicesGUI
 * @author              Lukas Zehnder <lz@studer-raimann.ch>
 * @ilCtrl_IsCalledBy   ilObjFileServicesGUI: ilAdministrationGUI
 * @ilCtrl_Calls        ilObjFileServicesGUI: ilPermissionGUI
 */
class ilObjFileServicesGUI extends ilObject2GUI
{
    public const CMD_EDIT_SETTINGS = 'editSettings';
    protected ilTabsGUI $tabs;
    public ilLanguage $lng;
    public ilErrorHandling $error_handling;
    protected ilCtrl $ctrl;
    protected ilSetting $settings;
    public ilGlobalTemplateInterface $tpl;
    protected Factory $refinery;
    protected WrapperFactory $http;
    protected ilFileServicesSettings $file_service_settings;

    /**
     * Constructor
     * @access public
     */
    public function __construct(int $id = 0, int $id_type = self::REPOSITORY_NODE_ID, int $parent_node_id = 0)
    {
        global $DIC;

        $this->type = ilObjFileServices::TYPE_FILE_SERVICES;
        parent::__construct($id, $id_type, $parent_node_id);

        $this->tabs = $DIC['ilTabs'];
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('adn');
        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->tree = $DIC['tree'];
        $this->settings = $DIC['ilSetting'];
        $this->error_handling = $DIC["ilErr"];
        $this->http = $DIC->http()->wrapper();
        $this->ref_id = $this->http->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
        $this->refinery = $DIC->refinery();
        $this->file_service_settings = new ilFileServicesSettings($DIC->settings());
    }

    public function getType(): string
    {
        return ilObjFileServices::TYPE_FILE_SERVICES;
    }

    protected function checkPermissionOrFail(string $str): void
    {
        if (!$this->hasUserPermissionTo($str)) {
            $this->error_handling->raiseError(
                $this->lng->txt('no_permission'),
                $this->error->MESSAGE
            );
        }
    }

    protected function hasUserPermissionTo(string $str): bool
    {
        return $this->access->checkAccess($str, '', $this->ref_id);
    }

    /**
     * Execute command
     * @access public
     */
    public function executeCommand(): void
    {
        $this->lng->loadLanguageModule("fils");

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();
        $this->checkPermissionOrFail('read');

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = self::CMD_EDIT_SETTINGS;
                }
                $this->$cmd();
                break;
        }
    }

    /**
     * Get tabs
     */
    public function getAdminTabs(): void
    {
        if ($this->rbac_system->checkAccess(
            "visible,read",
            $this->object->getRefId()
        )
        ) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT_SETTINGS),
                [self::CMD_EDIT_SETTINGS, "view"]
            );
        }
        if ($this->rbac_system->checkAccess(
            'edit_permission',
            $this->object->getRefId()
        )
        ) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(ilPermissionGUI::class, "perm"),
                [],
                ilPermissionGUI::class
            );
        }
    }

    public function setTitleAndDescription(): void
    {
        parent::setTitleAndDescription();
        $this->tpl->setDescription($this->object->getDescription());
    }

    private function initSettingsForm(): ilPropertyFormGUI
    {
        $permission_to_write = $this->hasUserPermissionTo('write');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("settings"));

        // default positive list
        $ne = new ilNonEditableValueGUI($this->lng->txt("file_suffix_default_positive"), "");
        $ne->setValue(implode(", ", $this->file_service_settings->getDefaultWhitelist()));
        $ne->setInfo($this->lng->txt("file_suffix_default_positive_info"));
        $form->addItem($ne);

        // file suffix custom negative list
        $ta = new ilTextAreaInputGUI(
            $this->lng->txt(
                "file_suffix_custom_negative"
            ),
            "suffix_repl_additional"
        );
        $ta->setInfo($this->lng->txt("file_suffix_custom_negative_info"));
        $ta->setRows(5);
        $ta->setDisabled(!$permission_to_write);
        $form->addItem($ta);

        // file suffix custom positive list
        $ta = new ilTextAreaInputGUI(
            $this->lng->txt(
                "file_suffix_custom_positive"
            ),
            "suffix_custom_white_list"
        );
        $ta->setInfo($this->lng->txt("file_suffix_custom_positive_info"));
        $ta->setRows(5);
        $ta->setDisabled(!$permission_to_write);
        $form->addItem($ta);

        // resulting overall positive list
        $ne = new ilNonEditableValueGUI($this->lng->txt("file_suffix_overall_positive"), "");
        $ne->setValue(implode(", ", $this->file_service_settings->getWhiteListedSuffixes()));
        $ne->setInfo($this->lng->txt("file_suffix_overall_positive_info"));
        $form->addItem($ne);

        // explicit negative list
        $ta = new ilTextAreaInputGUI(
            $this->lng->txt("file_suffix_custom_expl_negative"),
            "suffix_custom_expl_black"
        );
        $ta->setInfo($this->lng->txt("file_suffix_custom_expl_negative_info"));
        $ta->setRows(5);
        $ta->setDisabled(!$permission_to_write);
        $form->addItem($ta);

        // command buttons
        if ($permission_to_write) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
            $form->addCommandButton('view', $this->lng->txt('cancel'));
        }

        return $form;
    }

    protected function editSettings(): void
    {
        $this->tabs_gui->setTabActive('settings');

        $this->checkPermissionOrFail("visible,read");

        // get form
        $form = $this->initSettingsForm();

        // set current values
        $val = [];
        $val["suffix_repl_additional"] = implode(", ", $this->file_service_settings->getWhiteListNegative());
        $val["suffix_custom_white_list"] = implode(", ", $this->file_service_settings->getWhiteListPositive());
        $val["suffix_custom_expl_black"] = implode(", ", $this->file_service_settings->getProhibited());
        $form->setValuesByArray($val);

        // set content
        $this->tpl->setContent($form->getHTML());
    }

    protected function saveSettings(): void
    {
        $this->checkPermissionOrFail("write");

        // get form
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $trafo = function (string $id): ?string {
                return $this->http->post()->has($id)
                    ? $this->http->post()->retrieve($id, $this->refinery->to()->string())
                    : null;
            };


            $this->settings->set("suffix_repl_additional", $trafo("suffix_repl_additional"));
            $this->settings->set("suffix_custom_white_list", $trafo("suffix_custom_white_list"));
            $this->settings->set("suffix_custom_expl_black", $trafo("suffix_custom_expl_black"));

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, self::CMD_EDIT_SETTINGS);
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
}
