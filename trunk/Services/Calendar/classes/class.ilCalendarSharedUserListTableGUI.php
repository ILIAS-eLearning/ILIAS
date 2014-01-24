<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once('./Services/Table/classes/class.ilTable2GUI.php');


/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilCalendarSharedUserListTableGUI extends ilTable2GUI
{
	protected $user_ids = array();
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object gui object
	 * @param string oparent command
	 * @return
	 */
	public function __construct($parent_obj,$parent_cmd)
	{
		parent::__construct($parent_obj,$parent_cmd);
		
		$this->setRowTemplate('tpl.calendar_shared_user_list_row.html','Services/Calendar');
		
		$this->addColumn('','id','1px');
		$this->addColumn($this->lng->txt('name'),'last_firstname','60%');
		$this->addColumn($this->lng->txt('login'),'login','40%');
		
		$this->addMultiCommand('shareAssign',$this->lng->txt('cal_share_cal'));
		$this->addMultiCommand('shareAssignEditable',$this->lng->txt('cal_share_cal_editable'));
		$this->setSelectAllCheckbox('user_ids');
		$this->setPrefix('search');
	}
	
	/**
	 * set users
	 *
	 * @access public
	 * @param array array of user ids
	 * @return bool
	 */
	public function setUsers($a_user_ids)
	{
		$this->user_ids = $a_user_ids;
	}
	
	/**
	 * fill row
	 *
	 * @access protected
	 * @return
	 */
	public function fillRow($a_set)
	{
		$this->tpl->setVariable('VAL_ID',$a_set['id']);
		
		$this->tpl->setVariable('LASTNAME',$a_set['lastname']);
		$this->tpl->setVariable('FIRSTNAME',$a_set['firstname']);
		$this->tpl->setVariable('LOGIN',$a_set['login']);
	}
	
	
	/**
	 * parse
	 *
	 * @access public
	 * @return
	 */
	public function parse()
	{
		
		$users = array();
		foreach($this->user_ids as $id)
		{
			$name = ilObjUser::_lookupName($id);
			
			$tmp_data['id'] = $id;
			$tmp_data['lastname']  = $name['lastname'];
			$tmp_data['firstname'] = $name['firstname'];
			$tmp_data['login'] = ilObjUser::_lookupLogin($id);
			$tmp_data['last_firstname'] = $tmp_data['lastname'].$tmp_data['firstname'].$tmp_data['login'];
			
			$users[] = $tmp_data;
		}

		$this->setData($users ? $users : array());
	}
	
	
}
?>