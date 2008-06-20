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
* GUI class for course/group waiting list
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesMembership 
*/
class ilWaitingListTableGUI extends ilTable2GUI
{
	protected $waiting_list = null;
	protected $wait = array();
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj,$waiting_list,$show_content = true)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
		$this->lng->loadLanguageModule('grp');
		$this->lng->loadLanguageModule('crs');
	 	$this->ctrl = $ilCtrl;
	 	
		parent::__construct($a_parent_obj,'members');

		$this->setFormName('waiting');
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj,'members'));

	 	$this->addColumn('','f',"1");
	 	$this->addColumn($this->lng->txt('lastname'),'name','20%');
	 	$this->addColumn($this->lng->txt('login'),'login','10%');
	 	$this->addColumn($this->lng->txt('application_date'),'sub_time',"60%");
	 	$this->addColumn('','mail','10%');
		
		$this->addMultiCommand('assignFromWaitingList',$this->lng->txt('assign'));
		$this->addMultiCommand('refuseFromList',$this->lng->txt('refuse'));
		$this->addMultiCommand('sendMailToSelectedUsers',$this->lng->txt('crs_mem_send_mail'));
		
		$this->setPrefix('waiting');
		$this->setSelectAllCheckbox('waiting');
		$this->setRowTemplate("tpl.show_waiting_list_row.html","Services/Membership");
		
		if($show_content)
		{
			$this->enable('sort');
			$this->enable('header');
			$this->enable('numinfo');
			$this->enable('select_all');
		}
		else
		{
			$this->disable('content');
			$this->disable('header');
			$this->disable('footer');
			$this->disable('numinfo');
			$this->disable('select_all');
		}	
		
		$this->waiting_list = $waiting_list;
	}
	
	/**
	 * set subscribers
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function setUsers($a_sub)
	{
		$this->wait = $a_sub;
		$this->readUserData();
	}
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($a_set)
	{
		global $ilUser;
		
				
		$this->tpl->setVariable('VAL_ID',$a_set['id']);
		$this->tpl->setVariable('VAL_NAME',$a_set['name']);
		$this->tpl->setVariable('VAL_SUBTIME',ilFormat::formatUnixTime($a_set['sub_time'],true));
		$this->tpl->setVariable('VAL_LOGIN',$a_set['login']);
		
		$this->ctrl->setParameterByClass(get_class($this->getParentObject()),'member_id',$a_set['id']);
		$link = $this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()),'sendMailToSelectedUsers');
		$this->tpl->setVariable('MAIL_LINK',$link);
		$this->tpl->setVariable('MAIL_TITLE',$this->lng->txt('crs_mem_send_mail'));
	}
	
	/**
	 * read data
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	public function readUserData()
	{
		foreach($this->wait as $usr_id => $usr_data)
		{
			$tmp_arr['id'] = $usr_id;
			$tmp_arr['sub_time'] = $usr_data['time'];
			
			$name = ilObjUser::_lookupName($usr_id);
			$tmp_arr['name'] = $name['lastname'].', '.$name['firstname'];
			$tmp_arr['login'] = '['.ilObjUser::_lookupLogin($usr_id).']';
			
			$wait[] = $tmp_arr;
		}
		$this->setData($wait ? $wait: array());
	}
	
}
?>