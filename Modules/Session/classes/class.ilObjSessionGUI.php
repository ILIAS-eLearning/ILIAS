<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Object/classes/class.ilObjectGUI.php');
include_once('./Modules/Session/classes/class.ilObjSession.php');
include_once('./Modules/Session/classes/class.ilSessionFile.php');

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
    /**
     * @var ilLogger
     */
    protected $logger = null;


    public $lng;
    public $ctrl;
    public $tpl;
    
    protected $container_ref_id = 0;
    protected $container_obj_id = 0;
    
    protected $files = array();

    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        
        $this->type = "sess";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        
        $this->lng = $lng;
        $this->lng->loadLanguageModule("event");
        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('trac');
        $this->lng->loadLanguageModule('sess');

        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        
        $this->logger = $GLOBALS['DIC']->logger()->sess();
    }
    
    
    /**
     * execute command
     *
     * @access public
     * @return
     */
    public function executeCommand()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];
  
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        if (
            !$this->getCreationMode() &&
            $GLOBALS['DIC']->access()->checkAccess('read', '', $_GET['ref_id'])
        ) {
            $GLOBALS['DIC']['ilNavigationHistory']->addItem(
                (int) $_GET['ref_id'],
                ilLink::_getLink((int) $_GET['ref_id'], 'sess'),
                'sess'
            );
        }

        $this->prepareOutput();
        switch ($next_class) {
            case 'ilsessionmembershipgui':
                $this->tabs_gui->activateTab('members');
                include_once './Modules/Session/classes/class.ilSessionMembershipGUI.php';
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
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;
        
            case 'ilobjectcopygui':
                include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('sess');
                $this->ctrl->forwardCommand($cp);
                break;
                
            case "ilexportgui":
//				$this->prepareOutput();
                $this->tabs_gui->setTabActive("export");
                include_once("./Services/Export/classes/class.ilExportGUI.php");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $ret = $this->ctrl->forwardCommand($exp_gui);
//				$this->tpl->show();
                break;

            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            
            case 'ilmembershipgui':
                $this->ctrl->setReturn($this, 'members');
                include_once './Services/Membership/classes/class.ilMembershipMailGUI.php';
                $mem = new ilMembershipMailGUI($this);
                $this->ctrl->forwardCommand($mem);
                break;
            
            case "illearningprogressgui":
                include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId()
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
                }
                $ilCtrl->forwardCommand($form);
                break;

            case "ilbookinggatewaygui":
                $tree = $DIC['tree'];
                $parent_id = $tree->getParentId((int) $_REQUEST['ref_id']);

                $this->tabs_gui->activateTab('obj_tool_setting_booking');
                $gui = new ilBookingGatewayGUI($this, $parent_id);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if ($cmd == "applyFilter") {
                    $cmd == "applyFilter";
                    $this->$cmd();
                } elseif ($cmd == "resetFilter") {
                    $cmd == "resetFilter";
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
        
        return true;
    }


    /**
     * Redirect to member adminsitration
     */
    protected function membersObject()
    {
        $this->ctrl->redirectByClass('ilSessionMembershipGUI', 'participants');
    }
    
    /**
     * Get session object
     * @return ilObjSession
     */
    public function getCurrentObject()
    {
        return $this->object;
    }
    
    /**
     * @see ilObjectGUI::prepareOutput()
     */
    public function prepareOutput($a_show_subobjects = true)
    {
        parent::prepareOutput($a_show_subobjects);
        
        if (!$this->getCreationMode()) {
            $title = strlen($this->object->getTitle()) ? (': ' . $this->object->getTitle()) : '';
            
            include_once './Modules/Session/classes/class.ilSessionAppointment.php';
            $this->tpl->setTitle(
                $this->object->getFirstAppointment()->appointmentToString() . $title
            );
        }
    }

    /**
     * register to session
     *
     * @access public
     * @param
     * @return
     */
    public function registerObject()
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        $this->checkPermission('visible');
        
        include_once './Services/Membership/classes/class.ilParticipants.php';
        $part = ilParticipants::getInstance($this->getCurrentObject()->getRefId());

        include_once './Modules/Session/classes/class.ilEventParticipants.php';
        $event_part = new ilEventParticipants($this->getCurrentObject()->getId());
        $event_part->updateExcusedForUser($ilUser->getId(), false);

        if (
            $this->getCurrentObject()->isRegistrationUserLimitEnabled() and
            $this->getCurrentObject()->getRegistrationMaxUsers() and
            (count($event_part->getRegistered()) >= $this->getCurrentObject()->getRegistrationMaxUsers())
        ) {
            include_once './Modules/Session/classes/class.ilSessionWaitingList.php';
            $wait = new ilSessionWaitingList($this->getCurrentObject()->getId());
            $wait->addToList($ilUser->getId());
            ilUtil::sendInfo($this->lng->txt('sess_reg_added_to_wl'), true);
            $this->ctrl->redirect($this, 'infoScreen');
            return true;
        }
        
        
        switch ($this->getCurrentObject()->getRegistrationType()) {
            case ilMembershipRegistrationSettings::TYPE_NONE:
                $this->ctrl->redirect($this, 'info');
                break;
            
            case ilMembershipRegistrationSettings::TYPE_DIRECT:
                $part->register($ilUser->getId());
                ilUtil::sendSuccess($this->lng->txt('event_registered'), true);

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
                ilUtil::sendSuccess($this->lng->txt('sess_registered_confirm'), true);
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
    
    /**
     * Called from info screen
     * @return
     */
    public function joinObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        $this->checkPermission('read');

        if ($ilUser->isAnonymous()) {
            $this->ctrl->redirect($this, 'infoScreen');
        }
        
        include_once './Modules/Session/classes/class.ilEventParticipants.php';
            
        if (ilEventParticipants::_isRegistered($ilUser->getId(), $this->object->getId())) {
            $_SESSION['sess_hide_info'] = true;
            ilEventParticipants::_unregister($ilUser->getId(), $this->object->getId());
            ilUtil::sendSuccess($this->lng->txt('event_unregistered'), true);
        } else {
            ilEventParticipants::_register($ilUser->getId(), $this->object->getId());
            ilUtil::sendSuccess($this->lng->txt('event_registered'), true);
        }
        
        $this->ctrl->redirect($this, 'infoScreen');
    }

    /**
     * unregister from session
     *
     * @access public
     * @param bool $a_refuse_participation
     * @return void
     */
    public function unregisterObject($a_refuse_participation = false)
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        include_once './Modules/Session/classes/class.ilSessionParticipants.php';
        $part = ilSessionParticipants::getInstance($this->object->getRefId());
        if ($part->isSubscriber($ilUser->getId())) {
            $part->deleteSubscriber($ilUser->getId());
        }
        
        $part->unregister($ilUser->getId());

        if ($a_refuse_participation) {
            $event_part = new \ilEventParticipants($this->object->getId());
            $event_part->updateExcusedForUser($ilUser->getId(), true);
        }


        include_once './Modules/Session/classes/class.ilSessionWaitingList.php';
        ilSessionWaitingList::deleteUserEntry($ilUser->getId(), $this->getCurrentObject()->getId());

        // check for visible permission of user
        ilRbacSystem::resetCaches();
        $GLOBALS['DIC']->access()->clear();
        $has_access = $GLOBALS['DIC']->access()->checkAccessOfUser(
            $GLOBALS['DIC']->user()->getId(),
            'visible',
            '',
            $this->object->getRefId()
        );
        if (!$has_access) {
            $parent = $GLOBALS['DIC']->repositoryTree()->getParentId($this->object->getRefId());
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
            \ilUtil::sendInfo($this->lng->txt('sess_participation_refused_info'), true);
        } else {
            ilUtil::sendSuccess($this->lng->txt('event_unregistered'), true);
        }
        $this->ctrl->returnToParent($this);
    }
    
    /**
     * goto
     *
     * @access public
     * @param
     * @return
     * @static
     */
    public static function _goto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();

        $ilCtrl = $DIC->ctrl();
        $parts = explode('_', $a_target);
        $a_target = $parts[0];

        if ($ilAccess->checkAccess('write', '', $a_target)) {
            if (isset($parts[1]) && 'part' === $parts[1]) {
                $ilCtrl->initBaseClass('ilRepositoryGUI');
                $ilCtrl->setParameterByClass('ilSessionMembershipGUI', 'ref_id', (int) $a_target);
                $ilCtrl->setTargetScript('ilias.php');
                $ilCtrl->redirectByClass(array('ilRepositoryGUI', __CLASS__, 'ilSessionMembershipGUI'));
            }
        }

        if ($ilAccess->checkAccess('visible', "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            ilUtil::sendFailure(
                sprintf(
                    $lng->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                ),
                true
            );
            ilObjectGUI::_gotoRepositoryRoot();
        }
    }
    

    
    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreenObject()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    /**
     * Modify Item ListGUI for presentation in container
     * @global type $tree
     * @param type $a_item_list_gui
     * @param type $a_item_data
     * @param type $a_show_path
     */
    public function modifyItemGUI($a_item_list_gui, $a_item_data, $a_show_path)
    {
        global $DIC;

        $tree = $DIC['tree'];

        // if folder is in a course, modify item list gui according to course requirements
        if ($course_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs')) {
            // #10611
            include_once "Services/Object/classes/class.ilObjectActivation.php";
            ilObjectActivation::addListGUIActivationProperty($a_item_list_gui, $a_item_data);
                        
            include_once("./Modules/Course/classes/class.ilObjCourse.php");
            include_once("./Modules/Course/classes/class.ilObjCourseGUI.php");
            $course_obj_id = ilObject::_lookupObjId($course_ref_id);
            ilObjCourseGUI::_modifyItemGUI(
                $a_item_list_gui,
                get_class($this),
                $a_item_data,
                $a_show_path,
                ilObjCourse::_lookupAboStatus($course_obj_id),
                $course_ref_id,
                $course_obj_id,
                $this->object->getRefId()
            );
        }
    }

    /**
     * @param $ilToolbar ilToolbarGUI
     * show join request
     * This method is only needed to keep showJoinRequestButton method protected.
     */
    public function showJoinRequestButtonInCalendar(ilToolbarGUI $a_ilToolbar)
    {
        $this->showJoinRequestButton($a_ilToolbar);
    }


    /**
     * refuse participation
     */
    protected function refuseParticipationObject()
    {
        return $this->unregisterObject(true);
    }
    
    /**
     * @param $ilToolbar ilToolbarGUI
     * show join request
     */
    protected function showJoinRequestButton(ilToolbarGUI $ilToolbar = null)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if (!$ilToolbar) {
            global $DIC;

            $ilToolbar = $DIC['ilToolbar'];
        }

        if (!$this->getCurrentObject()->enabledRegistrationForUsers() || $ilUser->isAnonymous()) {
            return false;
        }

        include_once './Modules/Session/classes/class.ilSessionWaitingList.php';
        
        include_once './Services/Membership/classes/class.ilParticipants.php';
        $part = ilParticipants::getInstance($this->getCurrentObject()->getRefId());
        
        include_once './Modules/Session/classes/class.ilEventParticipants.php';

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
            $this->getCurrentObject()->isRegistrationUserLimitEnabled() and
            $this->getCurrentObject()->getRegistrationMaxUsers() and
            (count($event_part->getRegistered()) >= $this->getCurrentObject()->getRegistrationMaxUsers())
        ) {
            if ($this->getCurrentObject()->isRegistrationWaitingListEnabled()) {
                ilUtil::sendInfo($this->lng->txt('sess_reg_max_users_exceeded_wl'));
                $btn_attend->setCaption($this->lng->txt("mem_add_to_wl"), false);
                $btn_attend->setUrl($this->ctrl->getLinkTargetByClass(array("ilRepositoryGUI", "ilObjSessionGUI"), "register"));
                $ilToolbar->addButtonInstance($btn_attend);
                if (!$event_part->isExcused($ilUser->getId()) && !is_null($btn_excused)) {
                    $ilToolbar->addButtonInstance($btn_excused);
                }
                return true;
            } else {
                ilUtil::sendInfo($this->lng->txt('sess_reg_max_users_exceeded'));
                return true;
            }
        } else {
            if (!isset($_SESSION['sess_hide_info'])) {
                ilUtil::sendInfo($this->lng->txt('sess_join_info'));
                $btn_attend->setCaption($this->lng->txt("join_session"), false);
                $btn_attend->setUrl($this->ctrl->getLinkTargetByClass(array("ilRepositoryGUI", "ilObjSessionGUI"), "register"));
                $ilToolbar->addButtonInstance($btn_attend);
                if (!$event_part->isExcused($ilUser->getId()) && !is_null($btn_excused)) {
                    $ilToolbar->addButtonInstance($btn_excused);
                }
                return true;
            }
        }
    }
        
        

    /**
     * info screen
     *
     * @access protected
     * @param
     * @return
     */
    public function infoScreen()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];
        $tree = $DIC['tree'];
        $ilToolbar = $DIC['ilToolbar'];
        $lng = $DIC['lng'];

        $this->checkPermission('visible');
        $this->tabs_gui->setTabActive('info_short');

        $this->showJoinRequestButton();

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
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
            $list_gui = ilSessionObjectListGUIFactory::factory($item['type']);
            $list_gui->setContainerObject($this);
            
            $this->modifyItemGUI($list_gui, $item, false);
            
            $html .= $list_gui->getListItemHTML(
                $item['ref_id'],
                $item['obj_id'],
                $item['title'],
                $item['description']
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
        if (strlen($this->object->getLocation()) or strlen($this->object->getDetails())) {
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

        $record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'sess', $this->object->getId());
        $record_gui->setInfoObject($info);
        $record_gui->parse();
        
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
        require_once 'Services/Tracking/classes/class.ilChangeEvent.php';
        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $ilUser->getId()
        );
    }
    
    /**
     * send file
     *
     * @access public
     */
    public function sendFileObject()
    {
        $file = new ilSessionFile((int) $_GET['file_id']);
        
        ilUtil::deliverFile($file->getAbsolutePath(), $file->getFileName(), $file->getFileType());
        return true;
    }
    
    protected function initCreateForm($a_new_type)
    {
        if (!is_object($this->object)) {
            $this->object = new ilObjSession();
        }
        return $this->initForm('create');
    }

    /**
     * Save and assign sessoin materials
     *
     * @access protected
     */
    public function saveAndAssignMaterialsObject()
    {
        $this->saveObject(false);
        
        $this->ctrl->setParameter($this, 'ref_id', $this->object->getRefId());
        $this->ctrl->setParameter($this, 'new_type', '');
        $this->ctrl->redirect($this, 'materials');
    }
        
    /**
     * save object
     *
     * @access protected
     * @param bool	$a_redirect_on_success	Redirect to repository after success.
     * @return
     */
    public function saveObject($a_redirect_on_success = true)
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilUser = $DIC['ilUser'];
        
        $this->object = new ilObjSession();
        
        $this->ctrl->saveParameter($this, "new_type");
        
        $form = $this->initForm('create');
        $ilErr->setMessage('');
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $ilErr->setMessage($this->lng->txt('err_check_input'));
            $this->createObject($form);
            return false;
        }

        if (
            $this->record_gui instanceof \ilAdvancedMDRecordGUI &&
            !$this->record_gui->importEditFormPostValues()
        ) {
            $ilErr->setMessage($this->lng->txt('err_check_input'));
        }
        
        $this->load();
        $this->loadRecurrenceSettings();
                
        $this->object->validate();
        $this->object->getFirstAppointment()->validate();

        if (strlen($ilErr->getMessage())) {
            ilUtil::sendFailure($ilErr->getMessage());
            $this->form->setValuesByPost();
            $this->createObject();
            return false;
        }
        // Create session
        $this->object->create();
        $this->object->createReference();
        $this->object->putInTree($_GET["ref_id"]);
        $this->object->setPermissions($_GET["ref_id"]);
        
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
            include_once "Services/Tracking/classes/class.ilLPObjSettings.php";
            $lp_obj_settings = new ilLPObjSettings($this->object->getId());
            $lp_obj_settings->setMode(ilLPObjSettings::LP_MODE_DEACTIVATED);
            $lp_obj_settings->update(false);
        }
        
        // create appointment
        $this->object->getFirstAppointment()->setSessionId($this->object->getId());
        $this->object->getFirstAppointment()->create();

        $this->handleFileUpload();

        $DIC->object()->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();
        
        $this->createRecurringSessions($this->form->getInput("lp_preset"));

        if ($a_redirect_on_success) {
            ilUtil::sendInfo($this->lng->txt('event_add_new_event'), true);
            $this->ctrl->returnToParent($this);
        }
        
        return true;
    }
    
    public function handleFileUpload()
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        include_once './Modules/Session/classes/class.ilEventItems.php';
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
            
            include_once './Modules/File/classes/class.ilObjFile.php';
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
            $file->createDirectory();

            $upload = $DIC->upload();
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
    
    
    
    /**
     * create recurring sessions
     *
     * @access protected
     * @param bool $a_activate_lp
     * @return
     */
    protected function createRecurringSessions($a_activate_lp = true)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        if (!$this->rec->getFrequenceType()) {
            return true;
        }
        include_once('./Services/Calendar/classes/class.ilCalendarRecurrenceCalculator.php');
        $calc = new ilCalendarRecurrenceCalculator($this->object->getFirstAppointment(), $this->rec);
        
        $period_start = clone $this->object->getFirstAppointment()->getStart();
        
        
        $period_end = clone $this->object->getFirstAppointment()->getStart();
        $period_end->increment(IL_CAL_YEAR, 5);
        $date_list = $calc->calculateDateList($period_start, $period_end);
        
        $period_diff = $this->object->getFirstAppointment()->getEnd()->get(IL_CAL_UNIX) -
            $this->object->getFirstAppointment()->getStart()->get(IL_CAL_UNIX);
        $parent_id = $tree->getParentId($this->object->getRefId());
        
        include_once './Modules/Session/classes/class.ilEventItems.php';
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
                include_once "Services/Tracking/classes/class.ilLPObjSettings.php";
                $lp_obj_settings = new ilLPObjSettings($new_obj->getId());
                $lp_obj_settings->setMode(ilLPObjSettings::LP_MODE_DEACTIVATED);
                $lp_obj_settings->update(false);
            }
            
            $new_evi = new ilEventItems($new_obj->getId());
            $new_evi->setItems($eitems);
            $new_evi->update();
        }
    }
    
    
    /**
     * edit object
     *
     * @access protected
     * @param
     * @return
     */
    public function editObject()
    {
        $this->tabs_gui->setTabActive('settings');
        
        $this->initForm('edit');
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.sess_edit.html', 'Modules/Session');
        $this->tpl->setVariable('EVENT_EDIT_TABLE', $this->form->getHTML());
        
        if (!count($this->object->getFiles())) {
            return true;
        }
        $rows = array();
        foreach ($this->object->getFiles() as $file) {
            $table_data['id'] = $file->getFileId();
            $table_data['filename'] = $file->getFileName();
            $table_data['filetype'] = $file->getFileType();
            $table_data['filesize'] = $file->getFileSize();
            
            $rows[] = $table_data;
        }
        
        include_once("./Modules/Session/classes/class.ilSessionFileTableGUI.php");
        $table_gui = new ilSessionFileTableGUI($this, "edit");
        $table_gui->setTitle($this->lng->txt("event_files"));
        $table_gui->setData($rows);
        $table_gui->addCommandButton("cancel", $this->lng->txt("cancel"));
        $table_gui->addMultiCommand("confirmDeleteFiles", $this->lng->txt("delete"));
        $table_gui->setSelectAllCheckbox("file_id");
        $this->tpl->setVariable('EVENT_FILE_TABLE', $table_gui->getHTML());

        return true;
    }
    
    /**
     * update object
     *
     * @access protected
     * @param
     * @return
     */
    public function updateObject()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        
        $old_autofill = $this->object->hasWaitingListAutoFill();
                
        $form = $this->initForm('edit');
        $ilErr->setMessage('');
        if (!$this->form->checkInput()) {
            $ilErr->setMessage($this->lng->txt('err_check_input'));
            $form->setValuesByPost();
            $this->editObject();
            return false;
        }

        if (
            $this->record_gui instanceof \ilAdvancedMDRecordGUI &&
            !$this->record_gui->importEditFormPostValues()
        ) {
            $ilErr->setMessage($this->lng->txt('err_check_input'));
        }

        $this->load();
        
        $this->object->validate();
        $this->object->getFirstAppointment()->validate();

        if (strlen($ilErr->getMessage())) {
            ilUtil::sendFailure($ilErr->getMessage());
            $this->editObject();
            return false;
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

        $DIC->object()->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();

        // if autofill has been activated trigger process
        if (!$old_autofill &&
            $this->object->hasWaitingListAutoFill()) {
            $this->object->handleAutoFill();
        }
        
        ilUtil::sendSuccess($this->lng->txt('event_updated'), true);
        $this->ctrl->redirect($this, 'edit');
        #$this->object->initFiles();
        #$this->editObject();
        return true;
    }
    
    /**
     * confirm delete files
     *
     * @access public
     * @param
     * @return
     */
    public function confirmDeleteFilesObject()
    {
        $this->tabs_gui->setTabActive('settings');

        if (!count($_POST['file_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->editObject();
            return false;
        }
        
        include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $c_gui = new ilConfirmationGUI();
        
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteFiles"));
        $c_gui->setHeaderText($this->lng->txt("info_delete_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "edit");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteFiles");

        // add items to delete
        foreach ($_POST["file_id"] as $file_id) {
            $file = new ilSessionFile($file_id);
            if ($file->getSessionId() != $this->object->getEventId()) {
                ilUtil::sendFailure($this->lng->txt('select_one'));
                $this->edit();
                return false;
            }
            $c_gui->addItem("file_id[]", $file_id, $file->getFileName());
        }
        
        $this->tpl->setContent($c_gui->getHTML());
        return true;
    }
    
    /**
     * delete files
     *
     * @access public
     * @param
     * @return
     */
    public function deleteFilesObject()
    {
        if (!count($_POST['file_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->editObject();
            return false;
        }
        foreach ($_POST['file_id'] as $id) {
            $file = new ilSessionFile($id);
            $file->delete();
        }
        $this->object->initFiles();
        $this->editObject();
        return true;
    }
    
    protected function initContainer($a_init_participants = false)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
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
            ilUtil::sendFailure('No container object found. Aborting');
            return true;
        }
        $this->container_obj_id = ilObject::_lookupObjId($this->container_ref_id);
        
        if ($a_init_participants && $this->container_obj_id) {
            if ($is_course) {
                include_once './Modules/Course/classes/class.ilCourseParticipants.php';
                return ilCourseParticipants::_getInstanceByObjId($this->container_obj_id);
            } elseif ($is_group) {
                include_once './Modules/Group/classes/class.ilGroupParticipants.php';
                return ilGroupParticipants::_getInstanceByObjId($this->container_obj_id);
            }
        }
    }

    /**
     * show material assignment
     */
    public function materialsObject()
    {
        global $DIC;

        $tree = $DIC['tree'];
        $objDefinition = $DIC['objDefinition'];

        $this->tabs_gui->activateTab('materials');
        
        // #11337 - support ANY parent container (crs, grp, fld)
        $parent_ref_id = $tree->getParentId($this->object->getRefId());
        
        include_once "Services/Object/classes/class.ilObjectAddNewItemGUI.php";
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

        include_once 'Modules/Session/classes/class.ilEventItems.php';
        $this->event_items = new ilEventItems($this->object->getId());

        include_once 'Modules/Session/classes/class.ilSessionMaterialsTableGUI.php';
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

    /**
     * Apply filter
     */
    public function applyFilter()
    {
        $tbl = new ilSessionMaterialsTableGUI($this, "materials");
        $tbl->writeFilterToSession();	// writes filter to session
        $tbl->resetOffset();		// sets record offest to 0 (first page)
        $this->ctrl->redirect($this, "materials");
    }

    /**
     * Reset filter
     */
    public function resetFilter()
    {
        $tbl = new ilSessionMaterialsTableGUI($this, "materials");
        $tbl->resetOffset();		// sets record offest to 0 (first page)
        $tbl->resetFilter();		// clears filter
        $this->ctrl->redirect($this, "materials");
    }

    /**
     * Remove materials from the current object.
     */
    public function removeMaterialsObject()
    {
        $items_checked = is_array($_POST['items']) ? $_POST['items'] : array();

        $this->event_items = new ilEventItems($this->object->getId());
        $this->event_items->removeItems($items_checked);

        $this->postUpdateMaterials();
    }


    /**
     * save material assignment
     *
     * @access public
     */
    public function saveMaterialsObject()
    {
        include_once './Modules/Session/classes/class.ilEventItems.php';
        
        $this->event_items = new ilEventItems($this->object->getId());
        $db_items = $this->event_items->getItems();

        $list_items_checked = is_array($_POST['items']) ? $_POST['items'] : array();
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
    public function postUpdateMaterials()
    {
        include_once 'Modules/Session/classes/class.ilSessionMaterialsTableGUI.php';
        $tbl = new ilSessionMaterialsTableGUI($this, "materials");
        $tbl->setOffset(0);
        $tbl->storeNavParameter();//remove offset and go to page 1

        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'materials');
    }
    

    /**
     * show attendance list selection
     *
     * @access public
     * @return
     */
    public function attendanceListObject()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        
        $this->checkPermission('manage_members');
        $ilTabs->setTabActive('event_edit_members');
        
        $list = $this->initAttendanceList();
        $form = $list->initForm('printAttendanceList');
        $tpl->setContent($form->getHTML());
    }
    
    /**
     * Init attendance list object
     *
     * @return ilAttendanceList
     */
    protected function initAttendanceList()
    {
        $members_obj = $this->initContainer(true);
        
        include_once 'Services/Membership/classes/class.ilAttendanceList.php';
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
        
    /**
     * print attendance list
     *
     * @access protected
     */
    protected function printAttendanceListObject()
    {
        $this->checkPermission('manage_members');
                                                    
        $list = $this->initAttendanceList();
        $list->initFromForm();
        $list->setCallback(array($this, 'getAttendanceListUserData'));
        
        include_once 'Modules/Session/classes/class.ilEventParticipants.php';
        $this->event_part = new ilEventParticipants($this->object->getId());
        
        echo $list->getFullscreenHTML();
        exit();
    }
    
    /**
     * Get user data for attendance list
     * @param int $a_user_id
     * @param bool $a_is_admin
     * @param bool $a_is_tutor
     * @param bool $a_is_member
     * @param array $a_filters
     * @return array
     */
    public function getAttendanceListUserData($a_user_id, $a_filters)
    {
        $data = $this->event_part->getUser($a_user_id);
        
        if ($a_filters && $a_filters["registered"] && !$data["registered"]) {
            return;
        }
        
        $data['registered'] = $data['registered'] ?
            $this->lng->txt('yes') :
            $this->lng->txt('no');
        $data['participated'] = $data['participated'] ?
            $this->lng->txt('yes') :
            $this->lng->txt('no');
        
        return $data;
    }
    
    /**
     * list sessions of all user
     *
     * @access public
     * @param
     * @return
     */
    public function eventsListObject()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];
        $tree = $DIC['tree'];

        if (!$ilAccess->checkAccess('manage_members', '', $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
        }
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.sess_list.html', 'Modules/Session');
        $this->__showButton($this->ctrl->getLinkTarget($this, 'exportCSV'), $this->lng->txt('event_csv_export'));
                
        include_once 'Modules/Session/classes/class.ilEventParticipants.php';
        
        $this->tpl->addBlockfile("EVENTS_TABLE", "events_table", "tpl.table.html");
        $this->tpl->addBlockfile('TBL_CONTENT', 'tbl_content', 'tpl.sess_list_row.html', 'Modules/Session');
        
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
        $this->ctrl->setParameter($this, 'offset', (int) $_GET['offset']);
        
        $course_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs');
        $events = array();
        foreach ($tree->getSubtree($tree->getNodeData($course_ref_id), false, 'sess') as $event_id) {
            $tmp_event = ilObjectFactory::getInstanceByRefId($event_id, false);
            if (!is_object($tmp_event) or $tmp_event->getType() != 'sess') {
                continue;
            }
            $events[] = $tmp_event;
        }
        
        $headerNames = array();
        $headerVars = array();
        $colWidth = array();
        
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
        $tbl->setHeaderVars($headerVars, $this->ctrl->getParameterArray($this, 'eventsList'));
        $tbl->setColumnWidth($colWidth);

        $tbl->setOrderColumn($_GET["sort_by"]);
        $tbl->setOrderDirection($_GET["sort_order"]);
        $tbl->setOffset($_GET["offset"]);
        $tbl->setLimit($ilUser->getPref("hits_per_page"));
        $tbl->setMaxCount(count($members));
        $tbl->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));
        
        $sliced_users = array_slice($members, $_GET['offset'], $_SESSION['tbl_limit']);
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
            $this->tpl->setVariable("CSS_ROW", ilUtil::switchColor($counter++, 'tblrow1', 'tblrow2'));
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
            $this->tpl->setVariable("LEGEND_CSS_ROW", ilUtil::switchColor($counter++, 'tblrow1', 'tblrow2'));
            $this->tpl->setVariable("LEGEND_DIGIT", $i++);
            $this->tpl->setVariable("LEGEND_EVENT_TITLE", $event_obj->getTitle());
            $this->tpl->setVariable("LEGEND_EVENT_DESCRIPTION", $event_obj->getDescription());
            $this->tpl->setVariable("LEGEND_EVENT_LOCATION", $event_obj->getLocation());
            $this->tpl->setVariable("LEGEND_EVENT_APPOINTMENT", $event_obj->getFirstAppointment()->appointmentToString());
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * Init Form
     *
     * @access protected
     */
    protected function initForm($a_mode)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        if (is_object($this->form)) {
            return $this->form;
        }
        
        $this->lng->loadLanguageModule('dateplaner');
    
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        
        include_once('./Services/YUI/classes/class.ilYuiUtil.php');
        ilYuiUtil::initDomEvent();

        $this->form = new ilPropertyFormGUI();
        $this->form->setMultipart(true);
        $this->form->setTableWidth('600px');
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setMultipart(true);

        if ($a_mode == 'create') {
            $this->form = $this->initDidacticTemplate($this->form);
        }
        
        $this->lng->loadLanguageModule('dateplaner');
        include_once './Services/Form/classes/class.ilDateDurationInputGUI.php';
        $dur = new ilDateDurationInputGUI($this->lng->txt('cal_fullday'), 'event');
        $dur->setRequired(true);
        $dur->enableToggleFullTime(
            $this->lng->txt('event_fulltime_info'),
            $this->object->getFirstAppointment()->enabledFulltime() ? true : false
        );
        $dur->setShowTime(true);
        $dur->setStart($this->object->getFirstAppointment()->getStart());
        $dur->setEnd($this->object->getFirstAppointment()->getEnd());
        
        $this->form->addItem($dur);


        // Recurrence
        if ($a_mode == 'create') {
            if (!is_object($this->rec)) {
                include_once('./Modules/Session/classes/class.ilEventRecurrence.php');
                $this->rec = new ilEventRecurrence();
            }
            include_once('./Services/Calendar/classes/Form/class.ilRecurrenceInputGUI.php');
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
            $this->record_gui->setRefId((int) $_GET['ref_id']);
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

        include_once './Modules/Session/classes/class.ilSessionMembershipRegistrationSettingsGUI.php';
        include_once './Services/Membership/classes/class.ilMembershipRegistrationSettings.php';
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

        $DIC->object()->commonSettings()->legacyForm($this->form, $this->object)->addTileImage();
                
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
        $mail_type->setValue($this->object->getMailToMembersType());

        $mail_tutors = new ilRadioOption(
            $this->lng->txt('sess_mail_admins_only'),
            ilObjSession::MAIL_ALLOWED_ADMIN,
            $this->lng->txt('sess_mail_admins_only_info')
        );
        $mail_type->addOption($mail_tutors);

        $mail_all = new ilRadioOption(
            $this->lng->txt('sess_mail_all'),
            ilObjSession::MAIL_ALLOWED_ALL,
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
                return $this->form;
            
            case 'edit':
                $this->form->setTitle($this->lng->txt('event_table_update'));
                $this->form->addCommandButton('update', $this->lng->txt('save'));
                $this->form->addCommandButton('cancelEdit', $this->lng->txt('cancel'));
                return $this->form;
        }
        return $this->form;
    }
    
    /**
     * load settings
     *
     * @access protected
     * @param
     * @return
     */
    protected function load()
    {
        $event = $this->form->getItemByPostVar('event');
        if ($event->getStart() && $event->getEnd()) {
            $this->object->getFirstAppointment()->setStartingTime($event->getStart()->get(IL_CAL_UNIX));
            $this->object->getFirstAppointment()->setEndingTime($event->getStart()->get(IL_CAL_UNIX));
            $this->object->getFirstAppointment()->setStart($event->getStart());
            $this->object->getFirstAppointment()->setEnd($event->getEnd());
            $this->object->getFirstAppointment()->toggleFulltime($event->getStart() instanceof ilDate);
        }

        $this->object->setTitle(ilUtil::stripSlashes($_POST['title']));
        $this->object->setDescription(ilUtil::stripSlashes($_POST['desc']));
        $this->object->setLocation(ilUtil::stripSlashes($_POST['location']));
        $this->object->setName(ilUtil::stripSlashes($_POST['tutor_name']));
        $this->object->setPhone(ilUtil::stripSlashes($_POST['tutor_phone']));
        $this->object->setEmail(ilUtil::stripSlashes($_POST['tutor_email']));
        $this->object->setDetails(ilUtil::stripSlashes($_POST['details']));

        $this->object->setRegistrationNotificationEnabled(ilUtil::stripSlashes($_POST['registration_notification']));
        $this->object->setRegistrationNotificationOption(ilUtil::stripSlashes($_POST['notification_option']));

        $this->object->setRegistrationType((int) $_POST['registration_type']);

        switch ($this->object->getRegistrationType()) {
            case ilMembershipRegistrationSettings::TYPE_DIRECT:
                $this->object->enableCannotParticipateOption((bool) $_POST['show_cannot_participate_direct']);
                break;
            case ilMembershipRegistrationSettings::TYPE_REQUEST:
                $this->object->enableCannotParticipateOption((bool) $_POST['show_cannot_participate_request']);
                break;
            default:
                $this->object->enableCannotParticipateOption(false);
                break;
        }


        // $this->object->setRegistrationMinUsers((int) $_POST['registration_min_members']);
        $this->object->setRegistrationMaxUsers((int) $_POST['registration_max_members']);
        $this->object->enableRegistrationUserLimit((int) $_POST['registration_membership_limited']);
        $this->object->setShowMembers((int) $_POST['show_members']);
        $this->object->setMailToMembersType((int) $_POST['mail_type']);
        
        switch ((int) $_POST['waiting_list']) {
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

    /**
     * load recurrence settings
     *
     * @access protected
     * @return
     */
    protected function loadRecurrenceSettings()
    {
        include_once('./Modules/Session/classes/class.ilSessionRecurrence.php');
        $this->rec = new ilSessionRecurrence();
        
        switch ($_POST['frequence']) {
            case IL_CAL_FREQ_DAILY:
                $this->rec->setFrequenceType($_POST['frequence']);
                $this->rec->setInterval((int) $_POST['count_DAILY']);
                break;
            
            case IL_CAL_FREQ_WEEKLY:
                $this->rec->setFrequenceType($_POST['frequence']);
                $this->rec->setInterval((int) $_POST['count_WEEKLY']);
                if (is_array($_POST['byday_WEEKLY'])) {
                    $this->rec->setBYDAY(ilUtil::stripSlashes(implode(',', $_POST['byday_WEEKLY'])));
                }
                break;

            case IL_CAL_FREQ_MONTHLY:
                $this->rec->setFrequenceType($_POST['frequence']);
                $this->rec->setInterval((int) $_POST['count_MONTHLY']);
                switch ((int) $_POST['subtype_MONTHLY']) {
                    case 0:
                        // nothing to do;
                        break;
                    
                    case 1:
                        switch ((int) $_POST['monthly_byday_day']) {
                            case 8:
                                // Weekday
                                $this->rec->setBYSETPOS((int) $_POST['monthly_byday_num']);
                                $this->rec->setBYDAY('MO,TU,WE,TH,FR');
                                break;
                                
                            case 9:
                                // Day of month
                                $this->rec->setBYMONTHDAY((int) $_POST['monthly_byday_num']);
                                break;
                                
                            default:
                                $this->rec->setBYDAY((int) $_POST['monthly_byday_num'] . $_POST['monthly_byday_day']);
                                break;
                        }
                        break;
                    
                    case 2:
                        $this->rec->setBYMONTHDAY((int) $_POST['monthly_bymonthday']);
                        break;
                }
                break;
            
            case IL_CAL_FREQ_YEARLY:
                $this->rec->setFrequenceType($_POST['frequence']);
                $this->rec->setInterval((int) $_POST['count_YEARLY']);
                switch ((int) $_POST['subtype_YEARLY']) {
                    case 0:
                        // nothing to do;
                        break;
                    
                    case 1:
                        $this->rec->setBYMONTH((int) $_POST['yearly_bymonth_byday']);
                        $this->rec->setBYDAY((int) $_POST['yearly_byday_num'] . $_POST['yearly_byday']);
                        break;
                    
                    case 2:
                        $this->rec->setBYMONTH((int) $_POST['yearly_bymonth_by_monthday']);
                        $this->rec->setBYMONTHDAY((int) $_POST['yearly_bymonthday']);
                        break;
                }
                break;
        }
        
        // UNTIL
        switch ((int) $_POST['until_type']) {
            case 1:
                $this->rec->setFrequenceUntilDate(null);
                // nothing to do
                break;
                
            case 2:
                $this->rec->setFrequenceUntilDate(null);
                $this->rec->setFrequenceUntilCount((int) $_POST['count']);
                break;
                
            case 3:
                $frequence = $this->form->getItemByPostVar('frequence');
                $end = $frequence->getRecurrence()->getFrequenceUntilDate();
                $this->rec->setFrequenceUntilCount(0);
                $this->rec->setFrequenceUntilDate($end);
                break;
        }
    }
    
    
    /**
     *
     *
     * @access protected
     * @param
     * @return
     */
    protected function __toUnix($date, $time)
    {
        return mktime($time['h'], $time['m'], 0, $date['m'], $date['d'], $date['y']);
    }

    /**
     * Add session locator
     *
     * @access public
     *
     */
    public function addLocatorItems()
    {
        global $DIC;

        $ilLocator = $DIC['ilLocator'];
        
        if (!$this->getCreationMode()) {
            // see prepareOutput()
            include_once './Modules/Session/classes/class.ilSessionAppointment.php';
            $title = strlen($this->object->getTitle()) ? (': ' . $this->object->getTitle()) : '';
            $title = $this->object->getFirstAppointment()->appointmentToString() . $title;
        
            $ilLocator->addItem($title, $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
        }
    }


    /**
     * Redirect to parent content page
     */
    protected function redirectToParentContentPageObject()
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];

        $tree = $DIC->repositoryTree();
        $ctrl = $DIC->ctrl();

        $parent_id = $tree->getParentId($this->object->getRefId());

        // #11650
        $parent_type = ilObject::_lookupType($parent_id, true);

        $parent_class = $objDefinition->getClassName($parent_type);
        $parent_class = 'ilObj' . $parent_class . 'GUI';

        $ctrl->setParameterByClass($parent_class, "ref_id", $parent_id);
        $ctrl->redirectByClass($parent_class, "view");
    }


    /**
     * Build tabs
     *
     * @access public
     *
     */
    public function getTabs()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilTabs = $DIC['ilTabs'];
        $tree = $DIC['tree'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilHelp = $DIC['ilHelp'];
        $ilUser = $DIC->user();

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
        $tree = $DIC['tree'];
        $parent_id = $tree->getParentId((int) $_REQUEST['ref_id']);

        if ($ilAccess->checkAccess('write', '', $this->ref_id) && ilContainer::_lookupContainerSetting(
            ilObject::_lookupObjId($parent_id),
            ilObjectServiceSettingsGUI::BOOKING,
            false
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
        include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
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
     * Custom callback after object is created (in parent containert
     *
     * @param ilObject $a_obj
     */
    public function afterSaveCallback(ilObject $a_obj)
    {
        // add new object to materials
        include_once './Modules/Session/classes/class.ilEventItems.php';
        $event_items = new ilEventItems($this->object->getId());
        $event_items->addItem($a_obj->getRefId());
        $event_items->update();

        /*
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        $this->ctrl->redirect($this, "materials");
        */
    }
    
    
    /**
     * Used for waiting list
     */
    public function readMemberData($a_usr_ids)
    {
        $tmp_data = array();
        foreach ($a_usr_ids as $usr_id) {
            $tmp_data[$usr_id] = array();
        }
        return $tmp_data;
    }
    
    
    
    /**
     * container ref id
     * @return int ref id
     */
    public function getContainerRefId()
    {
        if (!$this->container_ref_id) {
            $this->initContainer();
        }
        return $this->container_ref_id;
    }


    /**
     * Cancel editigin
     * @global type $ilCtrl
     * @global type $tree
     */
    protected function cancelEditObject()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $tree = $DIC['tree'];
        
        $parent_id = $tree->getParentId((int) $_REQUEST['ref_id']);
        
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $parent_id);

        $ilCtrl->redirectByClass("ilrepositorygui", "");
    }
    
    
    /**
     * Get default member role
     * @return int
     */
    public function getDefaultMemberRole()
    {
        $local_roles = $GLOBALS['DIC']->rbac()->review()->getRolesOfRoleFolder($this->object->getRefId(), false);
        
        foreach ($local_roles as $role_id) {
            $title = ilObject::_lookupTitle($role_id);
            if (substr($title, 0, 19) == 'il_sess_participant') {
                return $role_id;
            }
        }
        return 0;
    }
    
    
    /**
     * get all local roles
     * @return int[]
     */
    public function getLocalRoles()
    {
        return $GLOBALS['DIC']->rbac()->review()->getRolesOfRoleFolder($this->object->getRefId(), false);
    }
    
    
    /**
     * Create a course mail signature
     * @return string
     */
    public function createMailSignature()
    {
        $link = chr(13) . chr(10) . chr(13) . chr(10);
        $link .= $this->lng->txt('sess_mail_permanent_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        include_once './Services/Link/classes/class.ilLink.php';
        $link .= ilLink::_getLink($this->object->getRefId());
        return rawurlencode(base64_encode($link));
    }

    /**
     * Import
     */
    protected function importFileObject($parent_id = null, $a_catch_errors = true)
    {
        return parent::importFileObject($parent_id, $a_catch_errors);
    }
}
