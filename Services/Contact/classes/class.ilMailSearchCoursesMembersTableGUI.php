<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
require_once 'Services/Utilities/classes/class.ilStr.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystem.php';

/**
*
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
*
*
* @ingroup ServicesMail
*/
class ilMailSearchCoursesMembersTableGUI extends ilTable2GUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    protected $mode;
    protected $mailing_allowed;
    /**
     * Constructor
     *
     * @access public
     * @param object	parent object
     * @param string	type; valid values are 'crs' for courses and
     *			and 'grp' for groups
     *
     */
    public function __construct($a_parent_obj, $type = 'crs', $context = 'mail', $contextObjects)
    {
        global $DIC;

        $this->ctrl = $DIC['ilCtrl'];
        $this->lng  = $DIC['lng'];
        $this->user = $DIC['ilUser'];

        $tableId = $type . '_cml_' . implode('_', (array) $contextObjects);
        $this->setId($tableId);
        parent::__construct($a_parent_obj, 'showMembers');

        $this->context = $context;
        if ($this->context == 'mail') {
            // check if current user may send mails
            include_once "Services/Mail/classes/class.ilMail.php";
            $mail = new ilMail($GLOBALS['DIC']['ilUser']->getId());
            $this->mailing_allowed = $DIC->rbac()->system()->checkAccess('internal_mail', $mail->getMailObjectReferenceId());
        }

        $this->setDefaultOrderDirection('ASC');
        $this->setDefaultOrderField('members_login');

        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('buddysystem');

        $mode = array();
        if ($type == 'crs') {
            $mode["checkbox"] = 'search_crs';
            $mode["short"] = 'crs';
            $mode["long"] = 'course';
            $mode["lng_type"] = $this->lng->txt('course');
            $mode["view"] = "crs_members";
        } elseif ($type == 'grp') {
            $mode["checkbox"] = 'search_grp';
            $mode["short"] = 'grp';
            $mode["long"] = 'group';
            $mode["lng_type"] = $this->lng->txt('group');
            $mode["view"] = "grp_members";
        }
        $this->setTitle($this->lng->txt('members'));
        $this->mode = $mode;
        $this->ctrl->setParameter($a_parent_obj, 'view', $mode['view']);
        if ($_GET['ref'] != '') {
            $this->ctrl->setParameter($a_parent_obj, 'ref', $_GET['ref']);
        }
        if (is_array($_POST[$mode["checkbox"]])) {
            $this->ctrl->setParameter($a_parent_obj, $mode["checkbox"], implode(',', $_POST[$mode["checkbox"]]));
        }

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        //$this->ctrl->clearParameters($a_parent_obj);

        $this->setRowTemplate('tpl.mail_search_courses_members_row.html', 'Services/Contact');

        // setup columns
        $this->addColumn('', '', '1%', true);
        $this->addColumn($this->lng->txt('login'), 'members_login', '22%');
        $this->addColumn($this->lng->txt('name'), 'members_name', '22%');
        $this->addColumn($this->lng->txt($mode['long']), 'members_crs_grp', '22%');
        if (ilBuddySystem::getInstance()->isEnabled()) {
            $this->addColumn($this->lng->txt('buddy_tbl_filter_state'), 'status', '23%');
        }
        $this->addColumn($this->lng->txt('actions'), '', '10%');

        if ($this->context == "mail") {
            if ($this->mailing_allowed) {
                $this->setSelectAllCheckbox('search_members[]');
                $this->addMultiCommand('mail', $this->lng->txt('mail_members'));
            }
        } else {
            $this->setSelectAllCheckbox('search_members[]');
            $this->lng->loadLanguageModule("wsp");
            $this->addMultiCommand('share', $this->lng->txt("wsp_share_with_members"));
        }
        $this->lng->loadLanguageModule('buddysystem');
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }
    
    /**
     * Fill row
     *
     * @access public
     * @param
     *
     */
    public function fillRow($a_set)
    {
        require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setListTitle($this->lng->txt("actions"));
        $current_selection_list->setId("act_" . md5($a_set['members_id'] . '::' . $a_set['search_' . $this->mode['short']]));

        $this->ctrl->setParameter($this->parent_obj, 'search_members', $a_set['members_id']);
        $this->ctrl->setParameter(
            $this->parent_obj,
            'search_' . $this->mode['short'],
            is_array($_REQUEST['search_' . $this->mode['short']]) ?
            implode(',', array_filter(array_map('intval', $_REQUEST['search_' . $this->mode['short']]))) :
            ilUtil::stripSlashes($_REQUEST['search_' . $this->mode['short']])
        );
        $this->ctrl->setParameter($this->parent_obj, 'view', $this->mode['view']);

        $action_html = '';
        if ($this->context == "mail") {
            if ($this->mailing_allowed) {
                $current_selection_list->addItem($this->lng->txt("mail_member"), '', $this->ctrl->getLinkTarget($this->parent_obj, "mail"));
            }
        } else {
            $current_selection_list->addItem($this->lng->txt("wsp_share_with_members"), '', $this->ctrl->getLinkTarget($this->parent_obj, "share"));
        }

        if ($this->context == 'mail' && ilBuddySystem::getInstance()->isEnabled()) {
            $relation = ilBuddyList::getInstanceByGlobalUser()->getRelationByUserId($a_set['members_id']);
            if (
                $a_set['members_id'] != $this->user->getId() &&
                $relation->isUnlinked() &&
                ilUtil::yn2tf(ilObjUser::_lookupPref($a_set['members_id'], 'bs_allow_to_contact_me'))
            ) {
                $this->ctrl->setParameterByClass('ilBuddySystemGUI', 'user_id', $a_set['members_id']);
                $current_selection_list->addItem($this->lng->txt('buddy_bs_btn_txt_unlinked_a'), '', $this->ctrl->getLinkTargetByClass('ilBuddySystemGUI', 'request'));
            }
        }

        if ($current_selection_list->getItems()) {
            $action_html = $current_selection_list->getHTML();
        }
        $this->tpl->setVariable(strtoupper('CURRENT_ACTION_LIST'), $action_html);

        foreach ($a_set as $key => $value) {
            $this->tpl->setVariable(strtoupper($key), $value);
        }
    }
}
