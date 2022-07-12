<?php declare(strict_types=1);

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

use ILIAS\ContentPage\PageMetrics\Command\StorePageMetricsCommand;
use ILIAS\ContentPage\PageMetrics\PageMetricsService;
use ILIAS\ContentPage\PageMetrics\PageMetricsRepositoryImp;
use ILIAS\ContentPage\PageMetrics\Event\PageUpdatedEvent;
use ILIAS\DI\Container;
use ILIAS\HTTP\GlobalHttpState;

/**
 * Class ilObjContentPageGUI
 * @ilCtrl_isCalledBy ilObjContentPageGUI: ilRepositoryGUI
 * @ilCtrl_isCalledBy ilObjContentPageGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjContentPageGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjContentPageGUI: ilInfoScreenGUI
 * @ilCtrl_Calls      ilObjContentPageGUI: ilObjectCopyGUI
 * @ilCtrl_Calls      ilObjContentPageGUI: ilExportGUI
 * @ilCtrl_Calls      ilObjContentPageGUI: ilLearningProgressGUI
 * @ilCtrl_Calls      ilObjContentPageGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjContentPageGUI: ilContentPagePageGUI
 * @ilCtrl_Calls      ilObjContentPageGUI: ilObjectCustomIconConfigurationGUI
 * @ilCtrl_Calls      ilObjContentPageGUI: ilObjectContentStyleSettingsGUI
 * @ilCtrl_Calls      ilObjContentPageGUI: ilObjectTranslationGUI
 * @ilCtrl_Calls      ilObjContentPageGUI: ilPageMultiLangGUI
 */
class ilObjContentPageGUI extends ilObject2GUI implements ilContentPageObjectConstants, ilDesktopItemHandling
{
    protected GlobalHttpState $http;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    private ilNavigationHistory $navHistory;
    private Container $dic;
    private bool $infoScreenEnabled = false;
    private PageMetricsService $pageMetricsService;
    private ilHelpGUI $help;
    private \ILIAS\DI\UIServices $uiServices;

    public function __construct(int $a_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->dic = $DIC;
        $this->http = $this->dic->http();
        $this->settings = $this->dic->settings();
        $this->navHistory = $this->dic['ilNavigationHistory'];
        $this->help = $DIC['ilHelp'];
        $this->uiServices = $DIC->ui();

        $this->lng->loadLanguageModule('copa');
        $this->lng->loadLanguageModule('style');
        $this->lng->loadLanguageModule('content');
        $this->lng->loadLanguageModule('rep');

        if ($this->object instanceof ilObjContentPage) {
            $this->infoScreenEnabled = (bool) ilContainer::_lookupContainerSetting(
                $this->object->getId(),
                ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                '1'
            );
        }

        $this->pageMetricsService = new PageMetricsService(
            new PageMetricsRepositoryImp($DIC->database()),
            $DIC->refinery()
        );
        $cs = $DIC->contentStyle();
        $this->content_style_gui = $cs->gui();
        if (is_object($this->object)) {
            $this->content_style_domain = $cs->domain()->styleForRefId($this->object->getRefId());
        }
    }

    public static function _goto(string $target) : void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $targetAttributes = explode('_', $target);
        $refId = (int) $targetAttributes[0];

        if ($refId <= 0) {
            $DIC['ilErr']->raiseError($DIC->language()->txt('msg_no_perm_read'), $DIC['ilErr']->FATAL);
        }

        if ($DIC->access()->checkAccess('read', '', $refId)) {
            $DIC->ctrl()->setTargetScript('ilias.php');
            $DIC->ctrl()->setParameterByClass(self::class, 'ref_id', $refId);
            $DIC->ctrl()->redirectByClass([
                ilRepositoryGUI::class,
                self::class,
            ], self::UI_CMD_VIEW);
        } elseif ($DIC->access()->checkAccess('visible', '', $refId)) {
            ilObjectGUI::_gotoRepositoryNode($refId, 'infoScreen');
        } elseif ($DIC->access()->checkAccess('read', '', ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('info', sprintf(
                $DIC->language()->txt('msg_no_perm_read_item'),
                ilObject::_lookupTitle(ilObject::_lookupObjId($refId))
            ), true);

            $DIC->ctrl()->setTargetScript('ilias.php');
            $DIC->ctrl()->setParameterByClass(ilRepositoryGUI::class, 'ref_id', ROOT_FOLDER_ID);
            $DIC->ctrl()->redirectByClass(ilRepositoryGUI::class);
        }

        $DIC['ilErr']->raiseError($DIC->language()->txt('msg_no_perm_read'), $DIC['ilErr']->FATAL);
    }

    public function getType() : string
    {
        return self::OBJ_TYPE;
    }

