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
* Class ilLPListOfSettingsGUI
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilLPListOfSettingsGUI:
*
* @package ilias-tracking
*
*/

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPObjSettings.php';

class ilLPListOfSettingsGUI extends ilLearningProgressBaseGUI
{
	function ilLPListOfSettingsGUI($a_mode,$a_ref_id)
	{
		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id);

		$this->obj_settings = new ilLPObjSettings($this->getObjId());
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		switch($this->ctrl->getNextClass())
		{
			default:
				$cmd = $this->__getDefaultCommand();
				$this->$cmd();

		}
		return true;
	}

	function show()
	{
		// Sub Tabs

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_obj_settings.html','Services/Tracking');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormaction($this));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_trac.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('tracking_settings'));
		$this->tpl->setVariable("TXT_TRACKING_SETTINGS", $this->lng->txt("tracking_settings"));

		$this->tpl->setVariable("TXT_ACTIVATE_TRACKING", $this->lng->txt("trac_activated"));
		$this->tpl->setVariable("ACTIVATED_IMG_OK",$activated = ilObjUserTracking::_enabledLearningProgress()
								? ilUtil::getImagePath('icon_ok.gif') 
								: ilUtil::getImagePath('icon_not_ok.gif'));
		$this->tpl->setVariable("ACTIVATED_STATUS",$activated ? $this->lng->txt('yes') : $this->lng->txt('no'));

		$this->tpl->setVariable("TXT_USER_RELATED_DATA", $this->lng->txt("trac_anonymized"));
		$this->tpl->setVariable("ANONYMIZED_IMG_OK",$anonymized = !ilObjUserTracking::_enabledUserRelatedData()
								? ilUtil::getImagePath('icon_ok.gif') 
								: ilUtil::getImagePath('icon_not_ok.gif'));
		$this->tpl->setVariable("ANONYMIZED_STATUS",$anonymized ? $this->lng->txt('no') : $this->lng->txt('yes'));

		$this->tpl->setVariable("TXT_VALID_REQUEST",$this->lng->txt('trac_valid_request'));
		$this->tpl->setVariable("INFO_VALID_REQUEST",$this->lng->txt('info_valid_request'));
		$this->tpl->setVariable("SECONDS",$this->lng->txt('seconds'));
		$this->tpl->setVariable("VAL_SECONDS",ilObjUserTracking::_getValidTimeSpan());

		$this->showModeSelection();

		if($this->obj_settings->getMode() == LP_MODE_VISITS)
		{
			$this->tpl->setCurrentBlock("visits");
			$this->tpl->setVariable("TXT_VISITS",$this->lng->txt('trac_num_visits'));
			$this->tpl->setVariable("NUM_VISITS",$this->obj_settings->getVisits());
			$this->tpl->setVariable("INFO_VISITS",$this->lng->txt('trac_visits_info'));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TXT_SAVE",$this->lng->txt('save'));

		// Show additional tables (e.g collection table)
		$this->__showTablesByMode();
	}

	function saveSettings()
	{
		$this->__addInfo();
		$this->obj_settings->setMode($_POST['modus']);
		if((int) $_POST['visits'])
		{
			$this->obj_settings->setVisits((int) $_POST['visits']);
		}
		$this->obj_settings->update();
		$this->show();
	}

	function assign()
	{
		if(!$_POST['item_ids'] and !$_POST['event_ids'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->show();
			return false;
		}
		if(count($_POST['item_ids']))
		{
			include_once 'Services/Tracking/classes/class.ilLPCollections.php';
			$lp_collections = new ilLPCollections($this->getObjId());
			foreach($_POST['item_ids'] as $ref_id)
			{
				$lp_collections->add($ref_id);
			}
		}
		ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'));
		$this->show();
	}

	function deassign()
	{
		if(!$_POST['item_ids'] and !$_POST['event_ids'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->show();
			return false;
		}
		if(count($_POST['item_ids']))
		{
			include_once 'Services/Tracking/classes/class.ilLPCollections.php';
			$lp_collections = new ilLPCollections($this->getObjId());
			foreach($_POST['item_ids'] as $ref_id)
			{
				$lp_collections->delete($ref_id);
			}
		}
		ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'));
		$this->show();
	}
	
	function __showTablesByMode()
	{
		switch($this->obj_settings->getMode())
		{
			case LP_MODE_MANUAL_BY_TUTOR:
			case LP_MODE_COLLECTION:

				$this->__showCollectionTable();
				break;

			case LP_MODE_SCORM:
				
				$this->__showSCOTable();
				break;

		}
		return true;
	}

	function __showSCOTable()
	{
		global $ilObjDataCache,$tree;

		include_once 'Services/Tracking/classes/class.ilLPCollections.php';
		include_once './Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php';


		if(!$items = ilLPCollections::_getPossibleSAHSItems($this->getObjId()))
		{
			ilUtil::sendFailure($this->lng->txt('trac_no_sahs_items_found'));
			return false;
		}

		$lp_collections = new ilLPCollections($this->getObjId());
		$tpl =& new ilTemplate('tpl.trac_collections.html',true,true,'Services/Tracking');

		//$tpl->setVariable("COLL_TITLE_IMG_ALT",$this->lng->txt('trac_assignments'));
		$tpl->setVariable("COLL_TITLE_IMG_ALT",$this->lng->txt('trac_lp_determination'));
		$tpl->setVariable("COLL_TITLE_IMG",ilUtil::getImagePath('icon_trac.gif'));
		//$tpl->setVariable("TABLE_TITLE", $this->lng->txt('trac_assignments'));
		$tpl->setVariable("TABLE_TITLE", $this->lng->txt('trac_lp_determination'));
		$tpl->setVariable("TABLE_INFO", $this->lng->txt('trac_lp_determination_info_sco'));
		//$tpl->setVariable("ITEM_DESC",$this->lng->txt('description'));
		//$tpl->setVariable("ITEM_ASSIGNED",$this->lng->txt('trac_assigned'));

		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->setVariable("BTN_ASSIGN",$this->lng->txt('trac_collection_assign'));
		$tpl->setVariable("BTN_DEASSIGN",$this->lng->txt('trac_collection_deassign'));

		
		$counter = 0;

		$tpl->addBlockFile('MATERIALS','materials','tpl.trac_collections_sco_row.html','Services/Tracking');
		$counter = 0;
		foreach($items as $obj_id => $data)
		{
			$tpl->setCurrentBlock("materials");
			$tpl->setVariable("COLL_TITLE",$data['title']);
			$tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$tpl->setVariable("CHECK_TRAC",ilUtil::formCheckbox(0,'item_ids[]',$obj_id));

			// Assigned
			$tpl->setVariable("ASSIGNED_IMG_OK",$lp_collections->isAssigned($obj_id)
							  ? ilUtil::getImagePath('icon_ok.gif') 
							  : ilUtil::getImagePath('icon_not_ok.gif'));
			$tpl->setVariable("ASSIGNED_STATUS",$lp_collections->isAssigned($obj_id)
							  ? $this->lng->txt('trac_assigned')
							  : $this->lng->txt('trac_not_assigned'));

			
			$tpl->parseCurrentBlock();
		}			
		$tpl->setVariable("SELECT_ROW",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
		$tpl->setVariable("SELECT_ALL",$this->lng->txt('select_all'));
		$this->tpl->setVariable("COLLECTION_TABLE",$tpl->get());
	}		


		

	function __showCollectionTable()
	{
		global $ilObjDataCache,$tree;

		include_once 'Services/Tracking/classes/class.ilLPCollections.php';
		include_once 'classes/class.ilLink.php';
		include_once 'classes/class.ilFrameTargetInfo.php';


		$lp_collections = new ilLPCollections($this->getObjId());

		$tpl =& new ilTemplate('tpl.trac_collections.html',true,true,'Services/Tracking');

		$tpl->setVariable("COLL_TITLE_IMG_ALT",$this->lng->txt('trac_lp_determination'));
		$tpl->setVariable("COLL_TITLE_IMG",ilUtil::getImagePath('icon_trac.gif'));
		//$tpl->setVariable("TABLE_TITLE",$this->lng->txt('trac_crs_assignments'));
		$tpl->setVariable("TABLE_TITLE",$this->lng->txt('trac_lp_determination'));
		$tpl->setVariable("TABLE_INFO",$this->lng->txt('trac_lp_determination_info_crs'));
		$tpl->setVariable("ITEM_DESC",$this->lng->txt('trac_crs_items'));
		$tpl->setVariable("ITEM_ASSIGNED",$this->lng->txt('trac_assigned'));

		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->setVariable("BTN_ASSIGN",$this->lng->txt('trac_collection_assign'));
		$tpl->setVariable("BTN_DEASSIGN",$this->lng->txt('trac_collection_deassign'));

		
		if(!ilLPCollections::_getCountPossibleItems($this->getRefId()) and !count($events))
		{
			$tpl->setCurrentBlock("no_items");
			$tpl->setVariable("NO_ITEM_MESSAGE",$this->lng->txt('trac_no_items'));
			$tpl->parseCurrentBlock();
		}

		// Show header
		$tpl->addBlockFile('MATERIALS','materials','tpl.trac_collections_row.html','Services/Tracking');
		$counter = 0;
		// Show materials
		foreach(ilLPCollections::_getPossibleItems($this->getRefId()) as $ref_id)
		{
			$obj_id = $ilObjDataCache->lookupObjId($ref_id);
			$type = $ilObjDataCache->lookupType($obj_id);

			$anonymized = $this->__checkItemAnonymized($obj_id,$type);

			$tpl->setCurrentBlock("materials");
			
			$tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_'.$type.'_s.gif'));
			$tpl->setVariable('ALT_IMG',$this->lng->txt('obj_'.$type));

			// Link to settings
			$tpl->setVariable("COLL_MODE",
							  $this->lng->txt('trac_mode').": ".
							  ilLPObjSettings::_mode2Text(ilLPObjSettings::_lookupMode($obj_id)));
			if($anonymized)
			{
				$tpl->setVariable("ANONYMIZED",$this->lng->txt('trac_anonymized_info_short'));
			}
			$tpl->setVariable("COLL_LINK",ilLink::_getLink($ref_id,$ilObjDataCache->lookupType($obj_id)));
			$tpl->setVariable("COLL_FRAME",ilFrameTargetInfo::_getFrame('MainContent',$ilObjDataCache->lookupType($obj_id)));
			$tpl->setVariable("COLL_DESC",$ilObjDataCache->lookupDescription($obj_id));
			$tpl->setVariable("COLL_TITLE",$ilObjDataCache->lookupTitle($obj_id));
			$tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));

			if(!$anonymized)
			{
				$tpl->setVariable("CHECK_TRAC",ilUtil::formCheckbox(0,'item_ids[]',$ref_id));
			}

			$path = $this->__formatPath($tree->getPathFull($ref_id),$ref_id);
			$tpl->setVariable("COLL_PATH",$this->lng->txt('path').": ".$path);

			// Assigned
			$tpl->setVariable("ASSIGNED_IMG_OK",$lp_collections->isAssigned($ref_id)
							  ? ilUtil::getImagePath('icon_ok.gif') 
							  : ilUtil::getImagePath('icon_not_ok.gif'));
			$tpl->setVariable("ASSIGNED_STATUS",$lp_collections->isAssigned($ref_id)
							  ? $this->lng->txt('trac_assigned')
							  : $this->lng->txt('trac_not_assigned'));

			
			$tpl->parseCurrentBlock();
		}
			
		$tpl->setVariable("SELECT_ROW",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
		$tpl->setVariable("SELECT_ALL",$this->lng->txt('select_all'));

		$this->tpl->setVariable("COLLECTION_TABLE",$tpl->get());
	}
	function __addInfo()
	{
		$message = $this->lng->txt('trac_settings_saved');

		if($this->obj_settings->getMode() == $_POST['modus'])
		{
			ilUtil::sendSuccess($message);
			return true;
		}

		ilUtil::sendSuccess($message);

		switch($_POST['modus'])
		{
			case LP_MODE_COLLECTION:
				$message = $this->lng->txt('trac_edit_collection');
				ilUtil::sendInfo($message);
				break;

			case LP_MODE_VISITS:
				$message = $this->lng->txt('trac_edit_visits');
				ilUtil::sendInfo($message);
				break;
				

			default:
				;
		}
		return true;
	}

	function __formatPath($a_path_arr,$a_ref_id)
	{
		global $tree;
		#$path = $this->__formatPath($tree->getPathFull($ref_id));
		#$tpl->setVariable("COLL_PATH",$this->lng->txt('path').": ".$path);
		$counter = 0;
		foreach($a_path_arr as $data)
		{
			if(!$tree->isGrandChild($this->getRefId(),$data['ref_id']))
			{
				continue;
			}
			if($a_ref_id == $data['ref_id'])
			{
				break;
			}
			if($counter++)
			{
				$path .= " -> ";
			}
			$path .= $data['title'];
		}

		return $path;
	}

	function __checkItemAnonymized($a_obj_id,$a_type)
	{
		switch($a_type)
		{
			case 'tst':
				include_once './Modules/Test/classes/class.ilObjTest.php';

				if(ilObjTest::_lookupAnonymity($a_obj_id))
				{
					return true;
				}
				return false;

			default:
				return false;
		}
	}
	
	/**
	 * Show mode selection
	 *
	 * @access private
	 * 
	 */
	private function showModeSelection()
	{
		$this->tpl->setVariable('TXT_MODE',$this->lng->txt('trac_mode'));
		
	 	foreach($this->obj_settings->getValidModes() as $mode_key => $mode_name)
	 	{
	 		$this->tpl->setCurrentBlock('mode_check');
	 		$this->tpl->setVariable('RADIO_ID',$mode_key);
	 		$this->tpl->setVariable('RADIO_CHECKED',$mode_key == $this->obj_settings->getMode() ? ' checked="checked"' : '');
			$this->tpl->setVariable('RADIO_VALUE',$mode_key);
			$this->tpl->setVariable('MODE_NAME',$mode_name);
			$this->tpl->setVariable('MODE_INFO',ilLPObjSettings::_mode2InfoText($mode_key));
			$this->tpl->parseCurrentBlock();
	 	}
	}
}
?>