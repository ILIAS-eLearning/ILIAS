<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/User/classes/class.ilObjUser.php';
require_once 'Services/Mail/classes/class.ilMailbox.php';
require_once 'Services/Mail/classes/class.ilFormatMail.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystem.php';

/**
* @author Jens Conze
* @version $Id$
* @ilCtrl_Calls ilMailSearchGroupsGUI: ilBuddySystemGUI
* @ingroup ServicesMail
*/
class ilMailSearchGroupsGUI
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

        $this->ctrl->saveParameter($this, "mobj_id");
        $this->ctrl->saveParameter($this, "ref");

        include_once "Services/Mail/classes/class.ilMail.php";
        $mail = new ilMail($this->user->getId());
        $this->mailing_allowed = $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId());

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
                $this->ctrl->saveParameter($this, 'search_grp');
                $this->ctrl->setReturn($this, 'showMembers');
                $this->ctrl->forwardCommand(new ilBuddySystemGUI());
                break;

            default:
                if (!($cmd = $this->ctrl->getCmd())) {
                    $cmd = "showMyGroups";
                }

                $this->$cmd();
                break;
        }
        return true;
    }

    public function mail()
    {
        if ($_GET["view"] == "mygroups") {
            $ids = ((int) $_GET['search_grp']) ? array((int) $_GET['search_grp']) : $_POST['search_grp'];
            if ($ids) {
                $this->mailGroups();
            } else {
                ilUtil::sendInfo($this->lng->txt("mail_select_group"));
                $this->showMyGroups();
            }
        } elseif ($_GET["view"] == "grp_members") {
            $ids = ((int) $_GET['search_members']) ? array((int) $_GET['search_members']) : $_POST['search_members'];
            if ($ids) {
                $this->mailMembers();
            } else {
                ilUtil::sendInfo($this->lng->txt("mail_select_one_entry"));
                $this->showMembers();
            }
        } else {
            $this->showMyGroups();
        }
    }

    public function mailGroups()
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

        $ids = ((int) $_GET['search_grp']) ? array((int) $_GET['search_grp']) : $_POST['search_grp'];
        foreach ($ids as $grp_id) {
            $ref_ids = ilObject::_getAllReferences($grp_id);
            foreach ($ref_ids as $ref_id) {
                $roles = $this->rbacreview->getAssignableChildRoles($ref_id);
                foreach ($roles as $role) {
                    if (substr($role['title'], 0, 14) == 'il_grp_member_' ||
                        substr($role['title'], 0, 13) == 'il_grp_admin_') {
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
            $mail_data["m_email"],
            $mail_data["m_subject"],
            $mail_data["m_message"],
            $mail_data["use_placeholders"],
            $mail_data['tpl_ctx_id'],
            $mail_data['tpl_ctx_params']
        );
        
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
            $mail_data["m_email"],
            $mail_data["m_subject"],
            $mail_data["m_message"],
            $mail_data["use_placeholders"],
            $mail_data['tpl_ctx_id'],
            $mail_data['tpl_ctx_params']
        );
    
        ilUtil::redirect("ilias.php?baseClass=ilMailGUI&type=search_res");
    }

    /**
     * Cancel action
     */
    public function cancel()
    {
        if ($_GET["view"] == "mygroups" &&
            $_GET["ref"] == "mail") {
            $this->ctrl->returnToParent($this);
        } else {
            $this->showMyGroups();
        }
    }
    
    /**
     * Show user's courses
     */
    public function showMyGroups()
    {
        include_once 'Modules/Group/classes/class.ilGroupParticipants.php';

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));

        $searchTpl = new ilTemplate('tpl.mail_search_template.html', true, true, 'Services/Contact');
        
        $_GET['view'] = 'mygroups';

        $this->lng->loadLanguageModule('crs');
        
        $this->ctrl->setParameter($this, 'view', 'mygroups');
        
        include_once 'Services/Contact/classes/class.ilMailSearchCoursesTableGUI.php';
        $table = new ilMailSearchCoursesTableGUI($this, 'grp', $_GET["ref"]);
        $table->setId('search_grps_tbl');
        $grp_ids = ilGroupParticipants::_getMembershipByType($this->user->getId(), 'grp');
        
        $counter = 0;
        $tableData = array();
        if (is_array($grp_ids) &&
            count($grp_ids) > 0) {
            $num_groups_hidden_members = 0;
            include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
            foreach ($grp_ids as $grp_id) {
                /**
                 * @var $oTmpGrp ilObjGroup
                 */
                $oTmpGrp = ilObjectFactory::getInstanceByObjId($grp_id);

                $hasUntrashedReferences = ilObject::_hasUntrashedReference($grp_id);
                $showMemberListEnabled = (boolean) $oTmpGrp->getShowMembers();
                $ref_ids = array_keys(ilObject::_getAllReferences($grp_id));
                $isPrivilegedUser = $this->rbacsystem->checkAccess('write', $ref_ids[0]);

                if ($hasUntrashedReferences && ($showMemberListEnabled || $isPrivilegedUser)) {
                    $oGroupParticipants = ilGroupParticipants::_getInstanceByObjId($grp_id);
                    $grp_members = $oGroupParticipants->getParticipants();

                    foreach ($grp_members as $key => $member) {
                        $tmp_usr = new ilObjUser($member);
                        
                        if (!$tmp_usr->getActive()) {
                            unset($grp_members[$key]);
                        }
                    }

                    $hiddenMembers = false;
                    if ((int) $oTmpGrp->getShowMembers() == $oTmpGrp->SHOW_MEMBERS_DISABLED) {
                        ++$num_groups_hidden_members;
                        $hiddenMembers = true;
                    }
                    unset($oTmpGrp);

                    $ref_ids = ilObject::_getAllReferences($grp_id);
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

                    $this->ctrl->setParameter($this, 'search_grp', $grp_id);
                    $this->ctrl->setParameter($this, 'view', 'mygroups');
                    
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
                        'CRS_ID' => $grp_id,
                        'CRS_NAME' => $this->cache->lookupTitle($grp_id),
                        'CRS_NO_MEMBERS' => count($grp_members),
                        'CRS_PATH' => $path,
                        'COMMAND_SELECTION_LIST' => $current_selection_list->getHTML(),
                        "hidden_members" => $hiddenMembers
                    );
                    $counter++;
                    $tableData[] = $rowData;
                }
            }
            if ($num_groups_hidden_members > 0) {
                $searchTpl->setCurrentBlock('caption_block');
                $searchTpl->setVariable('TXT_LIST_MEMBERS_NOT_AVAILABLE', $this->lng->txt('mail_crs_list_members_not_available'));
                $searchTpl->parseCurrentBlock();
            }
        }
        $table->setData($tableData);
        if ($counter > 0) {
            $this->tpl->setVariable('TXT_MARKED_ENTRIES', $this->lng->txt('marked_entries'));
        }
        
        $searchTpl->setVariable('TABLE', $table->getHTML());
        $this->tpl->setContent($searchTpl->get());
        
        if ($_GET["ref"] != "wsp") {
            $this->tpl->printToStdout();
        }
    }

    /**
     * Show course members
     */
    public function showMembers()
    {
        if ($_GET["search_grp"] != "") {
            $_POST["search_grp"] = explode(",", $_GET["search_grp"]);
        }

        if (!is_array($_POST["search_grp"]) ||
            count($_POST["search_grp"]) == 0) {
            ilUtil::sendInfo($this->lng->txt("mail_select_group"));
            $this->showMyGroups();
        } else {
            $this->tpl->setTitle($this->lng->txt("mail_addressbook"));
            include_once 'Services/Contact/classes/class.ilMailSearchCoursesMembersTableGUI.php';
            $context = $_GET["ref"] ? $_GET["ref"] : "mail";
            $table = new ilMailSearchCoursesMembersTableGUI($this, 'grp', $context, $_POST["search_grp"]);
            $this->lng->loadLanguageModule('crs');

            $tableData = array();
            $searchTpl = new ilTemplate('tpl.mail_search_template.html', true, true, 'Services/Contact');
            
            foreach ($_POST["search_grp"] as $grp_id) {
                $ref_ids = ilObject::_getAllReferences($grp_id);
                $ref_id = current($ref_ids);

                if (is_object($group_obj = ilObjectFactory::getInstanceByRefId($ref_id, false))) {
                    $grp_members = $group_obj->getGroupMemberData($group_obj->getGroupMemberIds());

                    foreach ($grp_members as $member) {
                        $tmp_usr = new ilObjUser($member['id']);
                        if (!$tmp_usr->getActive()) {
                            continue;
                        }

                        $fullname = "";
                        if (in_array(ilObjUser::_lookupPref($member['id'], 'public_profile'), array("g", 'y'))) {
                            $fullname = $member['lastname'] . ', ' . $member['firstname'];
                        }

                        $rowData = array(
                            'members_id' => $member["id"],
                            'members_login' => $member["login"],
                            'members_name' => $fullname,
                            'members_crs_grp' => $group_obj->getTitle(),
                            'search_grp' => $grp_id,
                        );

                        if ('mail' == $context && ilBuddySystem::getInstance()->isEnabled()) {
                            $relation = ilBuddyList::getInstanceByGlobalUser()->getRelationByUserId($member['id']);
                            $state_name = ilStr::convertUpperCamelCaseToUnderscoreCase($relation->getState()->getName());
                            $rowData['status'] = '';
                            if ($member['id'] != $this->user->getId()) {
                                if ($relation->isOwnedByActor()) {
                                    $rowData['status'] = $this->lng->txt('buddy_bs_state_' . $state_name . '_a');
                                } else {
                                    $rowData['status'] = $this->lng->txt('buddy_bs_state_' . $state_name . '_p');
                                }
                            }
                        }

                        $tableData[] = $rowData;
                    }
                }
            }
            $table->setData($tableData);
            if (count($tableData)) {
                $searchTpl->setVariable("TXT_MARKED_ENTRIES", $this->lng->txt("marked_entries"));
            }
            $searchTpl->setVariable('TABLE', $table->getHTML());
            $this->tpl->setContent($searchTpl->get());
            
            if ($_GET["ref"] != "wsp") {
                $this->tpl->printToStdout();
            }
        }
    }

    public function share()
    {
        if ($_GET["view"] == "mygroups") {
            $ids = $_REQUEST["search_grp"];
            if (is_array($ids) && count($ids)) {
                $this->addPermission($ids);
            } else {
                ilUtil::sendInfo($this->lng->txt("mail_select_course"));
                $this->showMyGroups();
            }
        } elseif ($_GET["view"] == "grp_members") {
            $ids = $_REQUEST["search_members"];
            if (is_array($ids) && count($ids)) {
                $this->addPermission($ids);
            } else {
                ilUtil::sendInfo($this->lng->txt("mail_select_one_entry"));
                $this->showMembers();
            }
        } else {
            $this->showMyGroups();
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