    protected function setTabs() : void
    {
        $this->help->setScreenIdComponent($this->object->getType());

        if ($this->checkPermissionBool('read')) {
            $this->tabs_gui->addTab(
                self::UI_TAB_ID_CONTENT,
                $this->lng->txt('content'),
                $this->ctrl->getLinkTarget($this, self::UI_CMD_VIEW)
            );
        }

        if ($this->infoScreenEnabled && ($this->checkPermissionBool('visible') || $this->checkPermissionBool('read'))) {
            $this->tabs_gui->addTab(
                self::UI_TAB_ID_INFO,
                $this->lng->txt('info_short'),
                $this->ctrl->getLinkTargetByClass(ilInfoScreenGUI::class, 'showSummary')
            );
        }

        if ($this->checkPermissionBool('write')) {
            $this->tabs_gui->addTab(
                self::UI_TAB_ID_SETTINGS,
                $this->lng->txt('settings'),
                $this->ctrl->getLinkTarget($this, self::UI_CMD_EDIT)
            );
        }

        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $this->tabs_gui->addTab(
                self::UI_TAB_ID_LP,
                $this->lng->txt('learning_progress'),
                $this->ctrl->getLinkTargetByClass(ilLearningProgressGUI::class)
            );
        }

        if ($this->checkPermissionBool('write')) {
            $this->tabs_gui->addTab(
                self::UI_TAB_ID_EXPORT,
                $this->lng->txt('export'),
                $this->ctrl->getLinkTargetByClass(ilExportGUI::class)
            );
        }

