<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* GUI class ilSCORMOfflineModeUsersTableGUI
*
* GUI class for managing users with scorm offline player connection
*
* @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
* @version $Id: class.ilSCORMOfflineModeUsersTableGUI.php  $
*
*
*/

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Description of class
 *
 * @ingroup ModulesScormAicc
 */
class ilSCORMOfflineModeUsersTableGUI extends ilTable2GUI
{
    private $obj_id = 0;

    /**
     * Constructor
     */
    public function __construct($a_obj_id, $a_parent_obj, $a_parent_cmd)
    {
        $this->obj_id = $a_obj_id;

        parent::__construct($a_parent_obj, $a_parent_cmd);
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

        include_once './Modules/ScormAicc/classes/class.ilSCORMOfflineMode.php';
        $users=ilSCORMOfflineMode::usersInOfflineMode($this->getObjId());
        foreach ($users as $user) {
            $tmp = array();
            $tmp['user'] = $user['user_id'];
            $tmp['firstname'] = $user['firstname'];
            $tmp['lastname'] = $user['lastname'];

            $data[] = $tmp;
        }
        $this->setData($data);
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
        $this->tpl->setVariable('VAL_USERNAME', $a_set['lastname'] . ', ' . $a_set['firstname']);

        $ilCtrl->setParameter($this->getParentObject(), 'user_id', $a_set['user']);
    }

    /**
     * Init table
     */
    protected function initTable()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate('tpl.scorm_offline_mode_users.html', 'Modules/ScormAicc');
        $this->setTitle($this->lng->txt('offline_mode_users'));
        $this->setDescription($this->lng->txt("offline_mode_users_info"));

        $this->addColumn('', '', '1px');
        $this->addColumn($this->lng->txt('name'), 'name');

        $this->enable('select_all');
        $this->setSelectAllCheckbox('user');

        $this->addMultiCommand('stopUserOfflineMode', $this->lng->txt('stop_user_offline_mode'));
    }
}
