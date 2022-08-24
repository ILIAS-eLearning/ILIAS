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
 ********************************************************************
 */

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjSessionGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjSessionGUI: ilExportGUI, ilCommonActionDispatcherGUI, ilMembershipMailGUI
* @ilCtrl_Calls ilObjSessionGUI:  ilLearningProgressGUI, ilSessionMembershipGUI, ilObjectMetaDataGUI, ilPropertyFormGUI
* @ilCtrl_Calls ilObjSessionGUI: ilBookingGatewayGUI
*
* @ingroup ModulesSession
*/
class ilObjSessionGUI extends ilObjectGUI implements ilDesktopItemHandling
{
    protected ilAppEventHandler $event;
    protected \ILIAS\FileUpload\FileUpload $upload;
    protected ilHelpGUI $help;
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\Refinery\Factory $refinery;

    public ilLanguage $lng;
    public ilCtrl $ctrl;
    public ilGlobalTemplateInterface $tpl;
    protected ilObjUser $user;
    public ilTree $tree;
    protected ilAccessHandler $access;
    protected ilToolbarGUI $toolbar;
    protected ilErrorHandling $ilErr;
    protected ilObjectService $object_service;
    public ilObjectDefinition $objDefinition;
    protected ilTabsGUI $tabs_gui;
    protected ilLocatorGUI $locator;
    protected ilRbacReview $rbacreview;

    protected int $container_ref_id = 0;
    protected int $container_obj_id = 0;
    protected array $files = [];
    protected ?ilPropertyFormGUI $form = null;
    protected ilAdvancedMDRecordGUI $record_gui;
    protected ?ilEventRecurrence $rec = null;
    protected ?ilEventItems $event_items = null;
    protected ?ilEventParticipants $event_part = null;
    protected int $requested_ref_id = 0;
    protected int $requested_user_id = 0;
    protected int $requested_file_id = 0;
    protected int $requested_offset = 0;
    protected string $requested_sort_by = "";
    protected string $requested_sort_order = "";
    protected array $requested_items = [];

    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $this->ilErr = $DIC["ilErr"];
        $this->object_service = $DIC->object();
        $this->objDefinition = $DIC['objDefinition'];
        $this->tabs_gui = $DIC->tabs();
        $this->locator = $DIC["ilLocator"];
        $this->rbacreview = $DIC->rbac()->review();
        $this->event = $DIC->event();
        $this->upload = $DIC->upload();
        $this->help = $DIC->help();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->type = "sess";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("event");
        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('trac');
        $this->lng->loadLanguageModule('sess');

        if ($this->http->wrapper()->query()->has('ref_id')) {
            $this->requested_ref_id = $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        if ($this->http->wrapper()->query()->has('user_id')) {
            $this->requested_user_id = $this->http->wrapper()->query()->retrieve(
                'user_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        if ($this->http->wrapper()->post()->has('file_id')) {
            $this->requested_file_id = $this->http->wrapper()->post()->retrieve(
                'file_id',
                $this->refinery->kindlyTo()->int()
            );
        } elseif ($this->http->wrapper()->query()->has('file_id')) {
            $this->requested_file_id = $this->http->wrapper()->query()->retrieve(
                'file_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        if ($this->http->wrapper()->query()->has('offset')) {
            $this->requested_offset = $this->http->wrapper()->query()->retrieve(
                'offset',
                $this->refinery->kindlyTo()->int()
            );
        }

        if ($this->http->wrapper()->query()->has('sort_by')) {
            $this->requested_sort_by = $this->http->wrapper()->query()->retrieve(
                'sort_by',
                $this->refinery->kindlyTo()->string()
            );
        }

        if ($this->http->wrapper()->query()->has('sort_order')) {
            $this->requested_sort_order = $this->http->wrapper()->query()->retrieve(
                'sort_order',
                $this->refinery->kindlyTo()->string()
            );
        }

        if ($this->http->wrapper()->post()->has('items')) {
            $this->requested_items = $this->http->wrapper()->post()->retrieve(
                'items',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
    }

    public function executeCommand(): void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (
            !$this->getCreationMode() &&
            $ilAccess->checkAccess('read', '', $this->requested_ref_id)
        ) {
            $GLOBALS['DIC']['ilNavigationHistory']->addItem(
                $this->requested_ref_id,
                ilLink::_getLink($this->requested_ref_id, 'sess'),
                'sess'
            );
        }

        $this->prepareOutput();
        switch ($next_class) {
            case 'ilsessionmembershipgui':
                $this->tabs_gui->activateTab('members');
                $mem_gui = new ilSessionMembershipGUI($this, $this->object);
                $this->ctrl->forwardCommand($mem_gui);
                break;

            case "ilinfoscreengui":
                $this->checkPermission("visible");
                $this->infoScreen();	// forwards command
                break;

            case 'ilobjectmetadatagui':
                $this->checkPermission('edit_metadata');
                $this->tabs_gui->activateTab('metadata');
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilobjectcopygui':
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('sess');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilexportgui":
                //				$this->prepareOutput();
                $this->tabs_gui->setTabActive("export");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $ret = $this->ctrl->forwardCommand($exp_gui);
                //				$this->tpl->show();
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilmembershipgui':
                $this->ctrl->setReturn($this, 'members');
                $mem = new ilMembershipMailGUI($this);
                $this->ctrl->forwardCommand($mem);
                break;

            case "illearningprogressgui":
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressBaseGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $this->requested_user_id ?: $ilUser->getId()
                );
                $this->ctrl->forwardCommand($new_gui);
                $this->tabs_gui->setTabActive('learning_progress');
                break;

            case "ilpropertyformgui":
                // only case is currently adv metadata internal link in info settings, see #24497
                if (!is_object($this->object)) {
                    $form = $this->initCreateForm("sess");
                } else {
                    $form = $this->initForm("edit");
                    if ($form === true) {
                        $form = $this->form;
                    }
                }
                $ilCtrl->forwardCommand($form);
                break;

            case "ilbookinggatewaygui":
                $tree = $this->tree;
                $parent_id = $tree->getParentId($this->requested_ref_id);

                $this->tabs_gui->activateTab('obj_tool_setting_booking');
                $gui = new ilBookingGatewayGUI($this, $parent_id);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if ($cmd == "applyFilter") {
                    $this->$cmd();
                } elseif ($cmd == "resetFilter") {
                    $this->$cmd();
                }
                if (!$cmd) {
                    $cmd = "infoScreen";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }

        $this->addHeaderAction();
    }

    protected function membersObject(): void
    {
        $this->ctrl->redirectByClass('ilSessionMembershipGUI', 'participants');
    }

    public function getCurrentObject(): ilObjSession
    {
        /**
         * @var ilObjSession $object
         */
        $object = $this->object;

        return $object;
    }

    public function prepareOutput(bool $show_subobjects = true): bool
    {
        parent::prepareOutput($show_subobjects);

        if (!$this->getCreationMode()) {
            $title = strlen($this->object->getTitle()) ? (': ' . $this->object->getTitle()) : '';

            $this->tpl->setTitle(
                $this->object->getFirstAppointment()->appointmentToString() . $title
            );
        }
        return true;
    }

    public function registerObject(): void
    {
        $ilUser = $this->user;
        $ilAppEventHandler = $this->event;

        $this->checkPermission('visible');

        $part = ilParticipants::getInstance($this->getCurrentObject()->getRefId());

        $event_part = new ilEventParticipants($this->getCurrentObject()->getId());
        $event_part->updateExcusedForUser($ilUser->getId(), false);

        if (
            $this->getCurrentObject()->isRegistrationUserLimitEnabled() &&
            $this->getCurrentObject()->getRegistrationMaxUsers() &&
            (count($event_part->getRegisteredParticipants()) >= $this->getCurrentObject()->getRegistrationMaxUsers())
        ) {
            $wait = new ilSessionWaitingList($this->getCurrentObject()->getId());
            $wait->addToList($ilUser->getId());
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('sess_reg_added_to_wl'), true);
            $this->ctrl->redirect($this, 'infoScreen');
        }


        switch ($this->getCurrentObject()->getRegistrationType()) {
            case ilMembershipRegistrationSettings::TYPE_NONE:
                $this->ctrl->redirect($this, 'info');
                break;

            case ilMembershipRegistrationSettings::TYPE_DIRECT:
                $part->register($ilUser->getId());
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('event_registered'), true);

                $ilAppEventHandler->raise(
                    "Modules/Session",
                    'enter',
                    array(
                        'obj_id' => $this->getCurrentObject()->getId(),
                        'ref_id' => $this->getCurrentObject()->getRefId(),
                        'usr_id' => $ilUser->getId()
                    )
                );

                $this->ctrl->redirect($this, 'infoScreen');
                break;

            case ilMembershipRegistrationSettings::TYPE_REQUEST:
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('sess_registered_confirm'), true);
                $part->addSubscriber($ilUser->getId());

                $ilAppEventHandler->raise(
                    "Modules/Session",
                    'register',
                    array(
                        'obj_id' => $this->getCurrentObject()->getId(),
                        'ref_id' => $this->getCurrentObject()->getRefId(),
                        'usr_id' => $ilUser->getId()
                    )
                );

                $this->ctrl->redirect($this, 'infoScreen');
                break;
        }
    }

