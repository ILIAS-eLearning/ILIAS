<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';

/**
* Class ilObjUserTrackingGUI
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ilCtrl_Calls ilLearningProgressGUI: ilLPListOfObjectsGUI, ilLPListOfSettingsGUI, ilLPListOfProgressGUI,  ilLMStatisticsGUI
* @ilCtrl_Calls ilLearningProgressGUI: ilLPObjectStatisticsGUI
*
*/
class ilLearningProgressGUI extends ilLearningProgressBaseGUI
{
	function ilLearningProgressGUI($a_mode,$a_ref_id = 0,$a_user_id = 0)
	{
		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id,$a_user_id);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilBench, $ilHelp;
		
		$ilBench->start('LearningProgress','0000_Start');


		$this->ctrl->setReturn($this, "");

		// E.g personal desktop mode needs locator header icon ...
		$this->__buildHeader();
		switch($this->__getNextClass())
		{
			case 'illplistofprogressgui':
				include_once 'Services/Tracking/classes/class.ilLPListOfProgressGUI.php';
				
				$ilHelp->setScreenIdComponent("lp");

				$this->__setSubTabs(LP_ACTIVE_PROGRESS);
				$this->__setCmdClass('illplistofprogressgui');
				$lop_gui = new ilLPListOfProgressGUI($this->getMode(),$this->getRefId(),$this->getUserId());
				$this->ctrl->forwardCommand($lop_gui);
				break;

			case 'illplistofobjectsgui':
				include_once 'Services/Tracking/classes/class.ilLPListOfObjectsGUI.php';
				if(stristr($this->ctrl->getCmd(), "matrix"))
				{
					$this->__setSubTabs(LP_ACTIVE_MATRIX);
				}
				else if(stristr($this->ctrl->getCmd(), "summary"))
				{
					$this->__setSubTabs(LP_ACTIVE_SUMMARY);
				}
				else
				{
					$this->__setSubTabs(LP_ACTIVE_OBJECTS);
				}
				$loo_gui = new ilLPListOfObjectsGUI($this->getMode(),$this->getRefId());
				$this->__setCmdClass('illplistofobjectsgui');
				$this->ctrl->forwardCommand($loo_gui);
				break;

			case 'illplistofsettingsgui':
				include_once 'Services/Tracking/classes/class.ilLPListOfSettingsGUI.php';

				$this->__setSubTabs(LP_ACTIVE_SETTINGS);
				$los_gui = new ilLPListOfSettingsGUI($this->getMode(),$this->getRefId());
				$this->__setCmdClass('illplistofsettingsgui');
				$this->ctrl->forwardCommand($los_gui);
				break;
				
			case 'illmstatisticsgui':
				include_once 'Services/Tracking/classes/class.ilLMStatisticsGUI.php';

				$this->__setSubTabs(LP_ACTIVE_LM_STATISTICS);
				$los_gui = new ilLMStatisticsGUI($this->getMode(),$this->getRefId());
				$this->__setCmdClass('illmstatisticsgui');
				$this->ctrl->forwardCommand($los_gui);
				break;

			case 'illpobjectstatisticsgui':
				include_once 'Services/Tracking/classes/class.ilLPObjectStatisticsGUI.php';
				if(stristr($this->ctrl->getCmd(), "access"))
				{
					$this->__setSubTabs(LP_ACTIVE_OBJSTATACCESS);
				}
				else if(stristr($this->ctrl->getCmd(), "types"))
				{
					$this->__setSubTabs(LP_ACTIVE_OBJSTATTYPES);
				}
				else if(stristr($this->ctrl->getCmd(), "daily"))
				{
					$this->__setSubTabs(LP_ACTIVE_OBJSTATDAILY);
				}
				else
				{
					$this->__setSubTabs(LP_ACTIVE_OBJSTATADMIN);
				}
				$this->__setCmdClass('illpobjectstatisticsgui');
				$ost_gui = new ilLPObjectStatisticsGUI($this->getMode(),$this->getRefId());
				$this->ctrl->forwardCommand($ost_gui);
				break;
			
			default:
				$cmd = $this->ctrl->getCmd();
				$this->$cmd();
				$this->tpl->show(true);
				break;
		}

		// E.G personal desktop mode needs $tpl->show();
		$this->__buildFooter();


		$ilBench->stop('LearningProgress','0000_Start');

