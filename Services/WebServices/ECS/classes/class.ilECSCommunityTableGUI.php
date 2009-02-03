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

include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once('Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php');

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS
*/
class ilECSCommunityTableGUI extends ilTable2GUI
{
	protected $lng;
	protected $ctrl;
	
	protected $part_settings = null;
	
	/**
	 * constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_parent_obj,$a_parent_cmd = '',$cid = 0)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
	 	
	 	parent::__construct($a_parent_obj,$a_parent_cmd);
	 	$this->addColumn('','mid',1);
	 	$this->addColumn($this->lng->txt('ecs_participants'),'participants',"50%");
	 	$this->addColumn($this->lng->txt('ecs_participants_infos'),'infos',"48%");
	 	$this->addColumn($this->lng->txt('ecs_active_header'),'active',"2%");
		$this->disable('form');	 	
		$this->setRowTemplate("tpl.participant_row.html","Services/WebServices/ECS");
		$this->setDefaultOrderField('participants');
		$this->setDefaultOrderDirection("desc");
		
		$this->part_settings = ilECSParticipantSettings::_getInstance();
	}
	
	/**
	 * Fill row
	 *
	 * @access public
	 * @param array row data
	 * 
	 */
	public function fillRow($a_set)
	{
		$this->tpl->setVariable('VAL_ID',$a_set['mid']);
		$this->tpl->setVariable('VAL_CHECKED',$a_set['checked'] ? 'checked="checked"' : '');
		$this->tpl->setVariable('VAL_TITLE',$a_set['participants']);
		$this->tpl->setVariable('VAL_DESC',$a_set['description']);
		$this->tpl->setVariable('VAL_EMAIL',$a_set['email']);
		$this->tpl->setVariable('VAL_DNS',$a_set['dns']);
		$this->tpl->setVariable('VAL_ABR',$a_set['abr']);
		$this->tpl->setVariable('TXT_EMAIL',$this->lng->txt('ecs_email'));
		$this->tpl->setVariable('TXT_DNS',$this->lng->txt('ecs_dns'));
		$this->tpl->setVariable('TXT_ABR',$this->lng->txt('ecs_abr'));
		$this->tpl->setVariable('TXT_ID',$this->lng->txt('ecs_unique_id'));
		
		
		
		if($a_set['checked'])
		{
			$this->tpl->setVariable('IMAGE_OK',ilUtil::getImagePath('icon_ok.gif'));
			$this->tpl->setVariable('TXT_OK',$this->lng->txt('ecs_active'));
		}
		else
		{
			$this->tpl->setVariable('IMAGE_OK',ilUtil::getImagePath('icon_not_ok.gif'));
			$this->tpl->setVariable('TXT_OK',$this->lng->txt('ecs_inactive'));
		}
	}
	
	/**
	 * Parse
	 *
	 * @access public
	 * @param array array of LDAPRoleAssignmentRule
	 * 
	 */
	public function parse($a_participants)
	{
		foreach($a_participants as $participant)
		{
			$tmp_arr['checked'] = $this->part_settings->isEnabled($participant->getMID());
			$tmp_arr['mid'] = $participant->getMID();
			$tmp_arr['participants'] = $participant->getParticipantName();
			$tmp_arr['description'] = $participant->getDescription();
			$tmp_arr['email'] = $participant->getEmail();
			$tmp_arr['dns'] = $participant->getDNS();
			$tmp_arr['abr'] = $participant->getAbbreviation();
			$def[] = $tmp_arr;
		}
		
	 	$this->setData($def ? $def : array());
	}
	
}


?>