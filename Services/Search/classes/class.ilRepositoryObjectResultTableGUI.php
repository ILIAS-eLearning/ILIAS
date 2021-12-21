<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* TableGUI class object (course,group and role) search results
 * Used in member search
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @ingroup ServicesSearch
*/
class ilRepositoryObjectResultTableGUI extends ilTable2GUI
{

    protected ilRbacAdmin $admin;

    protected ilRbacReview $review;

    public function __construct($a_parent_obj, $a_parent_cmd, $a_allow_object_selection = false)
    {
        global $DIC;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->admin = $DIC->rbac()->admin();
        $this->review = $DIC->rbac()->review();
        
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("title"), "title", "80%");
        $this->addColumn($this->lng->txt("members"), "member", "20%");
        
        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
        $this->setRowTemplate("tpl.rep_search_obj_result_row.html", "Services/Search");
        $this->setTitle($this->lng->txt('search_results'));
        $this->setEnableTitle(true);
        $this->setId("group_table");
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->enable('select_all');
        $this->setSelectAllCheckbox("obj[]");

        switch ($this->parent_obj->search_type) {
            case 'grp':
            case 'crs':
                if ($this->parent_obj->role_callback) {
                    $this->addMultiCommand('addRole', $this->lng->txt('add_member_role'));
                }
                $this->addMultiCommand('listUsers', $this->lng->txt('grp_list_members'));
                break;

            case 'role':
                if ($this->parent_obj->role_callback) {
                    $this->addMultiCommand('addRole', $this->lng->txt('add_role'));
                }
                $this->addMultiCommand('listUsers', $this->lng->txt('grp_list_users'));
                break;
        }

        if ((bool) $a_allow_object_selection) {
            $this->addMultiCommand('selectObject', $this->lng->txt('grp_select_object'));
        }
    }
    
    /**
     * @param array $row
     * @return bool
     */
    public function fillRow($row)
    {
        /*
        TODO: Checkboxes must be always enabled now because of role assignment. An alternative to pretend showing
        an empty list of users could be a warning message
        */
        //if($row['member'])
        //{
        $this->tpl->setVariable('VAL_ID', $row['id']);
        //}
        $this->tpl->setVariable('VAL_TITLE', $row['title']);
        if (strlen($row['desc'])) {
            $this->tpl->setVariable('VAL_DESC', $row['desc']);
        }
        $this->tpl->setVariable('VAL_MEMBER', $row['member']);
        return true;
    }
    

    public function parseObjectIds(array $a_ids) : void
    {
        $data = [];
        foreach ($a_ids as $object_id) {
            $row = array();
            $type = ilObject::_lookupType($object_id);
            
            if ($type == 'role') {
                if ($this->review->isRoleDeleted($object_id)) {
                    continue;
                }
            }
            
            
            $row['title'] = ilObject::_lookupTitle($object_id);
            $row['desc'] = ilObject::_lookupDescription($object_id);
            $row['id'] = $object_id;
            
            switch ($type) {
                
                case 'crs':
                case 'grp':

                    if (ilParticipants::hasParticipantListAccess($object_id)) {
                        $row['member'] = count(ilParticipants::getInstanceByObjId($object_id)->getParticipants());
                    } else {
                        $row['member'] = 0;
                    }
                    break;
                    
                case 'role':
                    $row['member'] = count(ilUserFilter::getInstance()->filter($this->review->assignedUsers($object_id)));
                    break;
            }
            
            $data[] = $row;
        }
        $this->setData($data ? $data : array());
    }

    /**
     * @inheritDoc
     */
    public function numericOrdering($a_field)
    {
        if ($a_field == "member") {
            return true;
        }
        return false;
    }
}
