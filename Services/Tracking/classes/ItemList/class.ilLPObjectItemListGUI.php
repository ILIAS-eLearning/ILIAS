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

/**
* Class ilLPItemListGUI
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*
*/

include_once 'Services/Tracking/classes/ItemList/class.ilLPItemListGUI.php';

class ilLPObjectItemListGUI extends ilLPItemListGUI
{
	var $references = array();


	function ilLPObjectItemListGUI($a_id,$a_type)
	{
		parent::ilLPItemListGUI($a_id,$a_type);
	}

	function addReferences($a_references)
	{
		$this->references = $a_references;
	}


	// Overwritten methods
	function __readTitle()
	{
		global $ilObjDataCache;
		$this->title = $ilObjDataCache->lookupTitle($this->getId());
	}
	function __readDescription()
	{
		global $ilObjDataCache;
		$this->description = $ilObjDataCache->lookupDescription($this->getId());
	}
	function __readStatusInfo()
	{
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
		$this->status_info = ilLPStatusWrapper::_getStatusInfo($this->getId());
	}

	function __readMark()
	{
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';
		$this->mark = ilLPMarks::_lookupMark($this->getCurrentUser(),$this->getId());
	}

	function __readComment()
	{
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';
		$this->comment = ilLPMarks::_lookupComment($this->getCurrentUser(),$this->getId());
	}

	function __readTypicalLearningTime()
	{
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
		$this->tlt = ilLPStatusWrapper::_getTypicalLearningTime($this->getId());
	}

	function __readUserStatus()
	{
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';

		if(in_array($this->getCurrentUser(),ilLPStatusWrapper::_getInProgress($this->getId())))
		{
			$this->status = LP_STATUS_IN_PROGRESS;
		}
		elseif(in_array($this->getCurrentUser(),ilLPStatusWrapper::_getCompleted($this->getId())))
		{
			$this->status = LP_STATUS_COMPLETED;
		}
		elseif(in_array($this->getCurrentUser(),ilLPStatusWrapper::_getFailed($this->getId())))
		{
			$this->status = LP_STATUS_FAILED;
		}
		else
		{
			$this->status = LP_STATUS_NOT_ATTEMPTED;
		}
	}

	function __readUserStatusInfo()
	{
		global $ilObjDataCache;

		$this->user_status_info = null;

		switch($this->getMode())
		{
			case LP_MODE_TEST_PASSED:
				// Get stored test results
				$found = false;
				foreach($this->status_info['results'] as $res)
				{
					if($this->getCurrentUser() == $res['user_id'])
					{
						$this->user_status_info = array($this->lng->txt('trac_reached_points'),
														$this->__getPercent($res['max_points'],$res['reached_points']));
						return true;
					}
				}
				return $this->user_status_info = array($this->lng->txt('trac_reached_points'),
													   "0.00%");

			case LP_MODE_TLT:
				if(!$this->status_info['tlt'])
				{
					return false;
				}
				include_once './Services/Tracking/classes/class.ilLearningProgress.php';
				$user_data = ilLearningProgress::_getProgress($this->getCurrentUser(),$this->getId());

				return $this->user_status_info = array($this->lng->txt('trac_edit_time'),
													   $this->__getPercent($this->status_info['tlt'],$user_data['spent_seconds']));

			case LP_MODE_VISITS:
				if(!$this->status_info['visits'])
				{
					return false;
				}

				include_once './Services/Tracking/classes/class.ilLearningProgress.php';
				$user_data = ilLearningProgress::_getProgress($this->getCurrentUser(),$this->getId());

				return $this->user_status_info = array($this->lng->txt('trac_reached_visits'),
													   $this->__getPercent($this->status_info['visits'],$user_data['visits']));

			case LP_MODE_OBJECTIVES:
				if(!$this->status_info['num_objectives'])
				{
					return false;
				}
				$fullfilled = $this->status_info['objective_result'][$this->getCurrentUser()] ? 
					$this->status_info['objective_result'][$this->getCurrentUser()] : array();
				return $this->user_status_info = array($this->lng->txt('trac_reached_objectives'),
													   $this->__getPercent($this->status_info['num_objectives'],
																		   count($fullfilled)));
			case LP_MODE_SCORM:
				if(!$this->status_info['num_scos'])
				{
					return false;
				}
				return $this->user_status_info = array($this->lng->txt('trac_edited_scos'),
													   $this->__getPercent($this->status_info['num_scos'],
																		   $this->status_info['num_completed'][$this->getCurrentUser()]));

			case LP_MODE_COLLECTION:
				include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
				include_once 'Services/Tracking/classes/class.ilLearningProgress.php';

				$user_time = 0;
				if(!is_array($this->status_info['collections']) or !$this->tlt)
				{
					return false;
				}
				foreach($this->status_info['collections'] as $item)
				{
					$object_id = $ilObjDataCache->lookupObjId($item);

					// count full time if status is completed 
					if(in_array($this->getCurrentUser(),ilLPStatusWrapper::_getCompleted($object_id)))
					{
						$user_time += ilLPStatusWrapper::_getTypicalLearningTime($object_id);
						continue;
					}

					switch($ilObjDataCache->lookupType($object_id))
					{
						case 'lm':
							$progress = ilLearningProgress::_getProgress($this->getCurrentUser(),$object_id);
							$user_time += min($progress['spent_seconds'],
											  ilLPStatusWrapper::_getTypicalLearningTime($object_id));
							break;
					}
				}

				if($user_time)
				{
					$this->user_status_info = array($this->lng->txt('trac_processing_time'),
													$this->__getPercent($this->tlt,$user_time));
				}
				break;
		}
	}

	function renderPath($a_force_details = true)
	{
		include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
		include_once 'classes/class.ilLink.php';

		if(!count($this->references))
		{
			return true;
		}
		foreach($this->references as $ref_id)
		{
			$path = '...';
			$counter = 0;
			$path_full = $this->tree->getPathFull($ref_id);
			foreach($path_full as $data)
			{
				if(++$counter < (count($path_full)-1))
				{
					continue;
				}
				$path .= " -> ";
				if($ref_id != $data['ref_id'])
				{
					$path .= $data['title'];
				}
				else
				{
					$path .= ('<a target="_top" href="'.
							  ilLink::_getLink($data['ref_id'],$data['type']).'">'.
							  $data['title'].'</a>');
				}
			}
			$this->tpl->setCurrentBlock("path_item");

			#var_dump("<pre>",$a_force_details,ilObjUserTracking::_enabledUserRelatedData(),!$this->isAnonymized(),"<pre>");

			if($a_force_details or (ilObjUserTracking::_enabledUserRelatedData() and !$this->isAnonymized()))
			{
				$this->ctrl->setParameterByClass($this->getCmdClass(),'details_id',$ref_id);
				$this->tpl->setVariable("PATH_DETAILS",$this->ctrl->getLinkTargetByClass($this->getCmdClass(),'details'));
				$this->tpl->setVariable("TXT_PATH_DETAILS",$this->lng->txt('details'));
			}

			$this->tpl->setVariable("OCCURRENCES",$this->lng->txt('trac_occurrences'));
			$this->tpl->setVariable("PATH_ITEM",$path);
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("path");
			$this->tpl->parseCurrentBlock();
		}
		return true;
	}



}
?>