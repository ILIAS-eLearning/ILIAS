<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPObjSettings.php';

/**
 * Class ilLPListOfSettingsGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @version $Id$
 *
 * @ilCtrl_Calls ilLPListOfSettingsGUI:
 *
 * @ingroup ServicesTracking
 *
 */
class ilLPListOfSettingsGUI extends ilLearningProgressBaseGUI
{
	protected $obj_settings;
	protected $obj_lp;
	
	function __construct($a_mode,$a_ref_id)
	{
		parent::__construct($a_mode,$a_ref_id);
		
		$this->obj_settings = new ilLPObjSettings($this->getObjId());
		
		include_once './Services/Object/classes/class.ilObjectLP.php';
		$this->obj_lp = ilObjectLP::getInstance($this->getObjId());
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

	/**
	 * Show settings tables
	 */
	protected function show()
	{
		global $ilHelp;

		$ilHelp->setSubScreenId("trac_settings");

		$form = $this->initFormSettings();
		$this->tpl->setContent($form->getHTML().$this->getTableByMode());
	}


	/**
	 * Init property form
	 *
	 * @return ilPropertyFormGUI $form
	 */
	protected function initFormSettings()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('tracking_settings'));
		$form->setFormAction($this->ctrl->getFormAction($this));

		// Mode
		$mod = new ilRadioGroupInputGUI($this->lng->txt('trac_mode'), 'modus');
		$mod->setRequired(true);
		$mod->setValue($this->obj_lp->getCurrentMode());
		$form->addItem($mod);

		foreach($this->obj_lp->getValidModes() as $mode_key)
		{			
			$opt = new ilRadioOption(
				$this->obj_lp->getModeText($mode_key),
				$mode_key,
				$this->obj_lp->getModeInfoText($mode_key)
			);
			$opt->setValue($mode_key);
			$mod->addOption($opt);

			// :TODO: Subitem for visits ?!
			if($mode_key == ilLPObjSettings::LP_MODE_VISITS)
			{
				$vis = new ilNumberInputGUI($this->lng->txt('trac_visits'), 'visits');
				$vis->setSize(3);
				$vis->setMaxLength(4);
				$vis->setInfo(sprintf($this->lng->txt('trac_visits_info'),
					ilObjUserTracking::_getValidTimeSpan()));
				$vis->setRequired(true);
				$vis->setValue($this->obj_settings->getVisits());
				$opt->addSubItem($vis);
			}
		}
		
		$form->addCommandButton('saveSettings', $this->lng->txt('save'));

