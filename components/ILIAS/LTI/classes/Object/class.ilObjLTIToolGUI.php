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

declare(strict_types=1);

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;

/**
 * Screens of the repository object type lti. The object is created for one tool, which cannot be
 * changed afterwards, and opening it launches that tool.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 *
 * @ilCtrl_Calls ilObjLTIToolGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjLTIToolGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjLTIToolGUI: ilLearningProgressGUI
 * @ilCtrl_Calls ilObjLTIToolGUI: ilLTIToolXapiStatementsGUI
 * @ilCtrl_Calls ilObjLTIToolGUI: ilLTIObjectGradebookGUI
 * @ilCtrl_Calls ilObjLTIToolGUI: ilLTIObjectRankingGUI
 * @ilCtrl_Calls ilObjLTIToolGUI: ilLTIObjectSettingsGUI
 * @ilCtrl_Calls ilObjLTIToolGUI: ilLTIToolLaunchGUI
 * @ilCtrl_Calls ilObjLTIToolGUI: ilLTIToolSettingsGUI
 * @ilCtrl_Calls ilObjLTIToolGUI: ilObjectMetaDataGUI
 * @ilCtrl_Calls ilObjLTIToolGUI: ilPermissionGUI
 */
class ilObjLTIToolGUI extends ilObject2GUI
{
    public const string CMD_LAUNCH = 'launch';
    public const string CMD_DELIVER_CERTIFICATE = 'deliverCertificate';

    private const string CMD_SAVE_OWN_TOOL = 'saveOwnTool';
    private const string CMD_REGISTER_TOOL = 'registerTool';
    private const string VERSION_PARAM = 'version';
    private const string TAB_CONTENT = 'tab_content';
    private const string TAB_INFO = 'tab_info';
    private const string TAB_SETTINGS = 'tab_settings';
    private const string TAB_STATEMENTS = 'tab_statements';
    private const string TAB_RANKING = 'tab_scoring';
    private const string TAB_GRADEBOOK = 'tab_grade_synchronization';
    private const string TAB_LEARNING_PROGRESS = 'learning_progress';
    private const string TAB_METADATA = 'meta_data';
    private const string TAB_PERMISSIONS = 'id_permissions';

    public function getType(): string
    {
        return 'lti';
    }

