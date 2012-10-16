<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class object (course,group and role) search results
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesSearch
*/
class ilRepositoryObjectResultTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 * @return 
	 * @param object $a_parent_obj
	 * @param object $a_parent_cmd
	 */
	public function __construct($a_parent_obj,$a_parent_cmd,$a_allow_object_selection = false)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("title"), "title", "80%");
		$this->addColumn($this->lng->txt("members"), "member", "20%");
		
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj));
		$this->setRowTemplate("tpl.rep_search_obj_result_row.html", "Services/Search");
		$this->setTitle($this->lng->txt('search_results'));
		$this->setEnableTitle(true);
		$this->setId("group_table");
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		$this->enable('select_all');
		$this->setSelectAllCheckbox("obj[]");

		$this->addMultiCommand('listUsers', $this->lng->txt('grp_list_users'));
		if((bool)$a_allow_object_selection)
		{
			$this->addMultiCommand('selectObject', $this->lng->txt('grp_select_object'));
		}
	}
	
	/**
	 * 
	 * @return 
	 * @param object $row
	 */
	public function fillRow($row)
	{
		$this->tpl->setVariable('VAL_ID',$row['id']);
		$this->tpl->setVariable('VAL_TITLE',$row['title']);
		if(strlen($row['desc']))
		{
			$this->tpl->setVariable('VAL_DESC',$row['desc']);
		}
		$this->tpl->setVariable('VAL_MEMBER',$row['member']);
		return true;
	}
	
	
	/**
	 * Parse object data
	 * @return 
	 * @param object $a_ids
	 */
	public function parseObjectIds($a_ids)
	{
		foreach($a_ids as $object_id)
		{
			$row = array();
			$type = ilObject::_lookupType($object_id);
			
			$row['title'] = ilObject::_lookupTitle($object_id);
			$row['desc'] = ilObject::_lookupDescription($object_id);
			$row['id'] = $object_id;
			
			switch($type)
			{
				case 'grp':
					include_once './Modules/Group/classes/class.ilGroupParticipants.php';
					$part = ilGroupParticipants::_getInstanceByObjId($object_id);
					include_once './Services/User/classes/class.ilUserFilter.php';
					$row['member'] = count(ilUserFilter::getInstance()->filter($part->getParticipants()));
					break;

				case 'crs':
					include_once './Modules/Course/classes/class.ilCourseParticipants.php';
					$part = ilCourseParticipants::_getInstanceByObjId($object_id);
					include_once './Services/User/classes/class.ilUserFilter.php';
					$row['member'] = count(ilUserFilter::getInstance()->filter($part->getParticipants()));
					break;
					
				case 'role':
					global $rbacreview;
					include_once './Services/User/classes/class.ilUserFilter.php';
					$row['member'] = count(ilUserFilter::getInstance()->filter($rbacreview->assignedUsers($object_id)));
					break;
			}
			
			$data[] = $row;
		}
		$this->setData($data ? $data : array());
	}
}
?>