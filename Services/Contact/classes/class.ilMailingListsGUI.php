<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Table/classes/class.ilTable2GUI.php";
require_once "Services/Contact/classes/class.ilMailingLists.php";
require_once "Services/Mail/classes/class.ilFormatMail.php";

/**
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
* @ingroup ServicesMail
*/
class ilMailingListsGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl = null;

    /**
     * @var ilCtrl
     */
    protected $ctrl = null;

    /**
     * @var ilLanguage
     */
    protected $lng = null;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    private $umail = null;
    private $mlists = null;
    private $form_gui = null;
    
    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->user = $DIC['ilUser'];
        $this->error = $DIC['ilErr'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->tabs = $DIC['ilTabs'];

        $this->umail = new ilFormatMail($this->user->getId());
        $this->mlists = new ilMailingLists($this->user);
        $this->mlists->setCurrentMailingList($_GET['ml_id']);
        
        $this->ctrl->saveParameter($this, 'mobj_id');
        $this->ctrl->saveParameter($this, 'ref');
    }

    public function executeCommand()
    {
        $forward_class = $this->ctrl->getNextClass($this);
        switch ($forward_class) {
            default:
                if (!($cmd = $this->ctrl->getCmd())) {
                    $cmd = 'showMailingLists';
                }

                $this->$cmd();
                break;
        }
        return true;
    }
    
    public function confirmDelete()
    {
        $ml_ids = ((int) $_GET['ml_id']) ? array($_GET['ml_id']) : $_POST['ml_id'];
        if (!$ml_ids) {
            ilUtil::sendInfo($this->lng->txt('mail_select_one_entry'));
            $this->showMailingLists();
            return true;
        }
        
        include_once('Services/Utilities/classes/class.ilConfirmationGUI.php');
        $c_gui = new ilConfirmationGUI();
        
        $c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDelete'));
        $c_gui->setHeaderText($this->lng->txt('mail_sure_delete_entry'));
        $c_gui->setCancel($this->lng->txt('cancel'), 'showMailingLists');
        $c_gui->setConfirm($this->lng->txt('confirm'), 'performDelete');

        $entries = $this->mlists->getSelected($ml_ids);
        foreach ($entries as $entry) {
            $c_gui->addItem('ml_id[]', $entry->getId(), $entry->getTitle());
        }
        
        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_list.html', 'Services/Contact');
        $this->tpl->setVariable('DELETE_CONFIRMATION', $c_gui->getHTML());
        
        $this->tpl->show();
    
        return true;
    }

    public function performDelete()
    {
        if (is_array($_POST['ml_id'])) {
            $counter = 0;
            foreach ($_POST['ml_id'] as $id) {
                if (ilMailingList::_isOwner($id, $this->user->getId())) {
                    $this->mlists->get(ilUtil::stripSlashes($id))->delete();
                    ++$counter;
                }
            }

            if ($counter) {
                ilUtil::sendInfo($this->lng->txt('mail_deleted_entry'));
            }
        } else {
            ilUtil::sendInfo($this->lng->txt('mail_delete_error'));
        }

        $this->showMailingLists();

        return true;
    }
    
    public function mailToList()
    {
        // check if current user may send mails
        include_once "Services/Mail/classes/class.ilMail.php";
        $mail = new ilMail($this->user->getId());
        $mailing_allowed = $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId());
    
        if (!$mailing_allowed) {
            ilUtil::sendFailure($this->lng->txt('no_permission'));
            return true;
        }

        $ml_ids = ((int) $_GET['ml_id']) ? array($_GET['ml_id']) : $_POST['ml_id'];
        if (!$ml_ids) {
            ilUtil::sendInfo($this->lng->txt('mail_select_one_entry'));
            $this->showMailingLists();
            return true;
        }
        
        $mail_data = $this->umail->getSavedData();
        if (!is_array($mail_data)) {
            $this->umail->savePostData($this->user->getId(), array(), '', '', '', '', '', '', '', '');
        }
    
        $lists = array();
        foreach ($ml_ids as $id) {
            if (ilMailingList::_isOwner($id, $this->user->getId()) &&
               !$this->umail->existsRecipient('#il_ml_' . $id, (string) $mail_data['rcp_to'])) {
                $lists[] = '#il_ml_' . $id;
            }
        }
        
        if (count($lists)) {
            $mail_data = $this->umail->appendSearchResult($lists, 'to');
            $this->umail->savePostData(
                $mail_data['user_id'],
                $mail_data['attachments'],
                $mail_data['rcp_to'],
                $mail_data['rcp_cc'],
                $mail_data['rcp_bcc'],
                $mail_data['m_type'],
                $mail_data['m_email'],
                $mail_data['m_subject'],
                $mail_data['m_message'],
                $mail_data['use_placeholders'],
                $mail_data['tpl_ctx_id'],
                $mail_data['tpl_ctx_params']
            );
        }

        ilUtil::redirect("ilias.php?baseClass=ilMailGUI&type=search_res");
        
        return true;
    }
    
    public function showMailingLists()
    {
        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_list.html', 'Services/Contact');

        // check if current user may send mails
        include_once "Services/Mail/classes/class.ilMail.php";
        $mail = new ilMail($this->user->getId());
        $mailing_allowed = $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId());

        require_once 'Services/Contact/classes/class.ilMailingListsTableGUI.php';
        $tbl = new ilMailingListsTableGUI($this, 'showMailingLists');

        require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
        $create_btn = ilLinkButton::getInstance();
        $create_btn->setCaption('create');
        $create_btn->setUrl($this->ctrl->getLinkTarget($this, 'showForm'));
        $this->toolbar->addButtonInstance($create_btn);

        $result = array();
        $entries = $this->mlists->getAll();
        if (count($entries)) {
            $tbl->enable('select_all');
            $counter = 0;
            require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

            foreach ($entries as $entry) {
                if ($entry->getMode() == ilMailingList::MODE_TEMPORARY) {
                    continue;
                }

                $result[$counter]['check'] = ilUtil::formCheckbox(0, 'ml_id[]', $entry->getId());
                $result[$counter]['title'] = $entry->getTitle() . " [#il_ml_" . $entry->getId() . "]";
                $result[$counter]['description'] = $entry->getDescription();
                $result[$counter]['members'] = count($entry->getAssignedEntries());

                $this->ctrl->setParameter($this, 'ml_id', $entry->getId());

                $current_selection_list = new ilAdvancedSelectionListGUI();
                $current_selection_list->setListTitle($this->lng->txt("actions"));
                $current_selection_list->setId("act_" . $counter);

                $current_selection_list->addItem($this->lng->txt("edit"), '', $this->ctrl->getLinkTarget($this, "showForm"));
                $current_selection_list->addItem($this->lng->txt("members"), '', $this->ctrl->getLinkTarget($this, "showMembersList"));
                if ($mailing_allowed) {
                    $current_selection_list->addItem($this->lng->txt("send_mail_to"), '', $this->ctrl->getLinkTarget($this, "mailToList"));
                }
                $current_selection_list->addItem($this->lng->txt("delete"), '', $this->ctrl->getLinkTarget($this, "confirmDelete"));

                $result[$counter]['COMMAND_SELECTION_LIST'] = $current_selection_list->getHTML();
                ++$counter;
            }
            
            if ($mailing_allowed) {
                $tbl->addMultiCommand('mailToList', $this->lng->txt('send_mail_to'));
            }
            $tbl->addMultiCommand('confirmDelete', $this->lng->txt('delete'));
        } else {
            $tbl->disable('header');
            $tbl->disable('footer');
        }

        $tbl->setData($result);

        if (isset($_GET['ref']) && $_GET['ref'] == 'mail') {
            $tbl->addCommandButton('cancel', $this->lng->txt('cancel'));
        }

        $this->tpl->setVariable('MAILING_LISTS', $tbl->getHTML());
        $this->tpl->show();
        return true;
    }

    /**
     * Cancel action
     */
    public function cancel()
    {
        if (isset($_GET['ref']) && $_GET['ref'] == 'mail') {
            $this->ctrl->returnToParent($this);
        } else {
            $this->showMailingLists();
        }
    }
    
    public function saveForm()
    {
        if ($this->mlists->getCurrentMailingList()->getId()) {
            if (!ilMailingList::_isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
                $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
            }

            $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
            $this->initForm('edit');
        } else {
            $this->initForm();
        }
        
        if ($this->form_gui->checkInput()) {
            $this->mlists->getCurrentMailingList()->setTitle($_POST['title']);
            $this->mlists->getCurrentMailingList()->setDescription($_POST['description']);
            if ($this->mlists->getCurrentMailingList()->getId()) {
                $this->mlists->getCurrentMailingList()->setChangedate(date('Y-m-d H:i:s', time()));
                $this->mlists->getCurrentMailingList()->update();
                ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
            } else {
                $this->mlists->getCurrentMailingList()->setCreatedate(date('Y-m-d H:i:s', time()));
                $this->mlists->getCurrentMailingList()->insert();
                $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
                $this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'saveForm'));

                $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
                $this->ctrl->redirect($this, 'showMembersList');

                exit;
            }
        }
        
        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_form.html', 'Services/Contact');
        
        $this->form_gui->setValuesByPost();
        
        $this->tpl->setVariable('FORM', $this->form_gui->getHTML());
        return $this->tpl->show();
    }
    
    private function initForm($a_type = 'create')
    {
        include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
        $this->form_gui = new ilPropertyFormGUI();
        
        $this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'saveForm'));
        $this->form_gui->setTitle($this->lng->txt('mail_mailing_list'));
        
        $titleGui = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $titleGui->setRequired(true);
        $this->form_gui->addItem($titleGui);
        
        $descriptionGui = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $descriptionGui->setCols(40);
        $descriptionGui->setRows(8);
        $this->form_gui->addItem($descriptionGui);
        
        $this->form_gui->addCommandButton('saveForm', $this->lng->txt('save'));
        $this->form_gui->addCommandButton('showMailingLists', $this->lng->txt('cancel'));
    }
    
    private function setValuesByObject()
    {
        $this->form_gui->setValuesByArray(array(
            'title' => $this->mlists->getCurrentMailingList()->getTitle(),
            'description' => $this->mlists->getCurrentMailingList()->getDescription()
        ));
    }
    
    private function setDefaultValues()
    {
        $this->form_gui->setValuesByArray(array(
            'title' => '',
            'description' => ''
        ));
    }

    public function showForm()
    {
        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_form.html', 'Services/Contact');
        
        if ($this->mlists->getCurrentMailingList()->getId()) {
            if (!ilMailingList::_isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
                $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
            }

            $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
            $this->initForm('edit');
            $this->setValuesByObject();
        } else {
            $this->initForm();
            $this->setDefaultValues();
        }
        
        $this->tpl->setVariable('FORM', $this->form_gui->getHTML());
        return $this->tpl->show();
    }
    
    public function showMembersList()
    {
        if (!$this->mlists->getCurrentMailingList()->getId()) {
            $this->showMailingLists();
            return true;
        }

        $this->ctrl->setParameter($this, 'cmd', 'post');
        $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_members.html', 'Services/Contact');

        require_once 'Services/Contact/classes/class.ilMailingListsMembersTableGUI.php';
        $tbl = new ilMailingListsMembersTableGUI($this, 'showMembersList', $this->mlists->getCurrentMailingList());
        $result = array();

        require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
        $create_btn = ilLinkButton::getInstance();
        $create_btn->setCaption('add');
        $create_btn->setUrl($this->ctrl->getLinkTarget($this, 'showAssignmentForm'));
        $this->toolbar->addButtonInstance($create_btn);

        $assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();
        if (count($assigned_entries)) {
            $tbl->enable('select_all');
            $tbl->setSelectAllCheckbox('a_id');

            $usr_ids = array();
            foreach ($assigned_entries as $entry) {
                $usr_ids[] = $entry['usr_id'];
            }

            require_once 'Services/User/classes/class.ilUserUtil.php';
            $names = ilUserUtil::getNamePresentation($usr_ids, false, false, '', false, false, false);

            $counter = 0;
            foreach ($assigned_entries as $entry) {
                $result[$counter]['check'] = ilUtil::formCheckbox(0, 'a_id[]', $entry['a_id']);
                $result[$counter]['user'] = $names[$entry['usr_id']];
                ++$counter;
            }

            $tbl->addMultiCommand('confirmDeleteMembers', $this->lng->txt('delete'));
        } else {
            $tbl->disable('header');
            $tbl->disable('footer');

            $tbl->setNoEntriesText($this->lng->txt('mail_search_no'));
        }

        $tbl->setData($result);

        $this->tpl->setVariable('MEMBERS_LIST', $tbl->getHTML());
        $this->tpl->show();
        return true;
    }

    public function confirmDeleteMembers()
    {
        if (!isset($_POST['a_id'])) {
            ilUtil::sendInfo($this->lng->txt('mail_select_one_entry'));
            $this->showMembersList();
            return true;
        }

        include_once('Services/Utilities/classes/class.ilConfirmationGUI.php');
        $c_gui = new ilConfirmationGUI();
        $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
        $c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDeleteMembers'));
        $c_gui->setHeaderText($this->lng->txt('mail_sure_delete_entry'));
        $c_gui->setCancel($this->lng->txt('cancel'), 'showMembersList');
        $c_gui->setConfirm($this->lng->txt('confirm'), 'performDeleteMembers');

        $assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();

        $usr_ids = array();
        foreach ($assigned_entries as $entry) {
            $usr_ids[] = $entry['usr_id'];
        }

        require_once 'Services/User/classes/class.ilUserUtil.php';
        $names = ilUserUtil::getNamePresentation($usr_ids, false, false, '', false, false, false);

        foreach ($assigned_entries as $entry) {
            if (in_array($entry['a_id'], $_POST['a_id'])) {
                $c_gui->addItem('a_id[]', $entry['a_id'], $names[$entry['usr_id']]);
            }
        }

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_members.html', 'Services/Contact');
        $this->tpl->setVariable('DELETE_CONFIRMATION', $c_gui->getHTML());

        $this->tpl->show();
        return true;
    }

    public function performDeleteMembers()
    {
        if (!ilMailingList::_isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (is_array($_POST['a_id'])) {
            $assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();
            foreach ($_POST['a_id'] as $id) {
                if (isset($assigned_entries[$id])) {
                    $this->mlists->getCurrentMailingList()->deleteEntry((int) $id);
                }
            }
            ilUtil::sendInfo($this->lng->txt('mail_deleted_entry'));
        } else {
            ilUtil::sendInfo($this->lng->txt('mail_delete_error'));
        }

        $this->showMembersList();

        return true;
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getAssignmentForm()
    {
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveAssignmentForm'));
        $form->setTitle($this->lng->txt('mail_assign_entry_to_mailing_list') . ' ' . $this->mlists->getCurrentMailingList()->getTitle());

        $options = array();
        $options[''] = $this->lng->txt('please_select');

        require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
        require_once 'Services/User/classes/class.ilUserUtil.php';
        $relations = ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations();
        $names = ilUserUtil::getNamePresentation(array_keys($relations->toArray()), false, false, '', false, false, false);
        foreach ($relations as $relation) {
            /**
             * @var $relation ilBuddySystemRelation
             */
            $options[$relation->getBuddyUserId()] = $names[$relation->getBuddyUserId()];
        }

        $assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();
        if (count($assigned_entries)) {
            foreach ($assigned_entries as $assigned_entry) {
                if (is_array($options) && array_key_exists($assigned_entry['usr_id'], $options)) {
                    unset($options[$assigned_entry['usr_id']]);
                }
            }
        }

        if (count($options) > 1) {
            $formItem = new ilSelectInputGUI($this->lng->txt('mail_entry_of_contacts'), 'usr_id');
            $formItem->setRequired(true);
            $formItem->setOptions($options);
            $form->addItem($formItem);

            $form->addCommandButton('saveAssignmentForm', $this->lng->txt('assign'));
        } elseif (count($options) == 1 && count($relations)) {
            ilUtil::sendInfo($this->lng->txt('mail_mailing_lists_all_contact_entries_assigned'));
        } elseif (count($relations) == 0) {
            ilUtil::sendInfo($this->lng->txt('mail_mailing_lists_no_contact_entries'));
        }
        $form->addCommandButton('showMembersList', $this->lng->txt('cancel'));

        return $form;
    }

    /**
     * @return bool
     */
    public function saveAssignmentForm()
    {
        if (!ilMailingList::_isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getAssignmentForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showAssignmentForm($form);
            return true;
        }

        require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
        if (ilBuddyList::getInstanceByGlobalUser()->getRelationByUserId((int) $_POST['usr_id'])->isLinked()) {
            $this->mlists->getCurrentMailingList()->assignUser((int) $_POST['usr_id']);
            ilUtil::sendInfo($this->lng->txt('saved_successfully'));
            $this->showMembersList();
            return true;
        }

        $this->showAssignmentForm($form);
        return true;
    }

    /**
     * @param ilPropertyFormGUI|null $form
     * @return bool
     */
    public function showAssignmentForm(ilPropertyFormGUI $form = null)
    {
        if (!$this->mlists->getCurrentMailingList()->getId()) {
            $this->showMembersList();
            return true;
        }

        if (!ilMailingList::_isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_members_form.html', 'Services/Contact');

        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getAssignmentForm();
        }

        $this->tpl->setVariable('FORM', $form->getHTML());
        $this->tpl->show();

        return true;
    }
}