    /**
     * Permanent link of an object: it opens with the permission to read, its info screen with the
     * permission to see it only.
     *
     * @throws ilCtrlException
     */
    public static function _goto(string $a_target): void
    {
        global $DIC;

        $access = $DIC->access();
        $lng = $DIC->language();
        $error = $DIC['ilErr'];
        $ref_id = (int) explode('_', $a_target)[0];

        if ($ref_id > 0 && $access->checkAccess('read', '', $ref_id)) {
            $DIC->ctrl()->setTargetScript('ilias.php');
            $DIC->ctrl()->setParameterByClass(self::class, 'ref_id', $ref_id);
            $DIC->ctrl()->redirectByClass([ilRepositoryGUI::class, self::class]);
        }

        if ($ref_id > 0 && $access->checkAccess('visible', '', $ref_id)) {
            ilObjectGUI::_gotoRepositoryNode($ref_id, 'infoScreen');
        }

        if ($ref_id > 0 && $access->checkAccess('read', '', ROOT_FOLDER_ID)) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage(
                'info',
                sprintf($lng->txt('msg_no_perm_read_item'), ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))),
                true
            );
            ilObjectGUI::_gotoRepositoryRoot();
        }

        $error->raiseError($lng->txt('msg_no_perm_read_lm'), $error->FATAL);
    }

    /**
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    public function executeCommand(): void
    {
        $this->lng->loadLanguageModule('lti');
        $this->prepareOutput();
        $this->trackReadEvent();

        switch ($this->ctrl->getNextClass($this)) {
            case strtolower(ilCommonActionDispatcherGUI::class):
                $this->ctrl->forwardCommand(ilCommonActionDispatcherGUI::getInstanceFromAjaxCall());
                break;

            case strtolower(ilLTIToolLaunchGUI::class):
                $this->tabs_gui->activateTab(self::TAB_CONTENT);
                $this->ctrl->forwardCommand(new ilLTIToolLaunchGUI($this->getLTIObject()));
                break;

            case strtolower(ilInfoScreenGUI::class):
                $this->tabs_gui->activateTab(self::TAB_INFO);
                $info = new ilInfoScreenGUI($this);
                $this->configureInfoScreen($info);
                $this->ctrl->forwardCommand($info);
                break;

            case strtolower(ilLTIToolXapiStatementsGUI::class):
                $this->tabs_gui->activateTab(self::TAB_STATEMENTS);
                $this->ctrl->forwardCommand(new ilLTIToolXapiStatementsGUI($this->getLTIObject()));
                break;

            case strtolower(ilLTIObjectRankingGUI::class):
                $this->tabs_gui->activateTab(self::TAB_RANKING);
                $this->ctrl->forwardCommand(new ilLTIObjectRankingGUI($this->getLTIObject()));
                break;

            case strtolower(ilLTIObjectGradebookGUI::class):
                $this->tabs_gui->activateTab(self::TAB_GRADEBOOK);
                $this->ctrl->forwardCommand(new ilLTIObjectGradebookGUI($this->getLTIObject()));
                break;

            case strtolower(ilLearningProgressGUI::class):
                if (!ilObjLTIToolAccess::hasLearningProgressAccess($this->getLTIObject())) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }
                $this->tabs_gui->activateTab(self::TAB_LEARNING_PROGRESS);
                $this->ctrl->forwardCommand(new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId()
                ));
                break;

            case strtolower(ilObjectMetaDataGUI::class):
                $this->checkPermission('write');
                $this->tabs_gui->activateTab(self::TAB_METADATA);
                $this->ctrl->forwardCommand(new ilObjectMetaDataGUI($this->getLTIObject()));
                break;

            case strtolower(ilLTIObjectSettingsGUI::class):
                $this->checkPermission('write');
                $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                $this->ctrl->forwardCommand(new ilLTIObjectSettingsGUI($this->getLTIObject()));
                break;

            case strtolower(ilLTIToolSettingsGUI::class):
                $this->checkPermission('write');
                $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                new ilLTIObjectSettingsGUI($this->getLTIObject())->addSubTabs();
                $this->ctrl->forwardCommand(new ilLTIToolSettingsGUI($this->getLTIObject()));
                break;

            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->activateTab(self::TAB_PERMISSIONS);
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                break;

            default:
                $cmd = $this->ctrl->getCmd(self::CMD_LAUNCH);
                // an object without content to launch opens on its info screen
                if ($cmd === self::CMD_LAUNCH && $this->object instanceof ilObjLTITool && !$this->isContentAvailable()) {
                    $this->ctrl->redirectByClass(ilInfoScreenGUI::class, 'showSummary');
                }
                $this->{$cmd}();
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function setTabs(): void
    {
        if (!$this->object instanceof ilObjLTITool) {
            parent::setTabs();
            return;
        }

        if ($this->checkPermissionBool('read') && $this->isContentAvailable()) {
            $this->tabs_gui->addTab(
                self::TAB_CONTENT,
                $this->lng->txt(self::TAB_CONTENT),
                $this->ctrl->getLinkTarget($this, self::CMD_LAUNCH)
            );
        }

        $this->tabs_gui->addTab(
            self::TAB_INFO,
            $this->lng->txt(self::TAB_INFO),
            $this->ctrl->getLinkTargetByClass(ilInfoScreenGUI::class, 'showSummary')
        );

        if ($this->checkPermissionBool('write')) {
            $this->tabs_gui->addTab(
                self::TAB_SETTINGS,
                $this->lng->txt(self::TAB_SETTINGS),
                $this->ctrl->getLinkTargetByClass(ilLTIObjectSettingsGUI::class, ilLTIObjectSettingsGUI::CMD_SHOW)
            );
        }

        $this->addReportTabs();

        if ($this->checkPermissionBool('write')) {
            $metadata_link = new ilObjectMetaDataGUI($this->object)->getTab();
            if ($metadata_link !== null && $metadata_link !== '') {
                $this->tabs_gui->addTab(self::TAB_METADATA, $this->lng->txt(self::TAB_METADATA), $metadata_link);
            }
        }

        parent::setTabs();
    }

    /**
     * The reports on what users did in the tool, each one only where the tool provides its data.
     *
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    private function addReportTabs(): void
    {
        $object = $this->getLTIObject();
        $reports = [
            self::TAB_STATEMENTS => [ilLTIToolXapiStatementsGUI::class, ilObjLTIToolAccess::hasStatementsAccess($object)],
            self::TAB_RANKING => [ilLTIObjectRankingGUI::class, ilObjLTIToolAccess::hasRankingAccess($object)],
            self::TAB_GRADEBOOK => [ilLTIObjectGradebookGUI::class, $object->getTool()->isGradeSynchronization()],
            self::TAB_LEARNING_PROGRESS => [ilLearningProgressGUI::class, ilObjLTIToolAccess::hasLearningProgressAccess($object)],
        ];

        foreach ($reports as $tab => [$class, $available]) {
            if ($available) {
                $this->tabs_gui->addTab($tab, $this->lng->txt($tab), $this->ctrl->getLinkTargetByClass($class));
            }
        }
    }

    /**
     * Opening an object counts as reading it, which the learning progress takes into account.
     */
    private function trackReadEvent(): void
    {
        if ($this->creation_mode || !$this->object instanceof ilObjLTITool) {
            return;
        }

        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $this->user->getId()
        );
        ilLPStatusWrapper::_updateStatus($this->object->getId(), $this->user->getId());
    }

    /**
     * Offers the tools the user may pick and, with the permission to define tools, a form for one of
     * their own. There is no title to fill in: an object is named after its tool.
     *
     * @throws ilCtrlException
     */
    public function create(): void
    {
        if (!$this->checkPermissionBool('create', '', $this->getType())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->renderCreation();
    }

    /**
     * The ways of creating an object, one section each, with the first one open. The section of an own
     * tool opens instead once it has been used, that is when its form comes back with errors or when
     * the LTI version has been switched.
     *
     * @throws ilCtrlException
     */
    private function renderCreation(?Form $own_form = null, ?Form $registration_form = null): void
    {
        $this->ctrl->saveParameter($this, 'crtptrefid');
        $this->ctrl->saveParameter($this, 'crtcb');
        $this->ctrl->setParameter($this, 'new_type', $this->getType());
        $this->tpl->setTitleIcon(ilObject::getIconForType($this->getType()));
        $this->tpl->setTitle($this->lng->txt('obj_' . $this->getType()));
        $this->tabs_gui->setBackTarget($this->lng->txt('cancel'), $this->ctrl->getLinkTargetByClass(static::class, 'cancel'));
        $this->tpl->addCss('./assets/css/lti_creation.css');

        $accordion = new ilAccordionGUI();
        $accordion->setBehaviour(ilAccordionGUI::FIRST_OPEN);
        $accordion->setContentClass('il-lti-creation');
        // the section a user works in stays open, which is also what lets a section be forced open
        $accordion->setId('lti_create');
        $accordion->setUseSessionStorage(true);
        $accordion->addItem(
            $this->lng->txt($this->getType() . '_select_provider'),
            $this->ui_renderer->render($this->buildToolSelection())
        );

        if (ilObjLTIAdministrationAccess::hasOwnToolCreationAccess()) {
            $accordion->addItem(
                $this->lng->txt('lti_dynamic_registration'),
                $this->ui_renderer->render($registration_form ?? $this->buildRegistrationForm()),
                $registration_form !== null
            );
            $accordion->addItem(
                $this->lng->txt('lti_custom_new'),
                $this->ui_renderer->render($this->buildOwnTool($own_form)),
                $own_form !== null || $this->request_wrapper->has(self::VERSION_PARAM)
            );
        }

        $this->tpl->setContent($accordion->getHTML());
    }

    /**
     * Creates the object for the tool of the request.
     *
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    public function save(): void
    {
        if (!$this->checkPermissionBool('create', '', $this->getType())) {
            $this->error->raiseError($this->lng->txt('no_create_permission'), $this->error->MESSAGE);
        }

        $tool = new ilLTITool($this->getIntParameter('tool_id'));
        if (!$tool->isSelectableBy($this->user->getId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('lti_no_provider_selected'));
            $this->create();
            return;
        }

        $this->createForTool($tool);
    }

    /**
     * Stores a tool of the user and creates the object for it.
     *
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    protected function saveOwnTool(): void
    {
        if (!ilObjLTIAdministrationAccess::hasOwnToolCreationAccess()) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->ctrl->setParameter($this, 'new_type', $this->getType());
        $form = $this->buildOwnToolForm();
        $with_errors = $form->save($this->getOwnToolAction(), $this->request, true);

        if ($with_errors !== null) {
            $this->renderCreation($with_errors);
            return;
        }

        $this->createForTool(new ilLTITool($form->getSavedId()));
    }

    /**
     * Where a tool of the user is registered through LTI Advantage Dynamic Registration. The registration
     * itself comes with LTI Advantage: for now the request is only validated.
     *
     * @throws ilCtrlException
     */
    protected function registerTool(): void
    {
        if (!ilObjLTIAdministrationAccess::hasOwnToolCreationAccess()) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->ctrl->setParameter($this, 'new_type', $this->getType());
        $form = $this->buildRegistrationForm()->withRequest($this->request);
        if ($form->getData() !== null) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('not_available'));
        }

        $this->renderCreation(null, $form);
    }

    /**
     * @throws ilCtrlException
     */
    private function buildRegistrationForm(): Form
    {
        $field = $this->ui_factory->input()->field();
        // the tool of the last row of the selection must not end up in the action of this form
        $this->ctrl->setParameter($this, 'tool_id', null);

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, self::CMD_REGISTER_TOOL),
            [
                'url' => $field->url($this->lng->txt('lti_con_prov_dyn_reg_url'), $this->lng->txt('lti_con_prov_dyn_reg_url_info'))
                    ->withRequired(true),
                'params' => $field->text($this->lng->txt('lti_con_prov_dyn_reg_params'), $this->lng->txt('lti_con_prov_dyn_reg_params_info')),
            ]
        )->withSubmitLabel($this->lng->txt('add'));
    }

    /**
     * The certificate of the current user, once they have one.
     *
     * @throws ilCtrlException
     */
    protected function deliverCertificate(): void
    {
        if (!new ilCertificateDownloadValidator()->isCertificateDownloadable($this->user->getId(), $this->object->getId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass(ilInfoScreenGUI::class, 'showSummary');
        }

        $this->lng->loadLanguageModule('certificate');
        new ilCertificatePdfAction(
            new ilPdfGenerator(new ilUserCertificateRepository()),
            new ilCertificateUtilHelper(),
            $this->lng->txt('error_creating_certificate_pdf')
        )->downloadPdf($this->user->getId(), $this->object->getId());
    }

    /**
     * The header offers the certificate of the current user, once they have one.
     */
    protected function initHeaderAction(?string $sub_type = null, ?int $sub_id = null): ?ilObjectListGUI
    {
        $header_action = parent::initHeaderAction($sub_type, $sub_id);
        if ($header_action === null || $this->creation_mode
            || !new ilCertificateDownloadValidator()->isCertificateDownloadable($this->user->getId(), $this->object->getId())) {
            return $header_action;
        }

        $this->lng->loadLanguageModule('certificate');
        $link = $this->ctrl->getLinkTarget($this, self::CMD_DELIVER_CERTIFICATE);
        $header_action->addCustomCommand($link, 'download_certificate');
        $header_action->addHeaderIcon(
            'cert_icon',
            ilUtil::getImagePath('standard/icon_cert.svg'),
            $this->lng->txt('download_certificate'),
            null,
            null,
            $link
        );

        return $header_action;
    }

    /**
     * The command the info action of the listing calls.
     *
     * @throws ilCtrlException
     */
    protected function infoScreen(): void
    {
        $this->ctrl->redirectByClass(ilInfoScreenGUI::class, 'showSummary');
    }

    /**
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    protected function launch(): void
    {
        $this->checkPermission('read');
        $this->tabs_gui->activateTab(self::TAB_CONTENT);
        $this->ctrl->forwardCommand(new ilLTIToolLaunchGUI($this->getLTIObject()));
    }

    /**
     * @throws ilCtrlException
     */
    private function buildToolSelection(): array
    {
        global $DIC;

        $table = ilLTIToolTable::forSelection(
            $this->lng,
            $this->user,
            $this->ui_factory,
            $this->ui_renderer,
            $DIC->uiService(),
            $this->tpl,
            $this->ctrl,
            $this->request,
            $this,
            'save'
        );
        $filter = $table->getFilter($this->ctrl->getLinkTarget($this, 'create'));

        return [$filter, $table->getTable($filter)];
    }

    /**
     * Each LTI version has its own form, because they authenticate the tool differently. The control on
     * top switches between them.
     *
     * @return array
     * @throws ilCtrlException
     */
    private function buildOwnTool(?Form $form): array
    {
        $version = $this->getRequestedVersion();
        $content = [$this->buildVersionControl($version)];

        $notice = ilLTIToolForm::getDeprecationNotice($this->ui_factory, $this->lng, $version);
        if ($notice !== null) {
            $content[] = $notice;
        }

        $content[] = $form ?? $this->buildOwnToolForm()->getForm(
            $this->getOwnToolAction(),
            $this->lng->txt('lti_add_own_provider')
        );

        return $content;
    }

    /**
     * @throws ilCtrlException
     */
    private function buildVersionControl(string $active): Component
    {
        // the tool of the last row of the selection must not end up in the links of this control
        $this->ctrl->setParameter($this, 'tool_id', null);

        $labels = [
            ilLTITool::VERSION_ADVANTAGE => $this->lng->txt('lti_version_advantage'),
            ilLTITool::VERSION_1P1 => $this->lng->txt('lti_version_1p1_deprecated'),
        ];

        $actions = [];
        foreach ($labels as $version => $label) {
            $this->ctrl->setParameter($this, self::VERSION_PARAM, $version);
            $actions[$label] = $this->ctrl->getLinkTarget($this, 'create');
        }
        $this->ctrl->setParameter($this, self::VERSION_PARAM, $active);

        return $this->ui_factory->viewControl()
            ->mode($actions, $this->lng->txt('lti_con_version'))
            ->withActive($labels[$active]);
    }

    private function buildOwnToolForm(): ilLTIToolForm
    {
        return new ilLTIToolForm(
            $this->lng,
            $this->ui_factory,
            $this->refinery,
            $this->user,
            0,
            $this->getRequestedVersion()
        );
    }

    /**
     * @throws ilCtrlException
     */
    private function getOwnToolAction(): string
    {
        $this->ctrl->setParameter($this, self::VERSION_PARAM, $this->getRequestedVersion());

        return $this->ctrl->getFormAction($this, self::CMD_SAVE_OWN_TOOL);
    }

    private function getRequestedVersion(): string
    {
        $version = $this->request_wrapper->has(self::VERSION_PARAM)
            ? $this->request_wrapper->retrieve(self::VERSION_PARAM, $this->refinery->kindlyTo()->string())
            : '';

        return $version === ilLTITool::VERSION_1P1 ? ilLTITool::VERSION_1P1 : ilLTITool::VERSION_ADVANTAGE;
    }

    /**
     * An object carries the title and the description of its tool, as there is nothing else to name it after.
     *
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    private function createForTool(ilLTITool $tool): void
    {
        $object = new ilObjLTITool();
        $object->setType($this->getType());
        $object->processAutoRating();
        $object->setTitle($tool->getTitle());
        $object->setDescription($tool->getDescription());
        $object->setToolId($tool->getId());
        $object->setMasteryScore($tool->getMasteryScore());
        $object->create();
        $object->createMetaData();
        $object->syncKeywordsFromTool();

        $this->ctrl->setParameter($this, 'new_type', '');
        $this->putObjectInTree($object);

        // a new object is offline and named after its tool, so its settings are where it is finished
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('object_added'), true);
        $this->ctrl->setParameterByClass(ilLTIObjectSettingsGUI::class, 'ref_id', $object->getRefId());
        $this->ctrl->redirectByClass(
            [ilObjLTIToolGUI::class, ilLTIObjectSettingsGUI::class],
            ilLTIObjectSettingsGUI::CMD_SHOW
        );
    }

    /**
     * Launching needs the object online and a tool that is still available.
     *
     * @throws ilObjectException
     */
    private function isContentAvailable(): bool
    {
        $object = $this->getLTIObject();

        return !$object->getOfflineStatus()
            && $object->getTool()->getAvailability() !== ilLTITool::AVAILABILITY_NONE;
    }

    /**
     * What the info screen tells about the tool is what a user needs to judge the data it receives.
     *
     * @throws ilObjectException
     */
    private function configureInfoScreen(ilInfoScreenGUI $info): void
    {
        if (!$this->checkPermissionBool('visible') && !$this->checkPermissionBool('read')) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        $object = $this->getLTIObject();
        $tool = $object->getTool();

        if ($tool->getUrl() === '') {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('lti_provider_not_set_msg'));
        } elseif ($tool->getAvailability() === ilLTITool::AVAILABILITY_NONE) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('lti_provider_not_avail_msg'));
        }

        $info->enablePrivateNotes();
        $info->enableNews($this->checkPermissionBool('read'));
        $info->enableNewsEditing(false);
        if ($this->checkPermissionBool('write') && new ilSetting('news')->get('enable_rss_for_internal')) {
            $info->setBlockProperty('news', 'settings', 'true');
            $info->setBlockProperty('news', 'public_notifications_option', 'true');
        }

        $info->addMetaDataSections($object->getId(), 0, $object->getType());

        if ($tool->hasOutcome() && (ilLPObjSettings::_lookupDBMode($object->getId()) ?? ilLPObjSettings::LP_MODE_DEACTIVATED) !== ilLPObjSettings::LP_MODE_DEACTIVATED) {
            $info->addSection($this->lng->txt('lti_info_learning_progress_section'));
            $info->addProperty($this->lng->txt('mastery_score'), round(100 * $object->getMasteryScore(), 2) . ' %');
        }

        $info->addSection($this->lng->txt('lti_info_privacy_section'));
        $info->addProperty($this->lng->txt('lti_con_prov_url'), $tool->getUrl());
        $info->addProperty(
            $this->lng->txt('conf_privacy_name'),
            $this->lng->txt('conf_privacy_name_' . ilObjCmiXapiGUI::getPrivacyNameString($tool->getPrivacyName()))
        );
        $info->addProperty(
            $this->lng->txt('conf_privacy_ident'),
            $this->lng->txt('conf_privacy_ident_' . ilObjCmiXapiGUI::getPrivacyIdentString($tool->getPrivacyIdent()))
        );
        if ($tool->isExternal()) {
            $info->addProperty(
                $this->lng->txt('lti_info_external_provider_label'),
                $this->lng->txt('lti_info_external_provider_info')
            );
        }
        if ($tool->getUseXapi()) {
            $info->addProperty($this->lng->txt('lti_con_prov_xapi_launch_url'), $tool->getXapiLaunchUrl());
        }
    }

    private function getIntParameter(string $name): int
    {
        return $this->request_wrapper->has($name)
            ? $this->request_wrapper->retrieve($name, $this->refinery->kindlyTo()->int())
            : 0;
    }

    /**
     * @throws ilObjectException
     */
    private function getLTIObject(): ilObjLTITool
    {
        if (!$this->object instanceof ilObjLTITool) {
            throw new ilObjectException('no LTI object given');
        }

        return $this->object;
    }
}
