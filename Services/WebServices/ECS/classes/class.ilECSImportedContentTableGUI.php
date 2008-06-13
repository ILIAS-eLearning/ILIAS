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
include_once('classes/class.ilFormat.php');

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS
*/
class ilECSImportedContentTableGUI extends ilTable2GUI
{
	protected $lng;
	protected $ctrl;
	
	/**
	 * constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_parent_obj,$a_parent_cmd = '')
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
	 	
	 	parent::__construct($a_parent_obj,$a_parent_cmd);
	 	$this->addColumn($this->lng->txt('title'),'title','25%');
	 	$this->addColumn($this->lng->txt('ecs_imported_from'),'from','30%');
	 	$this->addColumn($this->lng->txt('ecs_meta_data'),'md','35%');
	 	$this->addColumn($this->lng->txt('last_update'),'last_update','10%');
		$this->setRowTemplate('tpl.content_row.html','Services/WebServices/ECS');
		$this->setDefaultOrderField('title');
		$this->setDefaultOrderDirection('asc');
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		
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
		include_once('./classes/class.ilLink.php');
		
		$this->tpl->setVariable('VAL_TITLE',$a_set['title']);
		#$this->tpl->setVariable('VAL_LINK',ilLink::_getLink($a_set['ref_id'],'rcrs'));
		$this->tpl->setVariable('VAL_DESC',$a_set['desc']);
		$this->tpl->setVariable('VAL_REMOTE',$a_set['from']);
		$this->tpl->setVariable('VAL_REMOTE_INFO',$a_set['from_info']);
		$this->tpl->setVariable('TXT_EMAIL',$this->lng->txt('ecs_email'));
		$this->tpl->setVariable('TXT_DNS',$this->lng->txt('ecs_dns'));
		$this->tpl->setVariable('TXT_ABR',$this->lng->txt('ecs_abr'));
		$this->tpl->setVariable('VAL_LAST_UPDATE',$a_set['last_update']);
		
		
		$this->tpl->setVariable('TXT_TERM',$this->lng->txt('ecs_field_term'));
		$this->tpl->setVariable('TXT_CRS_TYPE',$this->lng->txt('ecs_field_courseType'));
		$this->tpl->setVariable('TXT_CRS_ID',$this->lng->txt('ecs_field_courseID'));
		$this->tpl->setVariable('TXT_CREDITS',$this->lng->txt('ecs_field_credits'));
		$this->tpl->setVariable('TXT_ROOM',$this->lng->txt('ecs_field_room'));
		$this->tpl->setVariable('TXT_CYCLE',$this->lng->txt('ecs_field_cycle'));
		$this->tpl->setVariable('TXT_SWS',$this->lng->txt('ecs_field_semester_hours'));
		$this->tpl->setVariable('TXT_START',$this->lng->txt('ecs_field_begin'));
		$this->tpl->setVariable('TXT_END',$this->lng->txt('ecs_field_end'));
		$this->tpl->setVariable('TXT_LECTURER',$this->lng->txt('ecs_field_lecturer'));
		
		include_once('./Services/WebServices/ECS/classes/class.ilECSDataMappingSettings.php');
		$settings = ilECSDataMappingSettings::_getInstance();
		
		include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
		
		$values = ilAdvancedMDValues::_getValuesByObjId($a_set['obj_id']);
		
		if($field = $settings->getMappingByECSName('lecturer'))
		{
			$this->tpl->setVariable('VAL_LECTURER',isset($values[$field]) ? $values[$field] : '--');
		}
		if($field = $settings->getMappingByECSName('term'))
		{
			$this->tpl->setVariable('VAL_TERM',isset($values[$field]) ? $values[$field] : '--');
		}
		if($field = $settings->getMappingByECSName('courseID'))
		{
			$this->tpl->setVariable('VAL_CRS_ID',isset($values[$field]) ? $values[$field] : '--');
		}
		if($field = $settings->getMappingByECSName('courseType'))
		{
			$this->tpl->setVariable('VAL_CRS_TYPE',isset($values[$field]) ? $values[$field] : '--');
		}
		if($field = $settings->getMappingByECSName('credits'))
		{
			$this->tpl->setVariable('VAL_CREDITS',isset($values[$field]) ? $values[$field] : '--');
		}
		if($field = $settings->getMappingByECSName('semester_hours'))
		{
			$this->tpl->setVariable('VAL_SWS',isset($values[$field]) ? $values[$field] : '--');
		}
		if($field = $settings->getMappingByECSName('room'))
		{
			$this->tpl->setVariable('VAL_ROOM',isset($values[$field]) ? $values[$field] : '--');
		}
		if($field = $settings->getMappingByECSName('cycle'))
		{
			$this->tpl->setVariable('VAL_CYCLE',isset($values[$field]) ? $values[$field] : '--');
		}
		if($field = $settings->getMappingByECSName('begin'))
		{
			$this->tpl->setVariable('VAL_START',isset($values[$field]) ? ilFormat::formatUnixTime($values[$field],true) : '--');
		}
		if($field = $settings->getMappingByECSName('end'))
		{
			$this->tpl->setVariable('VAL_END',isset($values[$field]) ? ilFormat::formatUnixTime($values[$field],true) : '--');
		}
	}
	
	/**
	 * Parse
	 *
	 * @access public
	 * @param array array of remote course ids
	 * 
	 */
	public function parse($a_rcrs)
	{
		global $ilObjDataCache;
		
		// Preload object data
		$ilObjDataCache->preloadReferenceCache($a_rcrs);
		
		// Read participants
		include_once('./Modules/RemoteCourse/classes/class.ilObjRemoteCourse.php');
		include_once('./Services/WebServices/ECS/classes/class.ilECSCommunityReader.php');
		try
		{
			$reader = ilECSCommunityReader::_getInstance();
		}
		catch(ilECSConnectorException $e)
		{
			$reader = null;
		}
		
		// read obj_ids
		$obj_ids = array();
		foreach($a_rcrs as $rcrs_ref_id)
		{
			$obj_id = $ilObjDataCache->lookupObjId($rcrs_ref_id);
			$obj_ids[$obj_id] = $ilObjDataCache->lookupObjId($rcrs_ref_id);
		}
		
		foreach($obj_ids as $obj_id => $obj_id)
		{
			$tmp_arr['ref_id'] = $ref_id;
			$tmp_arr['obj_id'] = $obj_id;
			$tmp_arr['title'] = $ilObjDataCache->lookupTitle($obj_id);
			$tmp_arr['desc'] = $ilObjDataCache->lookupDescription($obj_id);
			$tmp_arr['md'] = '';
			
			$mid = ilObjRemoteCourse::_lookupMID($obj_id);		
					
			if($reader and ($participant = $reader->getParticipantByMID($mid)))
			{
				$tmp_arr['from'] = $participant->getParticipantName();
				$tmp_arr['from_info'] = $participant->getDescription();
			}
			$tmp_arr['last_update'] = $ilObjDataCache->lookupLastUpdate($obj_id);
			$content[] = $tmp_arr;
		}
		
	 	$this->setData($content ? $content : array());
	}
	
}
?>