        if ($this->checkPermissionBool('edit_permission')) {
            $this->tabs_gui->addTab(
                self::UI_TAB_ID_PERMISSIONS,
                $this->lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass(ilPermissionGUI::class, 'perm')
            );
        }
    }

    public function executeCommand() : void
    {
        $nextClass = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd(self::UI_CMD_VIEW);

        $this->addToNavigationHistory();

        if (
            strtolower($nextClass) !== strtolower(ilObjectContentStyleSettingsGUI::class) &&
            (strtolower($cmd) !== strtolower(self::UI_CMD_EDIT) || strtolower($nextClass) !== strtolower(ilContentPagePageGUI::class))
        ) {
            $this->renderHeaderActions();
        }

        switch (strtolower($nextClass)) {
            case strtolower(ilObjectTranslationGUI::class):
                $this->checkPermission('write');

                $this->prepareOutput();
                $this->tabs_gui->activateTab(self::UI_TAB_ID_SETTINGS);
                $this->setSettingsSubTabs(self::UI_TAB_ID_I18N);

                $transgui = new ilObjectTranslationGUI($this);
                $this->ctrl->forwardCommand($transgui);
                break;

            case strtolower(ilObjectContentStyleSettingsGUI::class):
                $this->checkPermission("write");
                $this->prepareOutput();
                $this->setLocator();
                $this->tabs_gui->activateTab(self::UI_TAB_ID_SETTINGS);
                $this->setSettingsSubTabs(self::UI_TAB_ID_STYLE);
                $settings_gui = $this->content_style_gui
                    ->objectSettingsGUIForRefId(
                        null,
                        $this->object->getRefId()
                    );
                $this->ctrl->forwardCommand($settings_gui);
                break;

            case strtolower(ilContentPagePageGUI::class):
                $isMediaRequest = in_array(strtolower($cmd), array_map('strtolower', [
                    self::UI_CMD_COPAGE_DOWNLOAD_FILE,
                    self::UI_CMD_COPAGE_DISPLAY_FULLSCREEN,
                    self::UI_CMD_COPAGE_DOWNLOAD_PARAGRAPH,
                ]), true);
                if ($isMediaRequest) {
                    if (!$this->checkPermissionBool('read')) {
                        $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                    }
                } elseif (!$this->checkPermissionBool('write') || $this->user->isAnonymous()) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }

                $this->prepareOutput();

                $this->content_style_gui->addCss($this->tpl, $this->object->getRefId());
                $this->tpl->setCurrentBlock('SyntaxStyle');
                $this->tpl->setVariable('LOCATION_SYNTAX_STYLESHEET', ilObjStyleSheet::getSyntaxStylePath());
                $this->tpl->parseCurrentBlock();

                /** @var ilObjContentPage $obj */
                $obj = $this->object;
                $forwarder = new ilContentPagePageCommandForwarder(
                    $this->http,
                    $this->ctrl,
                    $this->tabs_gui,
                    $this->lng,
                    $obj,
                    $this->user,
                    $this->refinery,
                    $this->content_style_domain
                );
                $forwarder->setIsMediaRequest($isMediaRequest);

                $forwarder->addUpdateListener(function (PageUpdatedEvent $event) : void {
                    $this->pageMetricsService->store(
                        new StorePageMetricsCommand(
                            $this->object->getId(),
                            $event->page()->getLanguage()
                        )
                    );
                });

                $pageContent = $forwarder->forward();
                if ($pageContent !== '') {
                    $this->tpl->setContent($pageContent);
                }
                break;

            case strtolower(ilInfoScreenGUI::class):
                if (!$this->infoScreenEnabled) {
                    return;
                }
                $this->prepareOutput();

                $this->infoScreenForward();
                break;

            case strtolower(ilCommonActionDispatcherGUI::class):
                $this->prepareOutput();
                $this->ctrl->forwardCommand(ilCommonActionDispatcherGUI::getInstanceFromAjaxCall());
                break;

            case strtolower(ilPermissionGUI::class):
                $this->checkPermission('edit_permission');

                $this->prepareOutput();
                $this->tabs_gui->activateTab(self::UI_TAB_ID_PERMISSIONS);

                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                break;

            case strtolower(ilLearningProgressGUI::class):
                if (!ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }

                $this->prepareOutput();
                $this->tabs_gui->activateTab(self::UI_TAB_ID_LP);

                $usr_id = 0;
                if ($this->http->wrapper()->query()->has('user_id')) {
                    $usr_id = $this->http->wrapper()->query()->retrieve(
                        'user_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }

                $this->ctrl->forwardCommand(new ilLearningProgressGUI(
                    ilLearningProgressBaseGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $usr_id === 0 ? $this->user->getId() : $usr_id
                ));
                break;

            case strtolower(ilExportGUI::class):
                $this->checkPermission('write');

                $this->prepareOutput();
                $this->tabs_gui->activateTab(self::UI_TAB_ID_EXPORT);

                $gui = new ilExportGUI($this);
                $gui->addFormat('xml');
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilObjectCustomIconConfigurationGUI::class):
                if (!$this->checkPermissionBool('write') || !$this->settings->get('custom_icons', '0')) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }

                $this->prepareOutput();
                $this->tabs_gui->activateTab(self::UI_TAB_ID_SETTINGS);
                $this->setSettingsSubTabs(self::UI_TAB_ID_ICON);

                require_once 'Services/Object/Icon/classes/class.ilObjectCustomIconConfigurationGUI.php';
                $gui = new ilObjectCustomIconConfigurationGUI($this->dic, $this, $this->object);
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilObjectCopyGUI::class):
                $this->tpl->loadStandardTemplate();

                $gui = new ilObjectCopyGUI($this);
                $gui->setType(self::OBJ_TYPE);

                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $settingsCommands = array_map('strtolower', [self::UI_CMD_EDIT, self::UI_CMD_UPDATE]);
                switch (true) {
                    case in_array(strtolower($cmd), $settingsCommands, true):
                        $this->setSettingsSubTabs(self::UI_TAB_ID_SETTINGS);
                        break;
                }

                if (in_array(strtolower($cmd), array_map('strtolower', ['addToDesk', 'removeFromDesk']), true)) {
                    $this->ctrl->setCmd($cmd . 'Object');
                }

                parent::executeCommand();
        }
    }

    public function addToNavigationHistory() : void
    {
        if (!$this->getCreationMode() && $this->checkPermissionBool('read')) {
            $this->navHistory->addItem(
                $this->object->getRefId(),
                ilLink::_getLink($this->object->getRefId(), $this->object->getType()),
                $this->object->getType()
            );
        }
    }

    public function renderHeaderActions() : void
    {
        if (!$this->getCreationMode() && $this->checkPermissionBool('read')) {
            $this->addHeaderAction();
        }
    }

    public function infoScreenForward() : void
    {
        if (!$this->infoScreenEnabled) {
            return;
        }

        if (!$this->checkPermissionBool('visible') && !$this->checkPermissionBool('read')) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->tabs_gui->activateTab(self::UI_TAB_ID_INFO);

        $info = new ilInfoScreenGUI($this);
        $info->enableLearningProgress();
        $info->enablePrivateNotes();
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        $this->ctrl->forwardCommand($info);
    }

    protected function setSettingsSubTabs(string $activeTab) : void
    {
        if ($this->checkPermissionBool('write')) {
            $this->tabs_gui->addSubTab(
                self::UI_TAB_ID_SETTINGS,
                $this->lng->txt('settings'),
                $this->ctrl->getLinkTarget($this, self::UI_CMD_EDIT)
            );

            if ($this->settings->get('custom_icons', '0')) {
                $this->tabs_gui->addSubTab(
                    self::UI_TAB_ID_ICON,
                    $this->lng->txt('icon_settings'),
                    $this->ctrl->getLinkTargetByClass(ilObjectCustomIconConfigurationGUI::class)
                );
            }

            $this->tabs_gui->addSubTab(
                self::UI_TAB_ID_STYLE,
                $this->lng->txt('cont_style'),
                $this->ctrl->getLinkTargetByClass("ilobjectcontentstylesettingsgui", "")
            );

            $this->tabs_gui->addSubTab(
                self::UI_TAB_ID_I18N,
                $this->lng->txt('obj_multilinguality'),
                $this->ctrl->getLinkTargetByClass(ilObjectTranslationGUI::class)
            );

            $this->tabs_gui->activateSubTab($activeTab);
        }
    }

    protected function addLocatorItems() : void
    {
        if ($this->object instanceof ilObject) {
            $this->locator->addItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTarget($this, self::UI_CMD_VIEW),
                '',
                $this->object->getRefId()
            );
        }
    }

    public function infoScreen() : void
    {
        $this->ctrl->setCmd('showSummary');
        $this->ctrl->setCmdClass(ilInfoScreenGUI::class);

        $this->infoScreenForward();
    }

    public function view() : void
    {
        $this->checkPermission('read');

        $this->populateContentToolbar();

        $this->tabs_gui->activateTab(self::UI_TAB_ID_CONTENT);

        $this->tpl->setPermanentLink($this->object->getType(), $this->object->getRefId(), '', '_top');

        $this->tpl->setContent($this->getContent());
    }

    protected function populateContentToolbar() : void
    {
        if (!$this->user->isAnonymous() && $this->checkPermissionBool('write')) {
            $this->lng->loadLanguageModule('cntr');
            $this->toolbar->addComponent(
                $this->uiServices->factory()->button()->primary(
                    $this->lng->txt('cntr_text_media_editor'),
                    $this->ctrl->getLinkTargetByClass(ilContentPagePageGUI::class, 'edit')
                )
            );
        }
    }

    /**
     * @param string $ctrlLink A link which describes the target controller for all page object links/actions
     * @return string
     * @throws ilException
     */
    public function getContent(string $ctrlLink = '') : string
    {
        if ($this->checkPermissionBool('read')) {
            $this->object->trackProgress($this->user->getId());

            $this->initStyleSheets();

            $forwarder = new ilContentPagePageCommandForwarder(
                $this->http,
                $this->ctrl,
                $this->tabs_gui,
                $this->lng,
                $this->object,
                $this->user,
                $this->refinery,
                $this->content_style_domain
            );
            $forwarder->setPresentationMode(ilContentPagePageCommandForwarder::PRESENTATION_MODE_PRESENTATION);

            return $forwarder->forward($ctrlLink);
        }

        return '';
    }

    protected function initStyleSheets() : void
    {
        $this->content_style_gui->addCss($this->tpl, $this->object->getRefId());
        $this->tpl->setCurrentBlock('SyntaxStyle');
        $this->tpl->setVariable('LOCATION_SYNTAX_STYLESHEET', ilObjStyleSheet::getSyntaxStylePath());
        $this->tpl->parseCurrentBlock();
    }

    protected function afterSave(ilObject $new_object) : void
    {
        $new_object->getObjectTranslation()->addLanguage(
            $this->lng->getDefaultLanguage(),
            $new_object->getTitle(),
            $new_object->getDescription(),
            true,
            true
        );
        $new_object->getObjectTranslation()->save();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('object_added'), true);
        $this->ctrl->redirect($this, 'edit');
    }

    protected function setTitleAndDescription() : void
    {
        parent::setTitleAndDescription();

        $icon = ilObject::_getIcon($this->object->getId(), 'big', $this->object->getType());
        $this->tpl->setTitleIcon($icon, $this->lng->txt('obj_' . $this->object->getType()));
    }

    protected function initEditCustomForm(ilPropertyFormGUI $a_form) : void
    {
        $this->addAvailabilitySection($a_form);

        $presentationHeader = new ilFormSectionHeaderGUI();
        $presentationHeader->setTitle($this->lng->txt('settings_presentation_header'));
        $a_form->addItem($presentationHeader);

        $this->object_service->commonSettings()->legacyForm($a_form, $this->object)->addTileImage();

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt('obj_features'));
        $a_form->addItem($sh);

        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $a_form,
            [
                ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY
            ]
        );
    }

    private function addAvailabilitySection(ilPropertyFormGUI $form) : void
    {
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('rep_activation_availability'));
        $form->addItem($section);

        $online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'), 'activation_online');
        $online->setInfo($this->lng->txt('copa_activation_online_info'));
        $form->addItem($online);
    }

    protected function getEditFormCustomValues(array &$a_values) : void
    {
        $a_values['activation_online'] = $this->object->getOfflineStatus() === false;
        $a_values[ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY] = $this->infoScreenEnabled;
    }

    protected function updateCustom(ilPropertyFormGUI $form) : void
    {
        $this->object->setOfflineStatus(!(bool) $form->getInput('activation_online'));
        $this->object->update();

        ilObjectServiceSettingsGUI::updateServiceSettingsForm(
            $this->object->getId(),
            $form,
            [
                ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY
            ]
        );
        $this->object_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();
    }
}
