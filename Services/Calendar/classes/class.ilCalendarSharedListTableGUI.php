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
include_once('./Services/Calendar/classes/class.ilCalendarShared.php');

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilCalendarSharedListTableGUI extends ilTable2GUI 
{
	protected $calendar_id;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object gui object
	 * @param string oparent command
	 */
	public function __construct($parent_obj,$parent_cmd)
	{
		global $ilCtrl;
		
		parent::__construct($parent_obj,$parent_cmd);
		
		$this->setRowTemplate('tpl.calendar_shared_list_row.html','Services/Calendar');
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
		
		$this->addColumn('','id','1px');
		$this->addColumn($this->lng->txt('type'),'type','1px');
		$this->addColumn($this->lng->txt('title'),'title','80%');
		$this->addColumn($this->lng->txt('cal_shared_access_table_col'),'access','20%');
		
		$this->addMultiCommand('shareDeassign',$this->lng->txt('cal_unshare_cal'));
		$this->setPrefix('shared');
		$this->setSelectAllCheckbox('obj_ids');
	}
	
	/**
	 * set ids
	 *
	 * @access public
	 * @param array array of object ids
	 * @return bool
	 */
	public function setCalendarId($a_calendar_id)
	{
		$this->calendar_id = $a_calendar_id;
	}
	
	/**
	 * fill row
	 *
	 * @access public
	 * @return
	 */
	public function fillRow($a_set)
	{
		$this->tpl->setVariable('VAL_ID',$a_set['obj_id']);
		$this->tpl->setVariable('NAME',$a_set['title']);
		
		if(strlen($a_set['description']))
		{
			$this->tpl->setVariable('DESCRIPTION',$a_set['description']);	
		}
		
		$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_'.$a_set['type'].'.svg'));
		$this->tpl->setVariable('ALT_IMG',$this->lng->txt('obj_'.$a_set['type']));
		
		if($a_set['writable'])
		{
			$this->tpl->setVariable('CAL_ACCESS',$this->lng->txt('cal_shared_access_read_write'));
		}
		else
		{
			$this->tpl->setVariable('CAL_ACCESS',$this->lng->txt('cal_shared_access_read_only'));
		}
		
	}
	
	/**
	 * parse
	 *
	 * @access public
	 * @return
	 */
	public function parse()
	{
		$this->shared_obj = new ilCalendarShared($this->calendar_id);

		$items = array();
		foreach($this->shared_obj->getShared() as $item)
		{
			switch($item['obj_type'])
			{
				case ilCalendarShared::TYPE_USR:
					$data['type'] = 'usr';
					
					$name = ilObjUser::_lookupName($item['obj_id']);
					$data['title'] = $name['lastname'].', '.$name['firstname'];
					$data['description'] = '';
					break;
					
					
				case ilCalendarShared::TYPE_ROLE:
					$data['type'] = 'role';
					$data['title'] = ilObject::_lookupTitle($item['obj_id']);
					$data['description'] = ilObject::_lookupDescription($item['obj_id']);
					break;
			}
			$data['obj_id'] = $item['obj_id'];
			$data['create_date'] = $item['create_date'];
			$data['writable'] = $item['writable'];
			
			$items[] = $data;
		}
		$this->setData($items ? $items : array());
		return true;
	}
	
	
	
	
}
?>