		return true;
	}

	function __setCmdClass($a_class)
	{
		// If cmd class == 'illearningprogressgui' the cmd class is set to the the new forwarded class
		// otherwise e.g illplistofprogressgui tries to forward (back) to illearningprogressgui.

		if($this->ctrl->getCmdClass() == strtolower(get_class($this)))
		{
			$this->ctrl->setCmdClass(strtolower($a_class));
		}
		return true;
	}

	function __getNextClass()
	{
		global $ilAccess, $ilUser;
		
		// #9857
		if(!ilObjUserTracking::_enabledLearningProgress())
		{
			return;
		}

		if(strlen($next_class = $this->ctrl->getNextClass()))
		{
			if($this->getMode() == LP_MODE_PERSONAL_DESKTOP)
			{
				$_SESSION['il_lp_history'] = $next_class;
			}
			return $next_class;
		}
		switch($this->getMode())
		{
			case LP_MODE_ADMINISTRATION:
				return 'illplistofobjectsgui';

			case LP_MODE_REPOSITORY:
				$cmd = $this->ctrl->getCmd();
				if(in_array($cmd, array("editmanual", "updatemanual", "showtlt")))
				{
					return "";
				}
				
				if(!$this->anonymized and 
				   !$this->isObjectAnonymized() and 
				   $ilAccess->checkAccess('edit_learning_progress','',$this->getRefId()))
				{
					return 'illplistofobjectsgui';
				}
				return 'illplistofprogressgui';

			case LP_MODE_PERSONAL_DESKTOP:
								
				include_once("Services/Tracking/classes/class.ilObjUserTracking.php");			
				$has_edit = ilObjUserTracking::_hasLearningProgressOtherUsers();		
				$has_personal = ilObjUserTracking::_hasLearningProgressLearner();
				
				if($has_edit || $has_personal)
				{														
					// default
					$tgt = null;
					if($has_edit)
					{
						$tgt = 'illplistofobjectsgui';
					}
					else if($has_personal)
					{
						$tgt = 'illplistofprogressgui';
					}

					// validate session
					switch($_SESSION['il_lp_history'])
					{
						case 'illplistofobjectsgui':
							if(!$has_edit)
							{
								$_SESSION['il_lp_history'] = null;
							}
							break;

						case 'illplistofprogressgui':
							if(!$has_personal)
							{
								$_SESSION['il_lp_history'] = null;
							}
							break;					
					}

					if($_SESSION['il_lp_history'])
					{
						return $_SESSION['il_lp_history'];
					}
					else if($tgt)
					{
						return $tgt;
					}
				}
				
				// should not happen
				ilUtil::redirect("ilias.php?baseClass=ilPersonalDesktopGUI");
				
			case LP_MODE_USER_FOLDER:
				if(ilObjUserTracking::_enabledUserRelatedData())
				{
					return 'illplistofprogressgui';
				}
		}
	}
	
	protected function editManual()
	{
		global $tpl;
		
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->getRefId()))
		{			
			include_once './Services/Tracking/classes/class.ilLPObjSettings.php';
			$lp = new ilLPObjSettings(ilObject::_lookupObjId($this->getRefId()));			
			if($lp->getMode() == LP_MODE_COLLECTION_MANUAL)
			{
				$form = $this->initCollectionManualForm();				
				$tpl->setContent($form->getHTML());							
			}
		}		
	}
	
	protected function initCollectionManualForm()
	{
		global $lng, $ilCtrl;
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "updatemanual"));
		$form->setTitle($lng->txt("learning_progress"));
		$form->setDescription($lng->txt("trac_collection_manual_learner_info"));
		
		include_once './Services/Tracking/classes/class.ilLPCollectionCache.php';
		$coll_items = ilLPCollectionCache::_getItems($this->getObjId());
		
		switch(ilObject::_lookupType($this->getObjId()))
		{
			case "lm":
				$possible_items = ilLPCollections::_getPossibleLMItems($this->getObjId());		
				$coll_items = array_intersect(array_keys($possible_items), $coll_items);
				$subitem_title = $lng->txt("objs_st");
				$subitem_info = $lng->txt("trac_collection_manual_learner_lm_info");
				break;
		}
		
		include_once "Services/Tracking/classes/class.ilLPStatusCollectionManual.php";
		$lp_data = ilLPStatusCollectionManual::_getObjectStatus($this->getObjId(), $this->usr_id);
				
		$grp = new ilCheckboxGroupInputGUI($subitem_title, "sids");
		$grp->setInfo($subitem_info);
		$form->addItem($grp);
		
		$completed = array();
		foreach($coll_items as $item_id)
		{			
			$info = null;
			$status = LP_STATUS_NOT_ATTEMPTED_NUM;
			
			if(isset($lp_data[$item_id]))
			{
				$changed = new ilDateTime($lp_data[$item_id][1], IL_CAL_UNIX);
				$info = $lng->txt("trac_collection_manual_learner_changed_ts").": ".
					ilDatePresentation::formatDate($changed);
				
				if($lp_data[$item_id][0])
				{
					$status = LP_STATUS_COMPLETED_NUM;					
					$completed[] = $item_id;					
				}
			}
			
			$path = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
			$text = ilLearningProgressBaseGUI::_getStatusText($status);
			$icon = ilUtil::img($path, $text);
			
			$opt = new ilCheckboxOption($icon." ".$possible_items[$item_id]["title"], $item_id);
			if($info)
			{
				$opt->setInfo($info);
			}
			$grp->addOption($opt);			
		}
		
		if($completed)
		{
			$grp->setValue($completed);
		}
		
		$form->addCommandButton("updatemanual", $lng->txt("save"));
		
		return $form;
	}
	
	protected function updateManual()
	{
		global $ilCtrl, $lng;
		
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->getRefId()))
		{			
			include_once './Services/Tracking/classes/class.ilLPObjSettings.php';
			$lp = new ilLPObjSettings(ilObject::_lookupObjId($this->getRefId()));			
			if($lp->getMode() == LP_MODE_COLLECTION_MANUAL)
			{
				$form = $this->initCollectionManualForm();			
				if($form->checkInput())
				{
					include_once "Services/Tracking/classes/class.ilLPStatusCollectionManual.php";
					ilLPStatusCollectionManual::_setObjectStatus($this->getObjId(), $this->usr_id, $form->getInput("sids"));
					
					ilUtil::sendSuccess($lng->txt("settings_saved"), true);
				}							
				
				$ilCtrl->redirect($this, "editmanual");
			}
		}		
	}
	
	protected function showtlt()
	{
		global $lng, $ilCtrl, $tpl, $ilUser;
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "showtlt"));
		$form->setTitle($lng->txt("learning_progress"));
		$form->setDescription($lng->txt("trac_collection_tlt_learner_info"));
		
		include_once './Services/Tracking/classes/class.ilLPCollectionCache.php';
		$coll_items = ilLPCollectionCache::_getItems($this->getObjId());
		
		switch(ilObject::_lookupType($this->getObjId()))
		{
			case "lm":
				$possible_items = ilLPCollections::_getPossibleLMItems($this->getObjId());		
				$coll_items = array_intersect(array_keys($possible_items), $coll_items);
				break;
		}
		
		include_once "Services/Tracking/classes/class.ilLPStatus.php";
		include_once "Services/Tracking/classes/class.ilLPStatusCollectionTLT.php";
		$info = ilLPStatusCollectionTLT::_getStatusInfo($this->getObjId(), true);
		
		foreach($coll_items as $item_id)
		{
			$field = new ilCustomInputGUI($possible_items[$item_id]["title"]);
			
			// lp status
			$status = LP_STATUS_NOT_ATTEMPTED_NUM;
			if(isset($info["completed"][$item_id]) && 
				in_array($ilUser->getId(), $info["completed"][$item_id]))
			{
				$status = LP_STATUS_COMPLETED_NUM;
			}
			else if(isset($info["in_progress"][$item_id]) && 
				in_array($ilUser->getId(), $info["in_progress"][$item_id]))
			{
				$status = LP_STATUS_IN_PROGRESS_NUM;
			}
			$path = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
			$text = ilLearningProgressBaseGUI::_getStatusText($status);
			$field->setHtml(ilUtil::img($path, $text));
			
			// stats
			$spent = 0;
			if(isset($info["tlt_users"][$item_id][$ilUser->getId()]))
			{
				$spent = $info["tlt_users"][$item_id][$ilUser->getId()];			
			}
			$needed = $info["tlt"][$item_id];
			if($needed)
			{								
				$field->setInfo(sprintf($lng->txt("trac_collection_tlt_learner_subitem"), 
					ilFormat::_secondsToString($spent),
					ilFormat::_secondsToString($needed), 
					min(100, round($spent/$needed*100))));
			}
			
			$form->addItem($field);
		}
		
		$tpl->setContent($form->getHTML());		
	}
}
?>