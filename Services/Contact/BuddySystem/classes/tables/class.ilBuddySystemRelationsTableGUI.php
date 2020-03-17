<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemLinkButton.php';

/**
 * Class ilBuddyList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationsTableGUI extends ilTable2GUI
{
    /**
     * @var string
     */
    const APPLY_FILTER_CMD = 'applyContactsTableFilter';

    /**
     * @var string
     */
    const RESET_FILTER_CMD = 'resetContactsTableFilter';

    /**
     * @var string
     */
    const STATE_FILTER_ELM_ID = 'relation_state_type';

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $container_tpl;

    /**
     * @var bool
     */
    protected $access_to_mail_system = false;

    /**
     * @var bool
     */
    protected $chat_enabled = false;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @param        $a_parent_obj
     * @param string $a_parent_cmd
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        /**
         * @var $ilCtrl ilCtrl
         * @var $tpl    ilTemplate
         */
        global $DIC;

        $this->ctrl = $DIC['ilCtrl'];
        $this->container_tpl = $DIC['tpl'];
        $this->user = $DIC['ilUser'];

        $this->setId('buddy_system_tbl');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->lng->loadLanguageModule('buddysystem');

        $this->access_to_mail_system = $DIC->rbac()->system()->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId());

        $chatSettings = new ilSetting('chatroom');
        $this->chat_enabled = $chatSettings->get("chat_enabled", false);

        $this->setDefaultOrderDirection('ASC');
        $this->setDefaultOrderField('public_name');

        $this->setTitle($this->lng->txt('buddy_tbl_title_relations'));

        if ($this->access_to_mail_system || $this->chat_enabled) {
            $this->addColumn('', 'chb', '1%', true);
            $this->setSelectAllCheckbox('usr_id');
            if ($this->access_to_mail_system) {
                $this->addMultiCommand('mailToUsers', $this->lng->txt('send_mail_to'));
            }
            if ($this->chat_enabled) {
                $this->addMultiCommand('inviteToChat', $this->lng->txt('invite_to_chat'));
            }
        }

        $this->addColumn($this->lng->txt('name'), 'public_name');
        $this->addColumn($this->lng->txt('login'), 'login');
        $this->addColumn('', '');

        $this->setRowTemplate('tpl.buddy_system_relation_table_row.html', 'Services/Contact/BuddySystem');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setFilterCommand(self::APPLY_FILTER_CMD);
        $this->setResetCommand(self::RESET_FILTER_CMD);

        $this->initFilter();
    }

    /**
     * {@inheritdoc}
     */
    public function initFilter()
    {
        $this->filters = array();
        $this->filter = array();

        require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateFactory.php';

        require_once'Services/Form/classes/class.ilSelectInputGUI.php';
        $relations_state_selection = new ilSelectInputGUI($this->lng->txt('buddy_tbl_filter_state'), self::STATE_FILTER_ELM_ID);

        $options = array();
        $state = ilBuddySystemRelationStateFactory::getInstance()->getStatesAsOptionArray(false);
        foreach ($state as $key => $option) {
            $options[$key] = $option;
        }
        $relations_state_selection->setOptions(array('' => $this->lng->txt('please_choose')) + $options);
        $this->addFilterItem($relations_state_selection);
        $relations_state_selection->readFromSession();
        $this->filter['relation_state_type'] = $relations_state_selection->getValue();

        require_once 'Services/Form/classes/class.ilTextInputGUI.php';
        $public_name = new ilTextInputGUI($this->lng->txt('name'), 'public_name');
        $this->addFilterItem($public_name);
        $public_name->readFromSession();
        $this->filter['public_name'] = $public_name->getValue();
    }

    /**
     *
     */
    public function populate()
    {
        $this->setExternalSorting(false);
        $this->setExternalSegmentation(false);

        $data = array();

        $relations = ilBuddyList::getInstanceByGlobalUser()->getRelations();

        $state_filter = $this->filter[self::STATE_FILTER_ELM_ID];
        $relations = $relations->filter(function (ilBuddySystemRelation $relation) use ($state_filter) {
            return !strlen($state_filter) || strtolower(get_class($relation->getState())) == strtolower($state_filter);
        });

        require_once 'Services/User/classes/class.ilUserUtil.php';
        $public_names = ilUserUtil::getNamePresentation($relations->getKeys(), false, false, '', false, true, false);
        $logins = ilUserUtil::getNamePresentation($relations->getKeys(), false, false, '', false, false, false);

        $logins = array_map(function ($value) {
            $matches = null;
            preg_match_all('/\[([^\[]+?)\]/', $value, $matches);
            return (
                is_array($matches) &&
                isset($matches[1]) &&
                is_array($matches[1]) &&
                isset($matches[1][count($matches[1]) - 1])
            ) ? $matches[1][count($matches[1]) - 1] : '';
        }, $logins);

        $public_name = $this->filter['public_name'];
        $relations = $relations->filter(function (ilBuddySystemRelation $relation) use ($public_name, $relations, $public_names, $logins) {
            return (
                !strlen($public_name) ||
                strpos(strtolower($public_names[$relations->getKey($relation)]), strtolower($public_name)) !== false ||
                strpos(strtolower($logins[$relations->getKey($relation)]), strtolower($public_name)) !== false
            );
        });

        foreach ($relations->toArray() as $usr_id => $relation) {
            $data[] = array(
                'usr_id' => $usr_id,
                'public_name' => $public_names[$usr_id],
                'login' => $logins[$usr_id]
            );
        }

        $this->setData($data);
    }

    /**
     * Standard Version of Fill Row. Most likely to
     * be overwritten by derived class.
     * @param    array $a_set data array
     */
    protected function fillRow($a_set)
    {
        if ($this->access_to_mail_system) {
            $a_set['chb'] = ilUtil::formCheckbox(0, 'usr_id[]', $a_set['usr_id']);
        }

        $public_profile = ilObjUser::_lookupPref($a_set['usr_id'], 'public_profile');
        if (!$this->user->isAnonymous() && $public_profile == 'y' || $public_profile == 'g') {
            $this->ctrl->setParameterByClass('ilpublicuserprofilegui', 'user', $a_set['usr_id']);
            $profile_target = $this->ctrl->getLinkTargetByClass('ilpublicuserprofilegui', 'getHTML');
            $a_set['profile_link'] = $profile_target;
            $a_set['linked_public_name'] = $a_set['public_name'];

            $a_set['profile_link_login'] = $profile_target;
            $a_set['linked_login'] = $a_set['login'];
        } else {
            $a_set['unlinked_public_name'] = $a_set['public_name'];
            $a_set['unlinked_login'] = $a_set['login'];
        }

        $a_set['contact_actions'] = ilBuddySystemLinkButton::getInstanceByUserId($a_set['usr_id'])->getHtml();
        parent::fillRow($a_set);
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $listener_tpl = new ilTemplate('tpl.buddy_system_relation_table_listener.html', true, true, 'Services/Contact/BuddySystem');
        $listener_tpl->setVariable('TABLE_ID', $this->getId());
        $listener_tpl->setVariable('FILTER_ELM_ID', self::STATE_FILTER_ELM_ID);
        $listener_tpl->setVariable('NO_ENTRIES_TEXT', $this->getNoEntriesText() ? $this->getNoEntriesText() : $this->lng->txt("no_items"));

        return parent::render() . $listener_tpl->get();
    }
}
