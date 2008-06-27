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
* @ingroup ModulesCourse
*/

class ilCourseParticipantsTableGUI extends ilTable2GUI
{
	protected $type = 'admin';
	protected $show_learning_progress = false;
	protected $show_timings = false;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj,$a_type = 'admin',$show_content = true,$a_show_learning_progress = false,$a_show_timings = false)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->show_learning_progress = $a_show_learning_progress;
	 	$this->show_timings = $a_show_timings;
	 	
	 	$this->lng = $lng;
		$this->lng->loadLanguageModule('crs');
		$this->lng->loadLanguageModule('trac');
	 	$this->ctrl = $ilCtrl;
	 	
	 	$this->type = $a_type; 
	 	
	 	include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
	 	$this->privacy = ilPrivacySettings::_getInstance();
	 	
		parent::__construct($a_parent_obj,'members');

		$this->setFormName('participants');

	 	$this->addColumn('','f',"1");
	 	$this->addColumn($this->lng->txt('lastname'),'lastname','20%');
	 	$this->addColumn($this->lng->txt('login'),'login','10%');

		if($this->show_learning_progress)
		{
			$this->addColumn($this->lng->txt('learning_progress'),'progress');
		}

	 	if($this->privacy->enabledCourseAccessTimes())
	 	{
		 	$this->addColumn($this->lng->txt('last_access'),'access_time','16em');
	 	}
		$this->addColumn($this->lng->txt('crs_member_passed'),'passed');
		if($this->type == 'admin')
		{
			$this->setPrefix('admin');
			$this->setSelectAllCheckbox('admins');
		 	$this->addColumn($this->lng->txt('crs_notification'),'notification');
			$this->addCommandButton('updateAdminStatus',$this->lng->txt('save'));
		}
		elseif($this->type == 'tutor')
		{
			$this->setPrefix('tutor');
			$this->setSelectAllCheckbox('tutors');
		 	$this->addColumn($this->lng->txt('crs_notification'),'notification');
			$this->addCommandButton('updateTutorStatus',$this->lng->txt('save'));
		}
		else
		{
			$this->setPrefix('member');
			$this->addColumn($this->lng->txt('crs_blocked'),'blocked');
			$this->setSelectAllCheckbox('members');
			$this->addCommandButton('updateMemberStatus',$this->lng->txt('save'));
		}
	 	$this->addColumn($this->lng->txt(''),'optional');
	 	
		$this->setRowTemplate("tpl.show_participants_row.html","Modules/Course");
		
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
		global $ilUser,$ilAccess;
		
		$this->tpl->setVariable('VAL_ID',$a_set['usr_id']);
		$this->tpl->setVariable('VAL_NAME',$a_set['lastname'].', '.$a_set['firstname']);
		
		if(!$ilAccess->checkAccessOfUser($a_set['usr_id'],'read','',$this->getParentObject()->object->getRefId()) and 
			is_array($info = $ilAccess->getInfo()))
		{
			$this->tpl->setVariable('PARENT_ACCESS',$info[0]['text']);
		}
		
		
		if($this->privacy->enabledCourseAccessTimes())
		{
			$this->tpl->setVariable('VAL_ACCESS',$a_set['access_time']);
		}
		if($this->show_learning_progress)
		{
			$this->tpl->setCurrentBlock('lp');
			switch($a_set['progress'])
			{
				case LP_STATUS_COMPLETED:
					$this->tpl->setVariable('LP_STATUS_ALT',$this->lng->txt($a_set['progress']));
					$this->tpl->setVariable('LP_STATUS_PATH',ilUtil::getImagePath('scorm/complete.gif'));
					break;
					
				case LP_STATUS_IN_PROGRESS:
					$this->tpl->setVariable('LP_STATUS_ALT',$this->lng->txt($a_set['progress']));
					$this->tpl->setVariable('LP_STATUS_PATH',ilUtil::getImagePath('scorm/incomplete.gif'));
					break;

				case LP_STATUS_NOT_ATTEMPTED:
					$this->tpl->setVariable('LP_STATUS_ALT',$this->lng->txt($a_set['progress']));
					$this->tpl->setVariable('LP_STATUS_PATH',ilUtil::getImagePath('scorm/not_attempted.gif'));
					break;
					
				case LP_STATUS_FAILED:
					$this->tpl->setVariable('LP_STATUS_ALT',$this->lng->txt($a_set['progress']));
					$this->tpl->setVariable('LP_STATUS_PATH',ilUtil::getImagePath('scorm/failed.gif'));
					break;
							
			}
			$this->tpl->parseCurrentBlock();
		}
		
		if($this->type == 'admin')
		{
			$this->tpl->setVariable('VAL_POSTNAME','admins');
			$this->tpl->setVariable('VAL_NOTIFICATION_ID',$a_set['usr_id']);
			$this->tpl->setVariable('VAL_NOTIFICATION_CHECKED',$a_set['notification'] ? 'checked="checked"' : '');
		}
		elseif($this->type == 'tutor')
		{
			$this->tpl->setVariable('VAL_POSTNAME','tutors');
			$this->tpl->setVariable('VAL_NOTIFICATION_ID',$a_set['usr_id']);
			$this->tpl->setVariable('VAL_NOTIFICATION_CHECKED',$a_set['notification'] ? 'checked="checked"' : '');
		}
		else
		{
			$this->tpl->setCurrentBlock('blocked');
			$this->tpl->setVariable('VAL_BLOCKED_ID',$a_set['usr_id']);
			$this->tpl->setVariable('VAL_BLOCKED_CHECKED',$a_set['blocked'] ? 'checked="checked"' : '');
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setVariable('VAL_POSTNAME','members');
		}
		
		$this->tpl->setVariable('VAL_PASSED_ID',$a_set['usr_id']);
		$this->tpl->setVariable('VAL_PASSED_CHECKED',$a_set['passed'] ? 'checked="checked"' : '');
		
		
		$this->ctrl->setParameter($this->parent_obj,'member_id',$a_set['usr_id']);
		$this->tpl->setCurrentBlock('link');
		$this->tpl->setVariable('LINK_NAME',$this->ctrl->getLinkTarget($this->parent_obj,'editMember'));
		$this->tpl->setVariable('LINK_TXT',$this->lng->txt('edit'));
		$this->tpl->parseCurrentBlock();
		$this->ctrl->clearParameters($this->parent_obj);
		
		
		if($this->show_timings)
		{
			$this->ctrl->setParameterByClass('ilcoursecontentgui','member_id',$a_set['usr_id']);
			$this->tpl->setCurrentBlock('link');
			$this->tpl->setVariable('LINK_NAME',$this->ctrl->getLinkTargetByClass('ilcoursecontentgui','showUserTimings'));
			$this->tpl->setVariable('LINK_TXT',$this->lng->txt('timings_timings'));
			$this->tpl->parseCurrentBlock();
		}
		
		
		$this->tpl->setVariable('VAL_LOGIN',$a_set['login']);
	}
	
}
?>