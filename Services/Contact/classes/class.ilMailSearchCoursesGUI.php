<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/User/classes/class.ilObjUser.php';
require_once "Services/Mail/classes/class.ilMailbox.php";
require_once "Services/Mail/classes/class.ilFormatMail.php";
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystem.php';

/**
* @author Jens Conze
* @version $Id$
* @ilCtrl_Calls ilMailSearchCoursesGUI: ilBuddySystemGUI
* @ingroup ServicesMail
*/
class ilMailSearchCoursesGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilObjectDataCache
     */
    protected $cache;

    /**
     * @var ilFormatMail
     */
    protected $umail;

    /**
     * @var bool
     */
    protected $mailing_allowed;

    public function __construct($wsp_access_handler = null, $wsp_node_id = null)
    {
        global $DIC;

        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->user = $DIC['ilUser'];
        $this->error = $DIC['ilErr'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->rbacreview = $DIC['rbacreview'];
        $this->tree = $DIC['tree'];
        $this->cache = $DIC['ilObjDataCache'];

        // personal workspace
        $this->wsp_access_handler = $wsp_access_handler;
        $this->wsp_node_id = $wsp_node_id;

        // check if current user may send mails
        include_once "Services/Mail/classes/class.ilMail.php";
        $mail = new ilMail($this->user->getId());
        $this->mailing_allowed = $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId());

        $this->ctrl->saveParameter($this, "mobj_id");
        $this->ctrl->saveParameter($this, "ref");

        $this->umail = new ilFormatMail($this->user->getId());
    }

    public function executeCommand()
    {
        $forward_class = $this->ctrl->getNextClass($this);
        switch ($forward_class) {
            case 'ilbuddysystemgui':
                if (!ilBuddySystem::getInstance()->isEnabled()) {
                    $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
                }

                require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemGUI.php';
                $this->ctrl->saveParameter($this, 'search_crs');
                $this->ctrl->setReturn($this, 'showMembers');
                $this->ctrl->forwardCommand(new ilBuddySystemGUI());
                break;

            default:
                if (!($cmd = $this->ctrl->getCmd())) {
                    $cmd = "showMyCourses";
                }

                $this->$cmd();
                break;
        }
        return true;
    }

    public function mail()
    {
        if ($_GET["view"] == "mycourses") {
            $ids = ((int) $_GET['search_crs']) ? array((int) $_GET['search_crs']) : $_POST['search_crs'];
            
            if ($ids) {
                $this->mailCourses();
            } else {
                ilUtil::sendInfo($this->lng->txt("mail_select_course"));
                $this->showMyCourses();
            }
        } elseif ($_GET["view"] == "crs_members") {
            $ids = ((int) $_GET['search_members']) ? array((int) $_GET['search_members']) : $_POST['search_members'];
            if ($ids) {
                $this->mailMembers();
            } else {
                ilUtil::sendInfo($this->lng->txt("mail_select_one_entry"));
                $this->showMembers();
            }
        } else {
            $this->showMyCourses();
        }
    }

    public function mailCourses()
    {
        $members = array();

        if (!is_array($old_mail_data = $this->umail->getSavedData())) {
            $this->umail->savePostData(
                $this->user->getId(),
                array(),
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                ""
            );
        }

        require_once './Services/Object/classes/class.ilObject.php';
        require_once 'Services/Mail/classes/Address/Type/class.ilMailRoleAddressType.php';
        $ids = ((int) $_GET['search_crs']) ? array((int) $_GET['search_crs']) : $_POST['search_crs'];
        
        foreach ($ids as $crs_id) {
            $ref_ids = ilObject::_getAllReferences($crs_id);

            foreach ($ref_ids as $ref_id) {
                $roles = $this->rbacreview->getAssignableChildRoles($ref_id);
                foreach ($roles as $role) {
                    if (substr($role['title'], 0, 14) == 'il_crs_member_' ||
                        substr($role['title'], 0, 13) == 'il_crs_tutor_' ||
                        substr($role['title'], 0, 13) == 'il_crs_admin_') {
                        if (isset($old_mail_data['rcp_to']) &&
                           trim($old_mail_data['rcp_to']) != '') {
                            $rcpt = (new \ilRoleMailboxAddress($role['obj_id']))->value();
                            if (!$this->umail->existsRecipient($rcpt, (string) $old_mail_data['rcp_to'])) {
                                array_push($members, $rcpt);
                            }
                        } else {
                            array_push($members, (new \ilRoleMailboxAddress($role['obj_id']))->value());
                        }
                    }
                }
            }
        }
        
        if (count($members)) {
            $mail_data = $this->umail->appendSearchResult($members, 'to');
        } else {
            $mail_data = $this->umail->getSavedData();
        }

        $this->umail->savePostData(
            $mail_data["user_id"],
            $mail_data["attachments"],
            $mail_data["rcp_to"],
            $mail_data["rcp_cc"],
            $mail_data["rcp_bcc"],
            $mail_data["m_type"],
            $mail_data["m_email"],
            $mail_data["m_subject"],
            $mail_data["m_message"],
            $mail_data["use_placeholders"],
            $mail_data['tpl_ctx_id'],
            $mail_data['tpl_ctx_params']
        );

        #$this->ctrl->returnToParent($this);
        ilUtil::redirect("ilias.php?baseClass=ilMailGUI&type=search_res");
    }

    public function mailMembers()
    {
        $members = array();

        if (!is_array($this->umail->getSavedData())) {
            $this->umail->savePostData(
                $this->user->getId(),
                array(),
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                ""
            );
        }
    
        $ids = ((int) $_GET['search_members']) ? array((int) $_GET['search_members']) : $_POST['search_members'];
        
        foreach ($ids as $member) {
            $login = ilObjUser::_lookupLogin($member);
            array_push($members, $login);
        }
        $mail_data = $this->umail->appendSearchResult($members, "to");

        $this->umail->savePostData(
            $mail_data["user_id"],
            $mail_data["attachments"],
            $mail_data["rcp_to"],
            $mail_data["rcp_cc"],
            $mail_data["rcp_bcc"],
            $mail_data["m_type"],
            $mail_data["m_email"],
            $mail_data["m_subject"],
            $mail_data["m_message"],
            $mail_data["use_placeholders"],
            $mail_data['tpl_ctx_id'],
            $mail_data['tpl_ctx_params']
        );

        #$this->ctrl->returnToParent($this);
        ilUtil::redirect("ilias.php?baseClass=ilMailGUI&type=search_res");
    }

    /**
     * Cancel action
     */
    public function cancel()
    {
        if ($_GET["view"] == "mycourses" &&
            $_GET["ref"] == "mail") {
            $this->ctrl->returnToParent($this);
        } else {
            $this->showMyCourses();
        }
    }
    
    /**
     * Show user's courses
     */
    public function showMyCourses()
    {
        include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
    
        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        
        $searchTpl = new ilTemplate('tpl.mail_search_template.html', true, true, 'Services/Contact');
        
        $_GET['view'] = 'mycourses';

        $this->lng->loadLanguageModule('crs');

        include_once 'Services/Contact/classes/class.ilMailSearchCoursesTableGUI.php';
        $table = new ilMailSearchCoursesTableGUI($this, "crs", $_GET["ref"]);
        $table->setId('search_crs_tbl');
        include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
        $crs_ids = ilCourseParticipants::_getMembershipByType($this->user->getId(), 'crs');
        $counter = 0;
        $tableData = array();
        if (is_array($crs_ids) && count($crs_ids) > 0) {
            $num_courses_hidden_members = 0;
            include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
            foreach ($crs_ids as $crs_id) {
                /**
                 * @var $oTmpCrs ilObjCourse
                 */
                $oTmpCrs = ilObjectFactory::getInstanceByObjId($crs_id);

                $isOffline = !$oTmpCrs->isActivated();
                $hasUntrashedReferences = ilObject::_hasUntrashedReference($crs_id);
                $showMemberListEnabled = (boolean) $oTmpCrs->getShowMembers();
                $ref_ids = array_keys(ilObject::_getAllReferences($crs_id));
                $isPrivilegedUser = $this->rbacsystem->checkAccess('write', $ref_ids[0]);

                if ($hasUntrashedReferences && ((!$isOffline && $showMemberListEnabled) || $isPrivilegedUser)) {
                    $oCrsParticipants = ilCourseParticipants::_getInstanceByObjId($crs_id);
                    $crs_members = $oCrsParticipants->getParticipants();

                    foreach ($crs_members as $key => $member) {
                        $tmp_usr = new ilObjUser($member);
                        if ($tmp_usr->checkTimeLimit() == false || $tmp_usr->getActive() == false) {
                            unset($crs_members[$key]);
                        }
                    }
                    unset($tmp_usr);
                    
                    $hiddenMembers = false;
                    if ((int) $oTmpCrs->getShowMembers() == $oTmpCrs->SHOW_MEMBERS_DISABLED) {
                        ++$num_courses_hidden_members;
                        $hiddenMembers = true;
                    }
                    unset($oTmpCrs);
                    
                    $ref_ids = ilObject::_getAllReferences($crs_id);
                    $ref_id = current($ref_ids);
                    $path_arr = $this->tree->getPathFull($ref_id, $this->tree->getRootId());
                    $path_counter = 0;
                    $path = '';
                    foreach ($path_arr as $data) {
                        if ($path_counter++) {
                            $path .= " -> ";
                        }
                        $path .= $data['title'];
                    }
                    $path = $this->lng->txt('path') . ': ' . $path;

                    $current_selection_list = new ilAdvancedSelectionListGUI();
                    $current_selection_list->setListTitle($this->lng->txt("actions"));
                    $current_selection_list->setId("act_" . $counter);

                    $this->ctrl->setParameter($this, 'search_crs', $crs_id);
                    $this->ctrl->setParameter($this, 'view', 'mycourses');
                    
                    if ($_GET["ref"] == "mail") {
                        if ($this->mailing_allowed) {
                            $current_selection_list->addItem($this->lng->txt("mail_members"), '', $this->ctrl->getLinkTarget($this, "mail"));
                        }
                    } elseif ($_GET["ref"] == "wsp") {
                        $current_selection_list->addItem($this->lng->txt("wsp_share_with_members"), '', $this->ctrl->getLinkTarget($this, "share"));
                    }
                    $current_selection_list->addItem($this->lng->txt("mail_list_members"), '', $this->ctrl->getLinkTarget($this, "showMembers"));
                    
                    $this->ctrl->clearParameters($this);
                    
                    $rowData = array(
                        "CRS_ID" => $crs_id,
                        "CRS_NAME" => $this->cache->lookupTitle($crs_id),
                        "CRS_NO_MEMBERS" => count($crs_members),
                        "CRS_PATH" => $path,
                        'COMMAND_SELECTION_LIST' => $current_selection_list->getHTML(),
                        "hidden_members" => $hiddenMembers,
                    );
                    $counter++;
                    $tableData[] = $rowData;
                }
            }

            if ($num_courses_hidden_members > 0) {
                $searchTpl->setCurrentBlock('caption_block');
                $searchTpl->setVariable('TXT_LIST_MEMBERS_NOT_AVAILABLE', $this->lng->txt('mail_crs_list_members_not_available'));
                $searchTpl->parseCurrentBlock();
            }
        }

        $searchTpl->setVariable('TXT_MARKED_ENTRIES', $this->lng->txt('marked_entries'));
        
        $table->setData($tableData);
        if ($_GET['ref'] == 'mail') {
            $this->tpl->setVariable('BUTTON_CANCEL', $this->lng->txt('cancel'));
        }

        $searchTpl->setVariable('TABLE', $table->getHtml());
        $this->tpl->setContent($searchTpl->get());

        if ($_GET["ref"] != "wsp") {
            $this->tpl->show();
        }
    }

    /**
     * Show course members
     */
    public function showMembers()
    {
        include_once 'Modules/Course/classes/class.ilCourseParticipants.php';

        if ($_GET["search_crs"] != "") {
            $_POST["search_crs"] = explode(",", $_GET["search_crs"]);
            $_GET["search_crs"] = "";
        } elseif ($_SESSION["search_crs"] != "") {
            $_POST["search_crs"] = explode(",", $_SESSION["search_crs"]);
            $_SESSION["search_crs"] = "";
        }

        if (is_array($_POST['search_crs'])) {
            $_POST['search_crs'] = array_filter(array_map('intval', $_POST['search_crs']));
        }

        if (!is_array($_POST["search_crs"]) ||
            count($_POST["search_crs"]) == 0) {
            ilUtil::sendInfo($this->lng->txt("mail_select_course"));
            $this->showMyCourses();
        } else {
            foreach ($_POST['search_crs'] as $crs_id) {
                $oTmpCrs = ilObjectFactory::getInstanceByObjId($crs_id);
                if ($oTmpCrs->getShowMembers() == $oTmpCrs->SHOW_MEMBERS_DISABLED) {
                    unset($_POST['search_crs']);
                    ilUtil::sendInfo($this->lng->txt('mail_crs_list_members_not_available_for_at_least_one_crs'));
                    return $this->showMyCourses();
                }
                unset($oTmpCrs);
            }

            $this->tpl->setTitle($this->lng->txt("mail_addressbook"));
        
            $this->ctrl->setParameter($this, "view", "crs_members");
            if ($_GET["ref"] != "") {
                $this->ctrl->setParameter($this, "ref", $_GET["ref"]);
            }
            if (is_array($_POST["search_crs"])) {
                $this->ctrl->setParameter($this, "search_crs", implode(",", $_POST["search_crs"]));
            }
            $this->tpl->setVariable("ACTION", $this->ctrl->getFormAction($this));
            $this->ctrl->clearParameters($this);

            $this->lng->loadLanguageModule('crs');
            include_once 'Services/Contact/classes/class.ilMailSearchCoursesMembersTableGUI.php';
            $context = $_GET["ref"] ? $_GET["ref"] : "mail";
            $table = new ilMailSearchCoursesMembersTableGUI($this, 'crs', $context, $_POST["search_crs"]);
            $tableData = array();
            $searchTpl = new ilTemplate('tpl.mail_search_template.html', true, true, 'Services/Contact');
            foreach ($_POST["search_crs"] as $crs_id) {
                $members_obj = ilCourseParticipants::_getinstanceByObjId($crs_id);
                $tmp_members = $members_obj->getParticipants();
                $course_members = ilUtil::_sortIds($tmp_members, 'usr_data', 'lastname', 'usr_id');

                foreach ($course_members as $member) {
                    $tmp_usr = new ilObjUser($member);
                    if ($tmp_usr->checkTimeLimit() == false || $tmp_usr->getActive() == false) {
                        unset($tmp_usr);
                        continue;
                    }
                    unset($tmp_usr);
                    
                    $name = ilObjUser::_lookupName($member);
                    $login = ilObjUser::_lookupLogin($member);
    
                    $fullname = "";
                    if (in_array(ilObjUser::_lookupPref($member, 'public_profile'), array("g", 'y'))) {
                        $fullname = $name['lastname'] . ', ' . $name['firstname'];
                    }

                    $rowData = array(
                        'members_id' => $member,
                        'members_login' => $login,
                        'members_name' => $fullname,
                        'members_crs_grp' => $this->cache->lookupTitle($crs_id),
                        'search_crs' => $crs_id
                    );

                    if ('mail' == $context && ilBuddySystem::getInstance()->isEnabled()) {
                        $relation = ilBuddyList::getInstanceByGlobalUser()->getRelationByUserId($member);
                        $state_name = ilStr::convertUpperCamelCaseToUnderscoreCase($relation->getState()->getName());
                        $rowData['status'] = '';
                        if ($member != $this->user->getId()) {
                            if ($relation->isOwnedByRequest()) {
                                $rowData['status'] = $this->lng->txt('buddy_bs_state_' . $state_name . '_a');
                            } else {
                                $rowData['status'] = $this->lng->txt('buddy_bs_state_' . $state_name . '_p');
                            }
                        }
                    }

                    $tableData[] = $rowData;
                }
            }
            $table->setData($tableData);

            if (count($tableData)) {
                $searchTpl->setVariable("TXT_MARKED_ENTRIES", $this->lng->txt("marked_entries"));
            }

            $searchTpl->setVariable('TABLE', $table->getHtml());
            $this->tpl->setContent($searchTpl->get());
            
            if ($_GET["ref"] != "wsp") {
                $this->tpl->show();
            }
        }
    }
    
    public function share()
    {
        if ($_GET["view"] == "mycourses") {
            $ids = $_REQUEST["search_crs"];
            if (is_array($ids) && sizeof($ids)) {
                $this->addPermission($ids);
            } else {
                ilUtil::sendInfo($this->lng->txt("mail_select_course"));
                $this->showMyCourses();
            }
        } elseif ($_GET["view"] == "crs_members") {
            $ids = $_REQUEST["search_members"];
            if (is_array($ids) && sizeof($ids)) {
                $this->addPermission($ids);
            } else {
                ilUtil::sendInfo($this->lng->txt("mail_select_one_entry"));
                $this->showMembers();
            }
        } else {
            $this->showMyCourses();
        }
    }
    
    protected function addPermission($a_obj_ids)
    {
        if (!is_array($a_obj_ids)) {
            $a_obj_ids = array($a_obj_ids);
        }
        
        $existing = $this->wsp_access_handler->getPermissions($this->wsp_node_id);
        $added = false;
        foreach ($a_obj_ids as $object_id) {
            if (!in_array($object_id, $existing)) {
                $added = $this->wsp_access_handler->addPermission($this->wsp_node_id, $object_id);
            }
        }
        
        if ($added) {
            ilUtil::sendSuccess($this->lng->txt("wsp_share_success"), true);
        }
        $this->ctrl->redirectByClass("ilworkspaceaccessgui", "share");
    }
}
