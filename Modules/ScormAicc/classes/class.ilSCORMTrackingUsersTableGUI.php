<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesScormAicc
 */
class ilSCORMTrackingUsersTableGUI extends ilTable2GUI
{
    private $obj_id = 0;

    /**
     * Constructor
     */
    public function __construct($a_obj_id, $a_parent_obj, $a_parent_cmd)
    {
        $this->obj_id = $a_obj_id;

        $this->setId('sco_tr_usrs_' . $this->obj_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->initFilter();
    }

    /**
     * Get Obj id
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Parse table content
     */
    public function parse()
    {
        $this->initTable();

        // @TODO add filter
        $users = $this->getParentObject()->object->getTrackedUsers($this->filter['lastname']);
        $attempts = $this->getParentObject()->object->getAttemptsForUsers();
        $versions = $this->getParentObject()->object->getModuleVersionForUsers();
        
        include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        $privacy = ilPrivacySettings::_getInstance();
        $allowExportPrivacy = $privacy->enabledExportSCORM();

        $data = array();
        foreach ($users as $user) {
            $tmp = array();
            $tmp['user'] = $user['user_id'];
            if ($allowExportPrivacy == true) {
                $tmp['name'] = $user['lastname'] . ', ' . $user['firstname'];
            } else {
                $tmp['name'] = $user['user_id'];
            }
            $dt = new ilDateTime($user['last_access'], IL_CAL_DATETIME);
            $tmp['last_access'] = $dt->get(IL_CAL_UNIX);
            $tmp['attempts'] = (int) $attempts[$user['user_id']];
            $tmp['version'] = (int) $versions[$user['user_id']];

            $data[] = $tmp;
        }
        $this->setData($data);
    }

    public function initFilter()
    {
        $item = $this->addFilterItemByMetaType("lastname", ilTable2GUI::FILTER_TEXT);
        $this->filter["lastname"] = $item->getValue();
    }

    /**
     * Fill row template
     * @param array $a_set
     */
    protected function fillRow($a_set)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->tpl->setVariable('CHECKBOX_ID', $a_set['user']);
        $this->tpl->setVariable('VAL_USERNAME', $a_set['name']);

        // $ilCtrl->setParameter($this->getParentObject(),'user_id',$a_set['user']);
        // $this->tpl->setVariable('LINK_ITEM', $ilCtrl->getLinkTarget($this->getParentObject(),'showTrackingItem'));

        $this->tpl->setVariable('VAL_LAST', ilDatePresentation::formatDate(new ilDateTime($a_set['last_access'], IL_CAL_UNIX)));
        $this->tpl->setVariable('VAL_ATTEMPT', (int) $a_set['attempts']);
        $this->tpl->setVariable('VAL_VERSION', (string) $a_set['version']);
    }

    /**
     * Init table
     */
    protected function initTable()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->setFilterCommand('applyUserTableFilter');
        $this->setResetCommand('resetUserTableFilter');

        $this->setDisableFilterHiding(false);

        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate('tpl.scorm_track_items.html', 'Modules/ScormAicc');
        $this->setTitle($this->lng->txt('cont_tracking_items'));

        $this->addColumn('', '', '1px');
        $this->addColumn($this->lng->txt('user'), 'name', '35%');
        $this->addColumn($this->lng->txt('last_access'), 'last_access', '25%');
        $this->addColumn($this->lng->txt('attempts'), 'attempts', '20%');
        $this->addColumn($this->lng->txt('version'), 'version', '20%');

        $this->enable('select_all');
        $this->setSelectAllCheckbox('user');

        $this->addMultiCommand('deleteTrackingForUser', $this->lng->txt('delete'));
        $this->addMultiCommand('decreaseAttempts', $this->lng->txt('decrease_attempts'));
        $this->addMultiCommand('exportSelectionUsers', $this->lng->txt('export'));
    }
}