		return $form;
	}

	/**
	 * Save learning progress settings
	 * @return void
	 */
	protected function saveSettings()
	{
		$form = $this->initFormSettings();
		if($form->checkInput())
		{
			// anything changed?
			
			// mode
			$new_mode = (int)$form->getInput('modus');
			$old_mode = $this->obj_lp->getCurrentMode();	
			$mode_changed = ($old_mode != $new_mode);
			
			// visits
			$new_visits = null;
			$visits_changed = null;
			if($new_mode == ilLPObjSettings::LP_MODE_VISITS)
			{				
				$new_visits = (int)$form->getInput('visits');	
				$old_visits = $this->obj_settings->getVisits();
				$visits_changed = ($old_visits != $new_visits);
			}
			
			if($mode_changed)
			{
				// delete existing collection 
				$collection = $this->obj_lp->getCollectionInstance();
				if($collection)
				{
					$collection->delete();	
				}
			}			
			
			$refresh_lp = ($mode_changed || $visits_changed);
			
			// has to be done before LP refresh!
			$this->obj_lp->resetCaches();
			
			$this->obj_settings->setMode($new_mode);									
			$this->obj_settings->setVisits($new_visits);			
			$this->obj_settings->update($refresh_lp);
			
			if($mode_changed && 
				$this->obj_lp->getCollectionInstance() &&
				$new_mode != ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR) // #14819
			{
				ilUtil::sendInfo($this->lng->txt('trac_edit_collection'), true);
			}
			ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'), true);
			$this->ctrl->redirect($this, 'show');
		}

		$form->setValuesByPost();
		ilUtil::sendFailure($this->lng->txt('err_check_input'));

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_obj_settings.html','Services/Tracking');
		$this->tpl->setVariable('PROP_FORM',$form->getHTML());
		$this->tpl->setVariable('COLLECTION_TABLE',$this->getTableByMode());
	}

	/**
	 * Get tables by mode
	 */
	protected function getTableByMode()
	{
		$collection = $this->obj_lp->getCollectionInstance();
		if($collection && $collection->hasSelectableItems())
		{			
			include_once "Services/Tracking/classes/repository_statistics/class.ilLPCollectionSettingsTableGUI.php";
			$table = new ilLPCollectionSettingsTableGUI($this, 'show', $this->getRefId(), $this->obj_lp->getCurrentMode());
			$table->parse($collection);
			return $table->getHTML();
		}		
	}

	/**
	 * Save material assignment
	 * @return void
	 */
	protected function assign()
	{
		if(!$_POST['item_ids'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$this->ctrl->redirect($this,'show');
		}
		if(count($_POST['item_ids']))
		{
			$collection = $this->obj_lp->getCollectionInstance();
			if($collection && $collection->hasSelectableItems())
			{			
				$collection->activateEntries($_POST['item_ids']);
			}
			
			// #15045 - has to be done before LP refresh!
			$this->obj_lp->resetCaches();
			
			// refresh learning progress
			include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
			ilLPStatusWrapper::_refreshStatus($this->getObjId());
		}
		ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'),true);
		$this->ctrl->redirect($this,'show');
	}

	/**
	 * save mterial assignment
	 * @return void
	 */
	protected function deassign()
	{
		if(!$_POST['item_ids'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$this->ctrl->redirect($this,'show');
			return false;
		}
		if(count($_POST['item_ids']))
		{
			$collection = $this->obj_lp->getCollectionInstance();
			if($collection && $collection->hasSelectableItems())
			{			
				$collection->deactivateEntries($_POST['item_ids']);
			}
			
			// #15045 - has to be done before LP refresh!
			$this->obj_lp->resetCaches();
			
			// refresh learning progress
			include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
			ilLPStatusWrapper::_refreshStatus($this->getObjId());			
		}
		ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'),true);
		$this->ctrl->redirect($this,'show');
	}

	/**
	 * Group materials
	 */
	protected function groupMaterials()
	{
		if(!count((array) $_POST['item_ids']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$this->ctrl->redirect($this,'show');
		}
		
		$collection = $this->obj_lp->getCollectionInstance();
		if($collection && $collection->hasSelectableItems())
		{
			// Assign new grouping id
			$collection->createNewGrouping((array)$_POST['item_ids']);

			// refresh learning progress
			include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
			ilLPStatusWrapper::_refreshStatus($this->getObjId());
		}

		ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'),true);
		$this->ctrl->redirect($this,'show');
	}

	/**
	 *
	 */
	protected function releaseMaterials()
	{
		if(!count((array) $_POST['item_ids']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$this->ctrl->redirect($this,'show');
		}

		$collection = $this->obj_lp->getCollectionInstance();
		if($collection && $collection->hasSelectableItems())
		{		
			$collection->releaseGrouping((array)$_POST['item_ids']);
			
			// refresh learning progress
			include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
			ilLPStatusWrapper::_refreshStatus($this->getObjId());
		}
		
		ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'),true);
		$this->ctrl->redirect($this,'show');
	}

	/**
	 * Save obligatory state per grouped materials
	 */
	protected function saveObligatoryMaterials()
	{
		if(!is_array((array) $_POST['grp']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$this->ctrl->redirect($this,'show');
		}

		try {
			
			$collection = $this->obj_lp->getCollectionInstance();
			if($collection && $collection->hasSelectableItems())
			{	
				$collection->saveObligatoryMaterials((array)$_POST['grp']);

				// refresh learning progress
				include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
				ilLPStatusWrapper::_refreshStatus($this->getObjId());
			}

			ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
			$this->ctrl->redirect($this,'show');
		}
		catch(UnexpectedValueException $e) {
			ilUtil::sendFailure($this->lng->txt('trac_grouped_material_obligatory_err'), true);
			ilUtil::sendInfo($this->lng->txt('err_check_input'),true);
			$this->ctrl->redirect($this,'show');
		}
	}
	
	protected function updateTLT()
	{
		include_once "Services/MetaData/classes/class.ilMD.php";
		foreach($_POST['tlt'] as $item_id => $item)
		{			
			$md_obj = new ilMD($this->getObjId(),$item_id,'st');
			if(!is_object($md_section = $md_obj->getEducational()))
			{
				$md_section = $md_obj->addEducational();
				$md_section->save();
			}			
			$md_section->setPhysicalTypicalLearningTime((int)$item['mo'],
				(int)$item['d'],(int)$item['h'],(int)$item['m'],0);
			$md_section->update();
		}		
		
		// refresh learning progress
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		ilLPStatusWrapper::_refreshStatus($this->getObjId());		
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
		$this->ctrl->redirect($this,'show');
	}
}
?>