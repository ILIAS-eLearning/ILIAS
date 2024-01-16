<?php

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
 *********************************************************************/

declare(strict_types=1);

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

        $this->setId("group_table");

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
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->enable('select_all');
        $this->setSelectAllCheckbox("obj[]");

        switch ($this->parent_obj->getSearchType()) {
            case 'grp':
            case 'crs':
                if ($this->parent_obj->getRoleCallback()) {
                    $this->addMultiCommand('addRole', $this->lng->txt('add_member_role'));
                }
                $this->addMultiCommand('listUsers', $this->lng->txt('grp_list_members'));
                break;

            case 'role':
                if ($this->parent_obj->getRoleCallback()) {
                    $this->addMultiCommand('addRole', $this->lng->txt('add_role'));
                }
                $this->addMultiCommand('listUsers', $this->lng->txt('grp_list_users'));
                break;
        }

        if ($a_allow_object_selection) {
            $this->addMultiCommand('selectObject', $this->lng->txt('grp_select_object'));
        }
    }

    /**
     * @param array $a_set
     * @return void
     */
    protected function fillRow(array $a_set): void
    {
        /*
        TODO: Checkboxes must be always enabled now because of role assignment. An alternative to pretend showing
        an empty list of users could be a warning message
        */
        //if($row['member'])
        //{
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        //}
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (strlen($a_set['desc'])) {
            $this->tpl->setVariable('VAL_DESC', $a_set['desc']);
        }
        $this->tpl->setVariable('VAL_MEMBER', $a_set['member']);
    }


    public function parseObjectIds(array $a_ids): void
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
        $this->setData($data);
    }

    /**
     * @inheritDoc
     */
    public function numericOrdering(string $a_field): bool
    {
        if ($a_field == "member") {
            return true;
        }
        return false;
    }
}
