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
* @ingroup ModulesGroup
*/

class ilGroupParticipantsTableGUI extends ilTable2GUI
{
	protected $type = 'admin';
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj,$a_type = 'admin',$show_content = true)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
		$this->lng->loadLanguageModule('grp');
	 	$this->ctrl = $ilCtrl;
	 	
	 	$this->type = $a_type; 
	 	
	 	include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
	 	$this->privacy = ilPrivacySettings::_getInstance();
	 	
		parent::__construct($a_parent_obj,'members');

		$this->setFormName('participants');

	 	$this->addColumn('','f',"1");
	 	$this->addColumn($this->lng->txt('lastname'),'lastname','30%');
	 	$this->addColumn($this->lng->txt('login'),'login','25%');

	 	if($this->privacy->enabledAccessTimes())
	 	{
		 	$this->addColumn($this->lng->txt('last_access'),'access_time');
	 	}
		if($this->type == 'admin')
		{
			$this->setPrefix('admin');
			$this->setSelectAllCheckbox('admins');
		 	$this->addColumn($this->lng->txt('notification'),'notification');
			$this->addCommandButton('updateStatus',$this->lng->txt('grp_save_status'));
		}
		else
		{
			$this->setPrefix('member');
			$this->setSelectAllCheckbox('members');
		}
	 	$this->addColumn($this->lng->txt(''),'optional');
	 	
		$this->setRowTemplate("tpl.show_participants_row.html","Modules/Group");
		
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
		
		$this->tpl->setVariable('VAL_ID',$a_set['usr_id']);
		$this->tpl->setVariable('VAL_NAME',$a_set['lastname'].', '.$a_set['firstname']);
		
		if($this->privacy->enabledAccessTimes())
		{
			$this->tpl->setVariable('VAL_ACCESS',$a_set['access_time']);
		}
		if($this->type == 'admin')
		{
			$this->tpl->setVariable('VAL_POSTNAME','admins');
			$this->tpl->setVariable('VAL_NOTIFICATION_ID',$a_set['usr_id']);
			$this->tpl->setVariable('VAL_NOTIFICATION_CHECKED',$a_set['notification'] ? 'checked="checked"' : '');
		}
		else
		{
			$this->tpl->setVariable('VAL_POSTNAME','members');
		}
		
		$this->ctrl->setParameter($this->parent_obj,'member_id',$a_set['usr_id']);
		$this->tpl->setVariable('LINK_NAME',$this->ctrl->getLinkTarget($this->parent_obj,'editMember'));
		$this->tpl->setVariable('LINK_TXT',$this->lng->txt('edit'));
		$this->ctrl->clearParameters($this->parent_obj);
		
		$this->tpl->setVariable('VAL_LOGIN',$a_set['login']);

	
	}
	
}
?>