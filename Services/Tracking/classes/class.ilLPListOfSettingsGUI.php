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
		$this->tpl->setVariable("ACTIVATED_IMG_OK",$activated = ilObjUserTracking::_enabledTracking()
								? ilUtil::getImagePath('icon_ok.gif') 
								: ilUtil::getImagePath('icon_not_ok.gif'));
		$this->tpl->setVariable("ACTIVATED_STATUS",$activated ? $this->lng->txt('yes') : $this->lng->txt('no'));

		$this->tpl->setVariable("TXT_USER_RELATED_DATA", $this->lng->txt("trac_anonymized"));
		$this->tpl->setVariable("ANONYMIZED_IMG_OK",$anonymized = ilObjUserTracking::_enabledUserRelatedData()
								? ilUtil::getImagePath('icon_ok.gif') 
								: ilUtil::getImagePath('icon_not_ok.gif'));
		$this->tpl->setVariable("ANONYMIZED_STATUS",$anonymized ? $this->lng->txt('yes') : $this->lng->txt('no'));

		$this->tpl->setVariable("TXT_VALID_REQUEST",$this->lng->txt('trac_valid_request'));
		$this->tpl->setVariable("INFO_VALID_REQUEST",$this->lng->txt('info_valid_request'));
		$this->tpl->setVariable("SECONDS",$this->lng->txt('seconds'));
		$this->tpl->setVariable("VAL_SECONDS",ilObjUserTracking::_getValidTimeSpan());

		// Mode selector
		$this->tpl->setVariable("TXT_MODE",$this->lng->txt('trac_modus'));

		$this->tpl->setVariable("MODE",ilUtil::formSelect($this->obj_settings->getMode(),
														  'modus',
														  $this->obj_settings->getValidModes(),
														  false,true));

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
		if(!$_POST['item_ids'])
		{
			sendInfo($this->lng->txt('select_one'));
			$this->show();
			return false;
		}
		include_once 'Services/Tracking/classes/class.ilLPCollections.php';

		$lp_collections = new ilLPCollections($this->getObjId());

		foreach($_POST['item_ids'] as $obj_id)
		{
			$lp_collections->add($obj_id);
		}
		sendInfo($this->lng->txt('trac_settings_saved'));
		$this->show();
	}

	function deassign()
	{
		if(!$_POST['item_ids'])
		{
			sendInfo($this->lng->txt('select_one'));
			$this->show();
			return false;
		}
		include_once 'Services/Tracking/classes/class.ilLPCollections.php';

		$lp_collections = new ilLPCollections($this->getObjId());

		foreach($_POST['item_ids'] as $obj_id)
		{
			$lp_collections->delete($obj_id);
		}
		sendInfo($this->lng->txt('trac_settings_saved'));
		$this->show();
	}
	function __showTablesByMode()
	{
		switch($this->obj_settings->getMode())
		{
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
		include_once 'content/classes/SCORM/class.ilSCORMItem.php';


		if(!$items = ilLPCollections::_getPossibleSAHSItems($this->getObjId()))
		{
			sendInfo($this->lng->txt('trac_no_sahs_items_found'));
			return false;
		}


		$lp_collections = new ilLPCollections($this->getObjId());

		$tpl =& new ilTemplate('tpl.trac_collections.html',true,true,'Services/Tracking');

		$tpl->setVariable("COLL_TITLE_IMG_ALT",$this->lng->txt('trac_assignments'));
		$tpl->setVariable("COLL_TITLE_IMG",ilUtil::getImagePath('icon_trac.gif'));
		$tpl->setVariable("TABLE_TITLE",$this->lng->txt('trac_assignments'));
		$tpl->setVariable("ITEM_DESC",$this->lng->txt('description'));
		$tpl->setVariable("ITEM_ASSIGNED",$this->lng->txt('trac_assigned'));

		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->setVariable("BTN_ASSIGN",$this->lng->txt('trac_collection_assign'));
		$tpl->setVariable("BTN_DEASSIGN",$this->lng->txt('trac_collection_deassign'));

		
		$counter = 0;

		foreach($items as $obj_id => $data)
		{
			$tpl->setCurrentBlock("trac_row");
			#$tpl->setVariable("COLL_DESC",$ilObjDataCache->lookupDescription($obj_id));
			#$tpl->setVariable("COLL_TITLE",ilSCORMItem::_lookupTitle($obj_id));
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
			
		$this->tpl->setVariable("COLLECTION_TABLE",$tpl->get());
	}		


		

	function __showCollectionTable()
	{
		global $ilObjDataCache,$tree;

		include_once 'Services/Tracking/classes/class.ilLPCollections.php';

		$lp_collections = new ilLPCollections($this->getObjId());

		$tpl =& new ilTemplate('tpl.trac_collections.html',true,true,'Services/Tracking');

		$tpl->setVariable("COLL_TITLE_IMG_ALT",$this->lng->txt('trac_assignments'));
		$tpl->setVariable("COLL_TITLE_IMG",ilUtil::getImagePath('icon_trac.gif'));
		$tpl->setVariable("TABLE_TITLE",$this->lng->txt('trac_assignments'));
		$tpl->setVariable("ITEM_DESC",$this->lng->txt('description'));
		$tpl->setVariable("ITEM_ASSIGNED",$this->lng->txt('trac_assigned'));

		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->setVariable("BTN_ASSIGN",$this->lng->txt('trac_collection_assign'));
		$tpl->setVariable("BTN_DEASSIGN",$this->lng->txt('trac_collection_deassign'));

		
		if(!ilLPCollections::_getCountPossibleItems($this->getRefId()))
		{
			$tpl->setCurrentBlock("no_items");
			$tpl->setVariable("NO_ITEM_MESSAGE",$this->lng->txt('trac_no_items'));
			$tpl->parseCurrentBlock();
		}
		$counter = 0;
		foreach(ilLPCollections::_getPossibleItems($this->getRefId()) as $ref_id => $obj_id)
		{
			$tpl->setCurrentBlock("trac_row");
			$tpl->setVariable("COLL_DESC",$ilObjDataCache->lookupDescription($obj_id));
			$tpl->setVariable("COLL_TITLE",$ilObjDataCache->lookupTitle($obj_id));
			$tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$tpl->setVariable("CHECK_TRAC",ilUtil::formCheckbox(0,'item_ids[]',$obj_id));

			$path = $this->__formatPath($tree->getPathFull($ref_id));
			$tpl->setVariable("COLL_PATH",$this->lng->txt('path').": ".$path);

			// Assigned
			$tpl->setVariable("ASSIGNED_IMG_OK",$lp_collections->isAssigned($obj_id)
							  ? ilUtil::getImagePath('icon_ok.gif') 
							  : ilUtil::getImagePath('icon_not_ok.gif'));
			$tpl->setVariable("ASSIGNED_STATUS",$lp_collections->isAssigned($obj_id)
							  ? $this->lng->txt('trac_assigned')
							  : $this->lng->txt('trac_not_assigned'));

			
			$tpl->parseCurrentBlock();
		}			
			
		$this->tpl->setVariable("COLLECTION_TABLE",$tpl->get());
	}
	function __addInfo()
	{
		$message = $this->lng->txt('trac_settings_saved');

		if($this->obj_settings->getMode() == $_POST['modus'])
		{
			sendInfo($message);
			return true;
		}

		switch($_POST['modus'])
		{
			case LP_MODE_COLLECTION:
				$message .= '<br />';
				$message .= $this->lng->txt('trac_edit_collection');
				break;

			case LP_MODE_VISITS:
				$message .= '<br />';
				$message .= $this->lng->txt('trac_edit_visits');
				break;
				

			default:
				;
		}
		sendInfo($message);

		return true;
	}

	function __formatPath($a_path_arr)
	{
		$counter = 0;
		foreach($a_path_arr as $data)
		{
			if($counter++)
			{
				$path .= " -> ";
			}
			$path .= $data['title'];
		}

		return $path;
	}

}
?>