    public function joinObject(): void
    {
        $ilUser = $this->user;

        $this->checkPermission('read');

        if ($ilUser->isAnonymous()) {
            $this->ctrl->redirect($this, 'infoScreen');
        }

        if (ilEventParticipants::_isRegistered($ilUser->getId(), $this->object->getId())) {
            ilSession::set("sess_hide_info", true);
            ilEventParticipants::_unregister($ilUser->getId(), $this->object->getId());
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('event_unregistered'), true);
        } else {
            ilEventParticipants::_register($ilUser->getId(), $this->object->getId());
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('event_registered'), true);
        }

        $this->ctrl->redirect($this, 'infoScreen');
    }

    public function unregisterObject(bool $a_refuse_participation = false): void
    {
        $ilUser = $this->user;
        $ilAppEventHandler = $this->event;
        $access = $this->access;
        $tree = $this->tree;

        $part = ilSessionParticipants::getInstance($this->object->getRefId());
        if ($part->isSubscriber($ilUser->getId())) {
            $part->deleteSubscriber($ilUser->getId());
        }

        $part->unregister($ilUser->getId());

        if ($a_refuse_participation) {
            $event_part = new \ilEventParticipants($this->object->getId());
            $event_part->updateExcusedForUser($ilUser->getId(), true);
        }

        ilSessionWaitingList::deleteUserEntry($ilUser->getId(), $this->getCurrentObject()->getId());

        // check for visible permission of user
        ilRbacSystem::resetCaches();
        $access->clear();
        $has_access = $access->checkAccessOfUser(
            $ilUser->getId(),
            'visible',
            '',
            $this->object->getRefId()
        );
        if (!$has_access) {
            $parent = $tree->getParentId($this->object->getRefId());
            $this->redirectToRefId($parent);
            return;
        }

        $ilAppEventHandler->raise(
            "Modules/Session",
            'unregister',
            array(
                'obj_id' => $this->getCurrentObject()->getId(),
                'ref_id' => $this->getCurrentObject()->getRefId(),
                'usr_id' => $ilUser->getId()
            )
        );
        if ($a_refuse_participation) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('sess_participation_refused_info'), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('event_unregistered'), true);
        }
        $this->ctrl->returnToParent($this);
    }

    public static function _goto(string $a_target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC->access();
        $lng = $DIC->language();

        $ilCtrl = $DIC->ctrl();
        $parts = explode('_', $a_target);
        $a_target = (int) $parts[0];

        if ($ilAccess->checkAccess('write', '', $a_target)) {
            if (isset($parts[1]) && 'part' === $parts[1]) {
                $ilCtrl->setTargetScript('ilias.php');
                $ilCtrl->setParameterByClass('ilSessionMembershipGUI', 'ref_id', $a_target);
                $ilCtrl->setTargetScript('ilias.php');
                $ilCtrl->redirectByClass(array('ilRepositoryGUI', __CLASS__, 'ilSessionMembershipGUI'));
            }
        }

        if ($ilAccess->checkAccess('visible', "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }
    }

    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreenObject(): void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    public function modifyItemGUI(ilObjectListGUI $a_item_list_gui, array $a_item_data, bool $a_show_path): void
    {
        $tree = $this->tree;

        // if folder is in a course, modify item list gui according to course requirements
        if ($course_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs')) {
            // #10611
            ilObjectActivation::addListGUIActivationProperty($a_item_list_gui, $a_item_data);

            $course_obj_id = ilObject::_lookupObjId($course_ref_id);
            ilObjCourseGUI::_modifyItemGUI(
                $a_item_list_gui,
                get_class($this),
                $a_item_data,
                $a_show_path,
                $course_ref_id,
                $course_obj_id,
                $this->object->getRefId()
            );
        }
    }

    /**
     * show join request
     * This method is only needed to keep showJoinRequestButton method protected.
     */
    public function showJoinRequestButtonInCalendar(ilToolbarGUI $a_ilToolbar): bool
    {
        return $this->showJoinRequestButton($a_ilToolbar);
    }

    protected function refuseParticipationObject(): void
    {
        $this->unregisterObject(true);
    }

    protected function showJoinRequestButton(?ilToolbarGUI $ilToolbar = null): bool
    {
        $ilUser = $this->user;

        if (!$ilToolbar) {
            $ilToolbar = $this->toolbar;
        }

        if (!$this->getCurrentObject()->enabledRegistrationForUsers() || $ilUser->isAnonymous()) {
            return false;
        }

        $part = ilParticipants::getInstance($this->getCurrentObject()->getRefId());

        $btn_attend = ilLinkButton::getInstance();
        $btn_attend->addCSSClass("btn-primary");
        $this->ctrl->setParameter($this, "ref_id", $this->getCurrentObject()->getRefId());

        $btn_excused = null;
        if ($this->object->isCannotParticipateOptionEnabled()) {
            $btn_excused = \ilLinkButton::getInstance();
            $btn_excused->setCaption($this->lng->txt('sess_bt_refuse'), false);
            $btn_excused->setUrl($this->ctrl->getLinkTarget($this, 'refuseParticipation'));
        }


        if (ilEventParticipants::_isRegistered($ilUser->getId(), $this->getCurrentObject()->getId())) {
            if (!is_null($btn_excused)) {
                $ilToolbar->addButtonInstance($btn_excused);
            }
            return true;
        } elseif ($part->isSubscriber($ilUser->getId())) {
            if (!is_null($btn_excused)) {
                $ilToolbar->addButtonInstance($btn_excused);
            }
            return true;
        } elseif (ilSessionWaitingList::_isOnList($ilUser->getId(), $this->getCurrentObject()->getId())) {
            if (!is_null($btn_excused)) {
                $ilToolbar->addButtonInstance($btn_excused);
            }
            return true;
        }

        $event_part = new ilEventParticipants($this->getCurrentObject()->getId());

        if (
            $this->getCurrentObject()->isRegistrationUserLimitEnabled() &&
            $this->getCurrentObject()->getRegistrationMaxUsers() &&
            (count($event_part->getRegisteredParticipants()) >= $this->getCurrentObject()->getRegistrationMaxUsers())
        ) {
            if ($this->getCurrentObject()->isRegistrationWaitingListEnabled()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('sess_reg_max_users_exceeded_wl'));
                $btn_attend->setCaption($this->lng->txt("mem_add_to_wl"), false);
                $btn_attend->setUrl($this->ctrl->getLinkTargetByClass(array("ilRepositoryGUI", "ilObjSessionGUI"), "register"));
                $ilToolbar->addButtonInstance($btn_attend);
                if (!$event_part->isExcused($ilUser->getId()) && !is_null($btn_excused)) {
                    $ilToolbar->addButtonInstance($btn_excused);
                }
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('sess_reg_max_users_exceeded'));
            }
            return true;
        } else {
            if (is_null(ilSession::get("sess_hide_info"))) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('sess_join_info'));
                $btn_attend->setCaption($this->lng->txt("join_session"), false);
                $btn_attend->setUrl($this->ctrl->getLinkTargetByClass(array("ilRepositoryGUI", "ilObjSessionGUI"), "register"));
                $ilToolbar->addButtonInstance($btn_attend);
                if (!$event_part->isExcused($ilUser->getId()) && !is_null($btn_excused)) {
                    $ilToolbar->addButtonInstance($btn_excused);
                }
                return true;
            }
        }
        return false;
    }

    protected function infoScreen(): void
    {
        $ilUser = $this->user;
        $tree = $this->tree;
        $lng = $this->lng;

        $this->checkPermission('visible');
        $this->tabs_gui->setTabActive('info_short');

        $this->showJoinRequestButton();

        $info = new ilInfoScreenGUI($this);
        $info->enableBookingInfo(true);

        $eventItems = ilObjectActivation::getItemsByEvent($this->object->getId());
        $parent_id = $tree->getParentId($this->object->getRefId());
        $parent_id = ilObject::_lookupObjId($parent_id);
        $eventItems = ilContainerSorting::_getInstance($parent_id)->sortSubItems(
            'sess',
            $this->object->getId(),
            $eventItems
        );

        $lng->loadLanguageModule("cntr");// #14158

        $html = '';
        foreach ($eventItems as $item) {
            /**
             * @var ilObjectListGUI $list_gui
             */
            $list_gui = ilSessionObjectListGUIFactory::factory($item['type']);
            $list_gui->setContainerObject($this);

            $this->modifyItemGUI($list_gui, $item, false);

            $html .= $list_gui->getListItemHTML(
                (int) $item['ref_id'],
                (int) $item['obj_id'],
                (string) $item['title'],
                (string) $item['description']
            );
        }

        if (strlen($html)) {
            $info->addSection($this->lng->txt('event_materials'));
            $info->addProperty(
                '&nbsp;',
                $html
            );
        }



        // Session information
        if (strlen($this->object->getLocation()) || strlen($this->object->getDetails())) {
            $info->addSection($this->lng->txt('event_section_information'));
        }
        if (strlen($location = $this->object->getLocation())) {
            $info->addProperty(
                $this->lng->txt('event_location'),
                ilUtil::makeClickable(nl2br($this->object->getLocation()), true)
            );
        }
        if (strlen($this->object->getDetails())) {
            $info->addProperty(
                $this->lng->txt('event_details_workflow'),
                ilUtil::makeClickable(nl2br($this->object->getDetails()), true)
            );
        }

        $this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'sess', $this->object->getId());
        $this->record_gui->setInfoObject($info);
        $this->record_gui->parse();

        // meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());


        // Tutor information
        if ($this->object->hasTutorSettings()) {
            $info->addSection($this->lng->txt('event_tutor_data'));
            if (strlen($fullname = $this->object->getName())) {
                $info->addProperty(
                    $this->lng->txt('event_lecturer'),
                    $fullname
                );
            }
            if (strlen($email = $this->object->getEmail())) {
                $info->addProperty(
                    $this->lng->txt('tutor_email'),
                    $email
                );
            }
            if (strlen($phone = $this->object->getPhone())) {
                $info->addProperty(
                    $this->lng->txt('tutor_phone'),
                    $phone
                );
            }
        }

        // support contacts
        $parts = ilParticipants::getInstance($this->object->getRefId());
        $contacts = $parts->getContacts();
        if (count($contacts) > 0) {
            $info->addSection($this->lng->txt("crs_mem_contacts"));
            foreach ($contacts as $contact) {
                $pgui = new ilPublicUserProfileGUI($contact);
                $pgui->setBackUrl($this->ctrl->getLinkTargetByClass("ilinfoscreengui"));
                $pgui->setEmbedded(true);
                $info->addProperty("", $pgui->getHTML());
            }
        }

        // forward the command
        $this->ctrl->forwardCommand($info);

        // store read event
        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $ilUser->getId()
        );
    }

    public function sendFileObject(): bool
    {
        $file = new ilSessionFile($this->requested_file_id);

        ilFileDelivery::deliverFileLegacy($file->getAbsolutePath(), $file->getFileName(), $file->getFileType());
        return true;
    }

    protected function initCreateForm($a_new_type): ilPropertyFormGUI
    {
        if (!is_object($this->object)) {
            $this->object = new ilObjSession();
        }
        if (!$this->form instanceof ilPropertyFormGUI) {
            $this->initForm('create');
        }
        return $this->form;
    }

    protected function saveAndAssignMaterialsObject(): void
    {
        $this->saveObject(false);

        $this->ctrl->setParameter($this, 'ref_id', $this->object->getRefId());
        $this->ctrl->setParameter($this, 'new_type', '');
        $this->ctrl->redirect($this, 'materials');
    }

    public function saveObject(bool $a_redirect_on_success = true): void
    {
        $ilUser = $this->user;
        $object_service = $this->object_service;

        $this->object = new ilObjSession();

        $this->ctrl->saveParameter($this, "new_type");

        $this->initForm('create');
        $this->ilErr->setMessage('');
        if (!$this->form->checkInput()) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('err_check_input')
            );
            $this->form->setValuesByPost();
            $this->createObject();
            return;
        }

        if ($this->record_gui instanceof \ilAdvancedMDRecordGUI && !$this->record_gui->importEditFormPostValues()
        ) {
            $this->ilErr->setMessage($this->lng->txt('err_check_input'));
        }

        $this->load();
        $this->loadRecurrenceSettings();

        $this->object->validate();
        $this->object->getFirstAppointment()->validate();

        if (strlen($this->ilErr->getMessage())) {
            $this->tpl->setOnScreenMessage('failure', $this->ilErr->getMessage());
            $this->form->setValuesByPost();
            $this->createObject();
        }
        // Create session
        $this->object->create();
        $this->object->createReference();
        $this->object->putInTree($this->requested_ref_id);
        $this->object->setPermissions($this->requested_ref_id);

        ilObjectServiceSettingsGUI::updateServiceSettingsForm(
            $this->object->getId(),
            $this->form,
            array(
                ilObjectServiceSettingsGUI::CUSTOM_METADATA,
            )
        );
        if ($this->record_gui instanceof \ilAdvancedMDRecordGUI) {
            $this->record_gui->writeEditForm($this->object->getId());
        }


        // apply didactic template?
        $dtpl = $this->getDidacticTemplateVar("dtpl");
        if ($dtpl) {
            $this->object->applyDidacticTemplate($dtpl);
        }

        // #14547 - active is default
        if (!$this->form->getInput("lp_preset")) {
            $lp_obj_settings = new ilLPObjSettings($this->object->getId());
            $lp_obj_settings->setMode(ilLPObjSettings::LP_MODE_DEACTIVATED);
            $lp_obj_settings->update(false);
        }

        // create appointment
        $this->object->getFirstAppointment()->setSessionId($this->object->getId());
        $this->object->getFirstAppointment()->create();

        $this->handleFileUpload();

        $object_service->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();

        $this->createRecurringSessions((bool) $this->form->getInput("lp_preset"));

        if ($a_redirect_on_success) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('event_add_new_event'), true);
            $this->ctrl->returnToParent($this);
        }
    }

    public function handleFileUpload(): void
    {
        $tree = $this->tree;

        $ev = new ilEventItems($this->object->getId());
        $items = $ev->getItems();

        $counter = 0;
        while (true) {
            if (!isset($_FILES['files']['name'][$counter])) {
                break;
            }
            if (!strlen($_FILES['files']['name'][$counter])) {
                $counter++;
                continue;
            }

            $file = new ilObjFile();
            $file->setTitle(ilUtil::stripSlashes($_FILES['files']['name'][$counter]));
            $file->setDescription('');
            $file->setFileName(ilUtil::stripSlashes($_FILES['files']['name'][$counter]));
            $file->setFileType($_FILES['files']['type'][$counter]);
            $file->setFileSize($_FILES['files']['size'][$counter]);
            $file->create();
            $new_ref_id = $file->createReference();
            $file->putInTree($tree->getParentId($this->object->getRefId()));
            $file->setPermissions($tree->getParentId($this->object->getRefId()));

            $upload = $this->upload;
            if (!$upload->hasBeenProcessed()) {
                $upload->process();
            }
            $file->getUploadFile(
                $_FILES['files']['tmp_name'][$counter],
                $_FILES['files']['name'][$counter]
            );

            $items[] = $new_ref_id;
            $counter++;
        }

        $ev->setItems($items);
        $ev->update();
    }

    protected function createRecurringSessions(bool $a_activate_lp = true): bool
    {
        $tree = $this->tree;

        if (!$this->rec->getFrequenceType()) {
            return true;
        }
        $calc = new ilCalendarRecurrenceCalculator($this->object->getFirstAppointment(), $this->rec);

        $period_start = clone $this->object->getFirstAppointment()->getStart();


        $period_end = clone $this->object->getFirstAppointment()->getStart();
        $period_end->increment(IL_CAL_YEAR, 5);
        $date_list = $calc->calculateDateList($period_start, $period_end);

        $period_diff = $this->object->getFirstAppointment()->getEnd()->get(IL_CAL_UNIX) -
            $this->object->getFirstAppointment()->getStart()->get(IL_CAL_UNIX);
        $parent_id = $tree->getParentId($this->object->getRefId());

        $evi = new ilEventItems($this->object->getId());
        $eitems = $evi->getItems();

        $counter = 0;
        foreach ($date_list->get() as $date) {
            if (!$counter++) {
                continue;
            }

            $new_obj = $this->object->cloneObject($parent_id);

            // apply didactic template?
            $dtpl = $this->getDidacticTemplateVar("dtpl");
            if ($dtpl) {
                $new_obj->applyDidacticTemplate($dtpl);
            }

            $new_obj->read();
            $new_obj->getFirstAppointment()->setStartingTime($date->get(IL_CAL_UNIX));
            $new_obj->getFirstAppointment()->setEndingTime($date->get(IL_CAL_UNIX) + $period_diff);
            $new_obj->getFirstAppointment()->update();
            $new_obj->update(true);

            // #14547 - active is default
            if (!$a_activate_lp) {
                $lp_obj_settings = new ilLPObjSettings($new_obj->getId());
                $lp_obj_settings->setMode(ilLPObjSettings::LP_MODE_DEACTIVATED);
                $lp_obj_settings->update(false);
            }

            $new_evi = new ilEventItems($new_obj->getId());
            $new_evi->setItems($eitems);
            $new_evi->update();
        }

        return true;
    }

    public function editObject(): void
    {
        $this->tabs_gui->setTabActive('settings');

        $this->initForm('edit');
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.sess_edit.html', 'Modules/Session');
        $this->tpl->setVariable('EVENT_EDIT_TABLE', $this->form->getHTML());

        if (!count($this->object->getFiles())) {
            return;
        }
        $rows = [];
        foreach ($this->object->getFiles() as $file) {
            $table_data['id'] = $file->getFileId();
            $table_data['filename'] = $file->getFileName();
            $table_data['filetype'] = $file->getFileType();
            $table_data['filesize'] = $file->getFileSize();

            $rows[] = $table_data;
        }

        $table_gui = new ilSessionFileTableGUI($this, "edit");
        $table_gui->setTitle($this->lng->txt("event_files"));
        $table_gui->setData($rows);
        $table_gui->addCommandButton("cancel", $this->lng->txt("cancel"));
        $table_gui->addMultiCommand("confirmDeleteFiles", $this->lng->txt("delete"));
        $table_gui->setSelectAllCheckbox("file_id");
        $this->tpl->setVariable('EVENT_FILE_TABLE', $table_gui->getHTML());
    }

    public function updateObject(): void
    {
        $object_service = $this->object_service;

        $old_autofill = $this->object->hasWaitingListAutoFill();

        $this->initForm('edit');
        $this->ilErr->setMessage('');
        if (!$this->form->checkInput()) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('err_check_input')
            );
            $this->form->setValuesByPost();
            $this->editObject();
            return;
        }

        //Mantis 21972: Choose didactic template on settings screen
        $old_type = ilDidacticTemplateObjSettings::lookupTemplateId($this->object->getRefId());

        $modified = false;
        $new_type_info = $this->form->getInput('didactic_type');
        if ($new_type_info) {
            $new_type = explode('_', $this->form->getInput('didactic_type'));
            $new_type = (int) $new_type[1];

            $modified = ($new_type !== $old_type);
        }

        if (
            $this->record_gui instanceof \ilAdvancedMDRecordGUI &&
            !$this->record_gui->importEditFormPostValues()
        ) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('err_check_input')
            );
            $this->form->setValuesByPost();
            $this->editObject();
        }

        $this->load();

        $this->object->validate();
        $this->object->getFirstAppointment()->validate();

        if (strlen($this->ilErr->getMessage())) {
            $this->tpl->setOnScreenMessage('failure', $this->ilErr->getMessage());
            $this->editObject();
        }
        // Update event
        $this->object->update();
        $this->object->getFirstAppointment()->update();

        ilObjectServiceSettingsGUI::updateServiceSettingsForm(
            $this->object->getId(),
            $this->form,
            array(
                ilObjectServiceSettingsGUI::CUSTOM_METADATA,
            )
        );
        if ($this->record_gui instanceof \ilAdvancedMDRecordGUI) {
            $this->record_gui->writeEditForm();
        }
        $this->handleFileUpload();

        $object_service->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();

        // if autofill has been activated trigger process
        if (!$old_autofill &&
            $this->object->hasWaitingListAutoFill()) {
            $this->object->handleAutoFill();
        }

        //Mantis 21972: Choose didactic template on settings screen
        if (!$modified) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('event_updated'), true);
            $this->ctrl->redirect($this, 'edit');
            return;
        }

        if ($new_type == 0) {
            $new_type_txt = $this->lng->txt('il_sess_status_open');
        } else {
            $dtpl = new ilDidacticTemplateSetting($new_type);
            $new_type_txt = $dtpl->getPresentationTitle($this->lng->getLangKey());
        }
        $this->tabs_gui->activateTab('settings');

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('sess_warn_sess_type_changed'));
        $confirm->addItem(
            'sess_type',
            (string) $new_type,
            $this->lng->txt('sess_info_new_sess_type') . ': ' . $new_type_txt
        );
        $confirm->setConfirm($this->lng->txt('sess_change_type'), 'updateSessionType');
        $confirm->setCancel($this->lng->txt('cancel'), 'edit');

        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * change session type
     */
    public function updateSessionTypeObject(): void
    {
        ilDidacticTemplateUtils::switchTemplate(
            $this->object->getRefId(),
            (int) $this->http->request()->getParsedBody()['sess_type']
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'edit');
    }

    public function confirmDeleteFilesObject(): bool
    {
        $this->tabs_gui->setTabActive('settings');

        if (!count($this->requested_file_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->editObject();
            return false;
        }

        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteFiles"));
        $c_gui->setHeaderText($this->lng->txt("info_delete_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "edit");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteFiles");

        // add items to delete
        foreach ($this->requested_file_id as $file_id) {
            $file = new ilSessionFile($file_id);
            if ($file->getSessionId() != $this->object->getEventId()) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
                $this->editObject();
                return false;
            }
            $c_gui->addItem("file_id[]", $file_id, $file->getFileName());
        }

        $this->tpl->setContent($c_gui->getHTML());
        return true;
    }

    public function deleteFilesObject(): bool
    {
        if (!count($this->requested_file_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->editObject();
            return false;
        }
        foreach ($this->requested_file_id as $id) {
            $file = new ilSessionFile($id);
            $file->delete();
        }
        $this->object->initFiles();
        $this->editObject();
        return true;
    }

    /**
     * @return bool|ilParticipants
     */
    protected function initContainer(bool $a_init_participants = false)
    {
        $tree = $this->tree;

        $is_course = $is_group = false;

        // #13178
        $this->container_ref_id = $tree->checkForParentType($this->object->getRefId(), 'grp');
        if ($this->container_ref_id) {
            $is_group = true;
        }
        if (!$this->container_ref_id) {
            $this->container_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs');
            if ($this->container_ref_id) {
                $is_course = true;
            }
        }
        if (!$this->container_ref_id) {
            $this->tpl->setOnScreenMessage('failure', 'No container object found. Aborting');
            return true;
        }
        $this->container_obj_id = ilObject::_lookupObjId($this->container_ref_id);

        if ($a_init_participants && $this->container_obj_id) {
            if ($is_course) {
                return ilCourseParticipants::_getInstanceByObjId($this->container_obj_id);
            } elseif ($is_group) {
                return ilGroupParticipants::_getInstanceByObjId($this->container_obj_id);
            }
        }

        return false;
    }

    public function materialsObject(): void
    {
        $tree = $this->tree;
        $objDefinition = $this->objDefinition;

        $this->tabs_gui->activateTab('materials');

        // #11337 - support ANY parent container (crs, grp, fld)
        $parent_ref_id = $tree->getParentId($this->object->getRefId());

        $gui = new ilObjectAddNewItemGUI($parent_ref_id);
        $gui->setDisabledObjectTypes(
            array_merge(
                [
                    'itgr', 'sess'
                ],
                $objDefinition->getSideBlockTypes()
            )
        );
        $gui->setAfterCreationCallback($this->ref_id);
        $gui->render();

        $this->event_items = new ilEventItems($this->object->getId());

        $tbl = new ilSessionMaterialsTableGUI($this, "materials");

        $tbl->setDisableFilterHiding(true);

        $tbl->addMultiCommand('saveMaterials', $this->lng->txt('sess_assign'));
        $tbl->addMultiCommand("removeMaterials", $this->lng->txt("remove"));

        $tbl->setTitle($this->lng->txt("event_assign_materials_table"));
        $tbl->setDescription($this->lng->txt('event_assign_materials_info'));

        $tbl->setMaterialItems($this->event_items->getItems());
        $tbl->setContainerRefId($this->getContainerRefId());
        $data = $tbl->getDataFromDb();
        $tbl->setMaterials($data);

        $this->tpl->setContent($tbl->getHTML());
    }

    public function applyFilter(): void
    {
        $tbl = new ilSessionMaterialsTableGUI($this, "materials");
        $tbl->writeFilterToSession();	// writes filter to session
        $tbl->resetOffset();		// sets record offest to 0 (first page)
        $this->ctrl->redirect($this, "materials");
    }

    public function resetFilter(): void
    {
        $tbl = new ilSessionMaterialsTableGUI($this, "materials");
        $tbl->resetOffset();		// sets record offest to 0 (first page)
        $tbl->resetFilter();		// clears filter
        $this->ctrl->redirect($this, "materials");
    }

    public function removeMaterialsObject(): void
    {
        $items_checked = $this->requested_items;

        $this->event_items = new ilEventItems($this->object->getId());
        $this->event_items->removeItems($items_checked);

        $this->postUpdateMaterials();
    }

    public function saveMaterialsObject(): void
    {
        $this->event_items = new ilEventItems($this->object->getId());
        $db_items = $this->event_items->getItems();

        $list_items_checked = $this->requested_items;
        $list_items_checked = array_map('intval', $list_items_checked);

        $items_to_save = array_merge($db_items, $list_items_checked);
        $items_to_save = array_unique($items_to_save);

        $this->event_items->setItems($items_to_save);
        $this->event_items->update();
        $this->postUpdateMaterials();
    }

    /**
     * redirect to list of materials without offset/page.
     */
    public function postUpdateMaterials(): void
    {
        $tbl = new ilSessionMaterialsTableGUI($this, "materials");
        $tbl->setOffset(0);
        $tbl->storeNavParameter();//remove offset and go to page 1

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'materials');
    }

    public function attendanceListObject(): void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs_gui;

        $this->checkPermission('manage_members');
        $ilTabs->setTabActive('event_edit_members');

        $list = $this->initAttendanceList();
        $form = $list->initForm('printAttendanceList');
        $tpl->setContent($form->getHTML());
    }

    protected function initAttendanceList(): ilAttendanceList
    {
        $members_obj = $this->initContainer(true);

        $list = new ilAttendanceList(
            $this,
            $this->object,
            $members_obj
        );
        $list->setId('sessattlst');

        $event_app = $this->object->getFirstAppointment();
        ilDatePresentation::setUseRelativeDates(false);
        $desc = ilDatePresentation::formatPeriod($event_app->getStart(), $event_app->getEnd());
        ilDatePresentation::setUseRelativeDates(true);
        $desc .= " " . $this->object->getTitle();
        $list->setTitle($this->lng->txt('sess_attendance_list'), $desc);

        $list->addPreset('mark', $this->lng->txt('trac_mark'), true);
        $list->addPreset('comment', $this->lng->txt('trac_comment'), true);
        if ($this->object->enabledRegistration()) {
            $list->addPreset('registered', $this->lng->txt('event_tbl_registered'), true);
        }
        $list->addPreset('participated', $this->lng->txt('event_tbl_participated'), true);
        $list->addBlank($this->lng->txt('sess_signature'));

        $list->addUserFilter('registered', $this->lng->txt('event_list_registered_only'));

        return $list;
    }

    protected function printAttendanceListObject(): void
    {
        $this->checkPermission('manage_members');

        $list = $this->initAttendanceList();
        $list->initFromForm();
        $list->setCallback(array($this, 'getAttendanceListUserData'));

        $this->event_part = new ilEventParticipants($this->object->getId());
        $list->getFullscreenHTML();
    }

    public function getAttendanceListUserData(int $a_user_id, array $a_filters): ?array
    {
        $data = $this->event_part->getUser($a_user_id);

        if ($a_filters && $a_filters["registered"] && !$data["registered"]) {
            return null;
        }

        $data['registered'] = $data['registered'] ?
            $this->lng->txt('yes') :
            $this->lng->txt('no');
        $data['participated'] = $data['participated'] ?
            $this->lng->txt('yes') :
            $this->lng->txt('no');

        return $data;
    }

    public function eventsListObject(): void
    {
        $ilAccess = $this->access;
        $ilUser = $this->user;
        $tree = $this->tree;

        if (!$ilAccess->checkAccess('manage_members', '', $this->object->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilErr->MESSAGE);
        }

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.sess_list.html', 'Modules/Session');
        $this->__showButton($this->ctrl->getLinkTarget($this, 'exportCSV'), $this->lng->txt('event_csv_export'));

        $this->tpl->addBlockFile("EVENTS_TABLE", "events_table", "tpl.table.html");
        $this->tpl->addBlockFile('TBL_CONTENT', 'tbl_content', 'tpl.sess_list_row.html', 'Modules/Session');

        $members_obj = $this->initContainer(true);
        $members = $members_obj->getParticipants();
        $members = ilUtil::_sortIds($members, 'usr_data', 'lastname', 'usr_id');

        // Table
        $tbl = new ilTableGUI();
        $tbl->setTitle(
            $this->lng->txt("event_overview"),
            'icon_usr.svg',
            $this->lng->txt('obj_usr')
        );
        $this->ctrl->setParameter($this, 'offset', $this->requested_offset);

        $course_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs');
        $events = [];
        foreach ($tree->getSubTree($tree->getNodeData($course_ref_id), false, ['sess']) as $event_id) {
            $tmp_event = ilObjectFactory::getInstanceByRefId($event_id, false);
            if (!is_object($tmp_event) || $tmp_event->getType() != 'sess') {
                continue;
            }
            $events[] = $tmp_event;
        }

        $headerNames = [];
        $headerVars = [];
        $colWidth = [];

        $headerNames[] = $this->lng->txt('name');
        $headerVars[] = "name";
        $colWidth[] = '20%';

        for ($i = 1; $i <= count($events); $i++) {
            $headerNames[] = $i;
            $headerVars[] = "event_" . $i;
            $colWidth[] = 80 / count($events) . "%";
        }

        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $tbl->setHeaderNames($headerNames);
        $tbl->setHeaderVars($headerVars, $this->ctrl->getParameterArray($this));
        $tbl->setColumnWidth($colWidth);

        $tbl->setOrderColumn($this->requested_sort_by);
        $tbl->setOrderDirection($this->requested_sort_order);
        $tbl->setOffset($this->requested_offset);
        $tbl->setLimit((int) $ilUser->getPref("hits_per_page"));
        $tbl->setMaxCount(count($members));
        $tbl->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));

        $sliced_users = array_slice($members, $this->requested_offset, ilSession::get("tbl_limit"));
        $tbl->disable('sort');
        $tbl->render();

        $counter = 0;
        foreach ($sliced_users as $user_id) {
            foreach ($events as $event_obj) {
                $this->tpl->setCurrentBlock("eventcols");

                $event_part = new ilEventParticipants($this->object->getId());

                {
                    $this->tpl->setVariable("IMAGE_PARTICIPATED", $event_part->hasParticipated($user_id) ?
                                            ilUtil::getImagePath('icon_ok.svg') :
                                            ilUtil::getImagePath('icon_not_ok.svg'));

                    $this->tpl->setVariable("PARTICIPATED", $event_part->hasParticipated($user_id) ?
                                        $this->lng->txt('event_participated') :
                                        $this->lng->txt('event_not_participated'));
                }

                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("tbl_content");
            $name = ilObjUser::_lookupName($user_id);
            $this->tpl->setVariable("LASTNAME", $name['lastname']);
            $this->tpl->setVariable("FIRSTNAME", $name['firstname']);
            $this->tpl->setVariable("LOGIN", ilObjUser::_lookupLogin($user_id));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("HEAD_TXT_LEGEND", $this->lng->txt("legend"));
        $this->tpl->setVariable("HEAD_TXT_DIGIT", $this->lng->txt("event_digit"));
        $this->tpl->setVariable("HEAD_TXT_EVENT", $this->lng->txt("event"));
        $this->tpl->setVariable("HEAD_TXT_LOCATION", $this->lng->txt("event_location"));
        $this->tpl->setVariable("HEAD_TXT_DATE_TIME", $this->lng->txt("event_date_time"));
        $i = 1;
        foreach ($events as $event_obj) {
            $this->tpl->setCurrentBlock("legend_loop");
            $this->tpl->setVariable("LEGEND_DIGIT", $i++);
            $this->tpl->setVariable("LEGEND_EVENT_TITLE", $event_obj->getTitle());
            $this->tpl->setVariable("LEGEND_EVENT_DESCRIPTION", $event_obj->getDescription());
            $this->tpl->setVariable("LEGEND_EVENT_LOCATION", $event_obj->getLocation());
            $this->tpl->setVariable("LEGEND_EVENT_APPOINTMENT", $event_obj->getFirstAppointment()->appointmentToString());
            $this->tpl->parseCurrentBlock();
        }
    }

    protected function __showButton(string $cmd, string $text, string $target = ''): void
    {
        $this->toolbar->addButton($text, $this->ctrl->getLinkTarget($this, $cmd), $target);
    }

    protected function initForm(string $a_mode): bool
    {
        if ($this->form instanceof ilPropertyFormGUI) {
            return true;
        }
        $ilUser = $this->user;
        $object_service = $this->object_service;
        $this->lng->loadLanguageModule('dateplaner');

        ilYuiUtil::initDomEvent();

        $this->form = new ilPropertyFormGUI();
        $this->form->setMultipart(true);
        $this->form->setTableWidth('600px');
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setMultipart(true);

        $this->form = $this->initDidacticTemplate($this->form);

        $this->lng->loadLanguageModule('dateplaner');
        $dur = new ilDateDurationInputGUI($this->lng->txt('cal_fullday'), 'event');
        $dur->setRequired(true);
        $dur->enableToggleFullTime(
            $this->lng->txt('event_fulltime_info'),
            $this->object->getFirstAppointment()->enabledFulltime()
        );
        $dur->setShowTime(true);
        $dur->setStart($this->object->getFirstAppointment()->getStart());
        $dur->setEnd($this->object->getFirstAppointment()->getEnd());

        $this->form->addItem($dur);


        // Recurrence
        if ($a_mode == 'create') {
            if (!is_object($this->rec)) {
                $this->rec = new ilEventRecurrence();
            }
            $rec = new ilRecurrenceInputGUI($this->lng->txt('cal_recurrences'), 'frequence');
            $rec->allowUnlimitedRecurrences(false);
            $rec->setRecurrence($this->rec);
            $this->form->addItem($rec);

            // #14547
            $lp = new ilCheckboxInputGUI($this->lng->txt("sess_lp_preset"), "lp_preset");
            $lp->setInfo($this->lng->txt("sess_lp_preset_info"));
            $lp->setChecked(true);
            $this->form->addItem($lp);
        }

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('event_section_information'));
        $this->form->addItem($section);

        // title
        $title = new ilTextInputGUI($this->lng->txt('event_title'), 'title');
        $title->setRequired(true);
        $title->setValue($this->object->getTitle());
        $title->setSize(50);
        $title->setMaxLength(70);
        $this->form->addItem($title);

        // desc
        $desc = new ilTextAreaInputGUI($this->lng->txt('event_desc'), 'desc');
        $desc->setValue($this->object->getLongDescription());
        $desc->setRows(4);
        $desc->setCols(50);
        $this->form->addItem($desc);

        // location
        $desc = new ilTextAreaInputGUI($this->lng->txt('event_location'), 'location');
        $desc->setValue($this->object->getLocation());
        $desc->setRows(4);
        $desc->setCols(50);
        $this->form->addItem($desc);

        // workflow
        $details = new ilTextAreaInputGUI($this->lng->txt('event_details_workflow'), 'details');
        $details->setValue($this->object->getDetails());
        $details->setCols(50);
        $details->setRows(4);
        $this->form->addItem($details);

        if ($a_mode == 'create') {
            $this->record_gui = new ilAdvancedMDRecordGUI(
                ilAdvancedMDRecordGUI::MODE_EDITOR,
                'sess'
            );
            $this->record_gui->setRefId($this->requested_ref_id);
            $this->record_gui->setPropertyForm($this->form);
            $this->record_gui->parse();
        } elseif ($this->checkPermissionBool('edit_metadata')) {
            $this->record_gui = new ilAdvancedMDRecordGUI(
                ilAdvancedMDRecordGUI::MODE_EDITOR,
                'sess',
                $this->object->getId()
            );
            $this->record_gui->setPropertyForm($this->form);
            $this->record_gui->parse();
        }


        // section
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('event_tutor_data'));
        $this->form->addItem($section);

        // name
        $tutor_name = new ilTextInputGUI($this->lng->txt('tutor_name'), 'tutor_name');
        $tutor_name->setValue($this->object->getName());
        $tutor_name->setSize(20);
        $tutor_name->setMaxLength(70);
        $this->form->addItem($tutor_name);

        // email
        $tutor_email = new ilTextInputGUI($this->lng->txt('tutor_email'), 'tutor_email');
        $tutor_email->setValue($this->object->getEmail());
        $tutor_email->setSize(20);
        $tutor_email->setMaxLength(70);
        $this->form->addItem($tutor_email);

        // phone
        $tutor_phone = new ilTextInputGUI($this->lng->txt('tutor_phone'), 'tutor_phone');
        $tutor_phone->setValue($this->object->getPhone());
        $tutor_phone->setSize(20);
        $tutor_phone->setMaxLength(70);
        $this->form->addItem($tutor_phone);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('sess_section_reg'));
        $this->form->addItem($section);

        $reg_settings = new ilSessionMembershipRegistrationSettingsGUI(
            $this,
            $this->object,
            array(
                    ilMembershipRegistrationSettings::TYPE_DIRECT,
                    ilMembershipRegistrationSettings::TYPE_REQUEST,
                    ilMembershipRegistrationSettings::TYPE_TUTOR,
                    ilMembershipRegistrationSettings::TYPE_NONE,
                    ilMembershipRegistrationSettings::REGISTRATION_LIMITED_USERS
                )
        );
        $reg_settings->addMembershipFormElements($this->form, '');


        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('event_assign_files'));
        $this->form->addItem($section);

        $files = new ilFileWizardInputGUI($this->lng->txt('objs_file'), 'files');
        $files->setFilenames(array(0 => ''));
        $this->form->addItem($files);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('sess_setting_header_presentation'));
        $this->form->addItem($section);

        $object_service->commonSettings()->legacyForm($this->form, $this->object)->addTileImage();

        $features = new ilFormSectionHeaderGUI();
        $features->setTitle($this->lng->txt('obj_features'));
        $this->form->addItem($features);
        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $this->form,
            array(
                    ilObjectServiceSettingsGUI::CUSTOM_METADATA
                )
        );

        $gallery = new ilCheckboxInputGUI($this->lng->txt('sess_show_members'), 'show_members');
        $gallery->setChecked($this->object->getShowMembers());
        $gallery->setInfo($this->lng->txt('sess_show_participants_info'));
        $this->form->addItem($gallery);


        // Show mail to members type
        $mail_type = new ilRadioGroupInputGUI($this->lng->txt('sess_mail_type'), 'mail_type');
        $mail_type->setValue((string) $this->object->getMailToMembersType());

        $mail_tutors = new ilRadioOption(
            $this->lng->txt('sess_mail_admins_only'),
            (string) ilObjSession::MAIL_ALLOWED_ADMIN,
            $this->lng->txt('sess_mail_admins_only_info')
        );
        $mail_type->addOption($mail_tutors);

        $mail_all = new ilRadioOption(
            $this->lng->txt('sess_mail_all'),
            (string) ilObjSession::MAIL_ALLOWED_ALL,
            $this->lng->txt('sess_mail_all_info')
        );
        $mail_type->addOption($mail_all);
        $this->form->addItem($mail_type);



        switch ($a_mode) {
            case 'create':
                $this->form->setTitle($this->lng->txt('event_table_create'));

                $this->form->addCommandButton('save', $this->lng->txt('event_btn_add'));
                $this->form->addCommandButton('saveAndAssignMaterials', $this->lng->txt('event_btn_add_edit'));
                $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));

                return true;

            case 'edit':
                $this->form->setTitle($this->lng->txt('event_table_update'));

                $this->form->addCommandButton('update', $this->lng->txt('save'));
                $this->form->addCommandButton('cancelEdit', $this->lng->txt('cancel'));

                return true;
        }
        return true;
    }

    protected function load(): void
    {
        $event = $this->form->getItemByPostVar('event');
        if ($event->getStart() && $event->getEnd()) {
            $this->object->getFirstAppointment()->setStartingTime($event->getStart()->get(IL_CAL_UNIX));
            $this->object->getFirstAppointment()->setEndingTime($event->getStart()->get(IL_CAL_UNIX));
            $this->object->getFirstAppointment()->setStart($event->getStart());
            $this->object->getFirstAppointment()->setEnd($event->getEnd());
            $this->object->getFirstAppointment()->toggleFulltime($event->getStart() instanceof ilDate);
        }

        $this->object->setTitle($this->form->getInput('title'));
        $this->object->setDescription($this->form->getInput('desc'));
        $this->object->setLocation($this->form->getInput('location'));
        $this->object->setName($this->form->getInput('tutor_name'));
        $this->object->setPhone($this->form->getInput('tutor_phone'));
        $this->object->setEmail($this->form->getInput('tutor_email'));
        $this->object->setDetails($this->form->getInput('details'));

        $this->object->setRegistrationNotificationEnabled((bool) $this->form->getInput('registration_notification'));
        $this->object->setRegistrationNotificationOption($this->form->getInput('notification_option'));

        $this->object->setRegistrationType((int) $this->form->getInput('registration_type'));

        switch ($this->object->getRegistrationType()) {
            case ilMembershipRegistrationSettings::TYPE_DIRECT:
                $this->object->enableCannotParticipateOption((bool) $this->form->getInput('show_cannot_participate_direct'));
                break;
            case ilMembershipRegistrationSettings::TYPE_REQUEST:
                $this->object->enableCannotParticipateOption((bool) $this->form->getInput('show_cannot_participate_request'));
                break;
            default:
                $this->object->enableCannotParticipateOption(false);
                break;
        }

        // $this->object->setRegistrationMinUsers((int) $this->form->getInput('registration_min_members'));
        $this->object->setRegistrationMaxUsers((int) $this->form->getInput('registration_max_members'));
        $this->object->enableRegistrationUserLimit((int) $this->form->getInput('registration_membership_limited'));
        $this->object->setShowMembers((bool) $this->form->getInput('show_members'));
        $this->object->setMailToMembersType((int) $this->form->getInput('mail_type'));

        switch ((int) $this->form->getInput('waiting_list')) {
            case 2:
                $this->object->enableRegistrationWaitingList(true);
                $this->object->setWaitingListAutoFill(true);
                break;

            case 1:
                $this->object->enableRegistrationWaitingList(true);
                $this->object->setWaitingListAutoFill(false);
                break;

            default:
                $this->object->enableRegistrationWaitingList(false);
                $this->object->setWaitingListAutoFill(false);
                break;
        }
    }

    protected function loadRecurrenceSettings(): void
    {
        $this->rec = new ilEventRecurrence();

        switch ($this->form->getInput('frequence')) {
            case ilCalendarRecurrence::FREQ_DAILY:
                $this->rec->setFrequenceType($this->form->getInput('frequence'));
                $this->rec->setInterval((int) $this->form->getInput('count_DAILY'));
                break;

            case ilCalendarRecurrence::FREQ_WEEKLY:
                $this->rec->setFrequenceType($this->form->getInput('frequence'));
                $this->rec->setInterval((int) $this->form->getInput('count_WEEKLY'));
                if (is_array($this->form->getInput('byday_WEEKLY'))) {
                    $this->rec->setBYDAY(ilUtil::stripSlashes(implode(',', $this->form->getInput('byday_WEEKLY'))));
                }
                break;

            case ilCalendarRecurrence::FREQ_MONTHLY:
                $this->rec->setFrequenceType($this->form->getInput('frequence'));
                $this->rec->setInterval((int) $this->form->getInput('count_MONTHLY'));
                switch ((int) $this->form->getInput('subtype_MONTHLY')) {
                    case 0:
                        // nothing to do;
                        break;

                    case 1:
                        switch ((int) $this->form->getInput('monthly_byday_day')) {
                            case 8:
                                // Weekday
                                $this->rec->setBYSETPOS($this->form->getInput('monthly_byday_num'));
                                $this->rec->setBYDAY('MO,TU,WE,TH,FR');
                                break;

                            case 9:
                                // Day of month
                                $this->rec->setBYMONTHDAY($this->form->getInput('monthly_byday_num'));
                                break;

                            default:
                                $this->rec->setBYDAY(($this->form->getInput('monthly_byday_num') . $this->form->getInput('monthly_byday_day')));
                                break;
                        }
                        break;

                    case 2:
                        $this->rec->setBYMONTHDAY($this->form->getInput('monthly_bymonthday'));
                        break;
                }
                break;

            case ilCalendarRecurrence::FREQ_YEARLY:
                $this->rec->setFrequenceType($this->form->getInput('frequence'));
                $this->rec->setInterval((int) $this->form->getInput('count_YEARLY'));
                switch ((int) $this->form->getInput('subtype_YEARLY')) {
                    case 0:
                        // nothing to do;
                        break;

                    case 1:
                        $this->rec->setBYMONTH($this->form->getInput('yearly_bymonth_byday'));
                        $this->rec->setBYDAY(($this->form->getInput('yearly_byday_num') . $this->form->getInput('yearly_byday')));
                        break;

                    case 2:
                        $this->rec->setBYMONTH($this->form->getInput('yearly_bymonth_by_monthday'));
                        $this->rec->setBYMONTHDAY($this->form->getInput('yearly_bymonthday'));
                        break;
                }
                break;
        }

        // UNTIL
        switch ((int) $this->form->getInput('until_type')) {
            case 1:
                $this->rec->setFrequenceUntilDate(null);
                // nothing to do
                break;

            case 2:
                $this->rec->setFrequenceUntilDate(null);
                $this->rec->setFrequenceUntilCount((int) $this->form->getInput('count'));
                break;

            case 3:
                $frequence = $this->form->getItemByPostVar('frequence');
                $end = $frequence->getRecurrence()->getFrequenceUntilDate();
                $this->rec->setFrequenceUntilCount(0);
                $this->rec->setFrequenceUntilDate($end);
                break;
        }
    }

    protected function __toUnix(array $date, array $time): int
    {
        return mktime($time['h'], $time['m'], 0, $date['m'], $date['d'], $date['y']);
    }

    protected function addLocatorItems(): void
    {
        $ilLocator = $this->locator;

        if (!$this->getCreationMode()) {
            // see prepareOutput()
            $title = strlen($this->object->getTitle()) ? (': ' . $this->object->getTitle()) : '';
            $title = $this->object->getFirstAppointment()->appointmentToString() . $title;

            $ilLocator->addItem($title, $this->ctrl->getLinkTarget($this, "infoScreen"), "", $this->requested_ref_id);
        }
    }

    protected function redirectToParentContentPageObject(): void
    {
        $objDefinition = $this->objDefinition;
        $tree = $this->tree;
        $ctrl = $this->ctrl;

        $parent_id = $tree->getParentId($this->object->getRefId());

        // #11650
        $parent_type = ilObject::_lookupType($parent_id, true);

        $parent_class = $objDefinition->getClassName($parent_type);
        $parent_class = 'ilObj' . $parent_class . 'GUI';

        $ctrl->setParameterByClass($parent_class, "ref_id", $parent_id);
        $ctrl->redirectByClass($parent_class, "view");
    }

    protected function getTabs(): void
    {
        $ilAccess = $this->access;
        $ilTabs = $this->tabs_gui;
        $tree = $this->tree;
        $ilCtrl = $this->ctrl;
        $ilHelp = $this->help;
        $ilUser = $this->user;

        $ilHelp->setScreenIdComponent("sess");

        $parent_id = $tree->getParentId($this->object->getRefId());

        // #11650
        $parent_type = ilObject::_lookupType($parent_id, true);

        $this->tabs_gui->setBackTarget(
            $this->lng->txt('back_to_' . $parent_type . '_content'),
            $ilCtrl->getLinkTarget($this, "redirectToParentContentPage")
        );

        $this->tabs_gui->addTarget(
            'info_short',
            $this->ctrl->getLinkTarget($this, 'infoScreen')
        );

        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'edit')
            );
        }
        if ($ilAccess->checkAccess('manage_materials', '', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'materials',
                $this->lng->txt('crs_materials'),
                $this->ctrl->getLinkTarget($this, 'materials')
            );
        }

        // booking
        $parent_id = $tree->getParentId($this->requested_ref_id);

        if ($ilAccess->checkAccess('write', '', $this->ref_id) && ilContainer::_lookupContainerSetting(
            ilObject::_lookupObjId($parent_id),
            ilObjectServiceSettingsGUI::BOOKING
        )) {
            $this->tabs_gui->addTarget(
                "obj_tool_setting_booking",
                $this->ctrl->getLinkTargetByClass(array("ilbookinggatewaygui"), "")
            );
        }

        // member tab
        $is_participant = $this->object->getMembersObject()->isAssigned($ilUser->getId());
        $membership_gui = new ilSessionMembershipGUI($this, $this->object);
        $membership_gui->addMemberTab($this->tabs_gui, $is_participant);


        // learning progress
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'learning_progress',
                $this->ctrl->getLinkTargetByClass(array('ilobjsessiongui','illearningprogressgui'), ''),
                '',
                array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui')
            );
        }

        // meta data
        if ($ilAccess->checkAccess('edit_metadata', '', $this->ref_id)) {
            $mdgui = new ilObjectMetaDataGUI($this->object);
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTab(
                    "metadata",
                    $this->lng->txt('meta_data'),
                    $mdtab
                );
            }
        }

        // export
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "export",
                $this->ctrl->getLinkTargetByClass("ilexportgui", ""),
                "",
                "ilexportgui"
            );
        }


        // edit permissions
        if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }

    /**
     * Custom callback after object is created (in parent container)
     */
    public function afterSaveCallback(ilObject $a_obj): void
    {
        // add new object to materials
        $event_items = new ilEventItems($this->object->getId());
        $event_items->addItem($a_obj->getRefId());
        $event_items->update();
    }

    /**
     * Used for waiting list
     */
    public function readMemberData(array $a_usr_ids): array
    {
        $tmp_data = [];
        foreach ($a_usr_ids as $usr_id) {
            $tmp_data[$usr_id] = [];
        }
        return $tmp_data;
    }

    public function getContainerRefId(): int
    {
        if (!$this->container_ref_id) {
            $this->initContainer();
        }
        return $this->container_ref_id;
    }

    protected function cancelEditObject(): void
    {
        $ilCtrl = $this->ctrl;
        $tree = $this->tree;

        $parent_id = $tree->getParentId($this->requested_ref_id);

        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $parent_id);

        $ilCtrl->redirectByClass("ilrepositorygui", "");
    }

    public function getDefaultMemberRole(): int
    {
        $rbac_review = $this->rbacreview;

        $local_roles = $rbac_review->getRolesOfRoleFolder($this->object->getRefId(), false);

        foreach ($local_roles as $role_id) {
            $title = ilObject::_lookupTitle($role_id);
            if (substr($title, 0, 19) == 'il_sess_participant') {
                return $role_id;
            }
        }
        return 0;
    }

    /**
     * @return int[]
     */
    public function getLocalRoles(): array
    {
        $rbac_review = $this->rbacreview;

        return $this->rbacreview->getRolesOfRoleFolder($this->object->getRefId(), false);
    }

    public function createMailSignature(): string
    {
        $link = chr(13) . chr(10) . chr(13) . chr(10);
        $link .= $this->lng->txt('sess_mail_permanent_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        $link .= ilLink::_getLink($this->object->getRefId());
        return rawurlencode(base64_encode($link));
    }
}
