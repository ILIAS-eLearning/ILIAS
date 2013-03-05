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

	/**
	 * Show settings tables
	 */
	protected function show()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_obj_settings.html','Services/Tracking');

		$form = $this->initFormSettings();
		$this->tpl->setVariable('PROP_FORM',$form->getHTML());
		$this->tpl->setVariable('COLLECTION_TABLE',$this->getTableByMode());
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
		$mod->setValue($this->obj_settings->getMode());
		$form->addItem($mod);

		foreach($this->obj_settings->getValidModes() as $mode_key => $mode_name)
		{			
			$opt = new ilRadioOption(
				$mode_name,
				$mode_key,
				ilLPObjSettings::_mode2InfoText($mode_key)
			);
			$opt->setValue($mode_key);
			$mod->addOption($opt);

			// Subitem for vistits
			if($mode_key == LP_MODE_VISITS)
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
				
		/*
		// Info Active
		$act = new ilCustomInputGUI($this->lng->txt('trac_activated'), '');
		$img = new ilTemplate('tpl.obj_settings_img_row.html',true,true,'Services/Tracking');
		$img->setVariable("IMG_SRC",
			$activated = ilObjUserTracking::_enabledLearningProgress()
				? ilUtil::getImagePath('icon_ok.png')
				: ilUtil::getImagePath('icon_not_ok.png')
		);
		$act->setHTML($img->get());
		$form->addItem($act);

 		// Info Anonymized
 		$ano = new ilCustomInputGUI($this->lng->txt('trac_anonymized'), '');
		$img = new ilTemplate('tpl.obj_settings_img_row.html',true,true,'Services/Tracking');
		$img->setVariable("IMG_SRC",
			$anonymized = !ilObjUserTracking::_enabledUserRelatedData()
				? ilUtil::getImagePath('icon_ok.png')
				: ilUtil::getImagePath('icon_not_ok.png')
		);
		$ano->setHTML($img->get());
		$form->addItem($ano);
		*/				

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
			// do not confuse collections
			$new_mode = (int) $form->getInput('modus');
			$old_mode = $this->obj_settings->getMode();
			if($old_mode != $new_mode &&
				in_array($old_mode, array(LP_MODE_COLLECTION, LP_MODE_COLLECTION_MANUAL, LP_MODE_COLLECTION_TLT)))
			{
				include_once "Services/Tracking/classes/class.ilLPCollections.php";
				ilLPCollections::_deleteAll($this->getObjId());				
			}
			
			$this->obj_settings->setMode($new_mode);
			$this->obj_settings->setVisits($form->getInput('visits'));
			$this->obj_settings->update();

			if(in_array($new_mode, array(LP_MODE_COLLECTION, LP_MODE_COLLECTION_MANUAL, LP_MODE_COLLECTION_TLT)))
			{
				ilUtil::sendInfo($this->lng->txt('trac_edit_collection'),true);
			}
			ilUtil::sendSuccess($this->lng->txt('trac_settings_saved'),true);
			$this->ctrl->redirect($this,'show');
		}

		$form->setValuesByPost();
		ilUtil::sendFailure($this->lng->txt('err_check_input'));

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_obj_settings.html','Services/Tracking');
		$this->tpl->setVariable('PROP_FORM',$form->getHTML());
		$this->tpl->setVariable('COLLECTION_TABLE',$this->getTableByMode());

		return;
	}

	/**
	 * Get tables by mode
	 */
	protected function getTableByMode()
	{
		include_once './Services/Tracking/classes/class.ilLPCollectionSettingsTableGUI.php';
		switch($this->obj_settings->getMode())
		{
			case LP_MODE_COLLECTION:
			case LP_MODE_MANUAL_BY_TUTOR:
			case LP_MODE_SCORM:
			case LP_MODE_COLLECTION_MANUAL:
			case LP_MODE_COLLECTION_TLT:
				$table = new ilLPCollectionSettingsTableGUI($this->getRefId(),$this,'show');
				$table->setMode($this->obj_settings->getMode());
				$table->parse();
				return $table->getHTML();


			default:
				return '';
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
			include_once 'Services/Tracking/classes/class.ilLPCollections.php';
			ilLPCollections::activate($this->getObjId(), $_POST['item_ids']);

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
			include_once 'Services/Tracking/classes/class.ilLPCollections.php';
			ilLPCollections::deactivate($this->getObjId(),$_POST['item_ids']);

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

		// Assign new grouping id
		include_once './Services/Tracking/classes/class.ilLPCollections.php';
		ilLPCollections::createNewGrouping($this->getObjId(),(array) $_POST['item_ids']);

		// refresh learning progress
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		ilLPStatusWrapper::_refreshStatus($this->getObjId());

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

		include_once './Services/Tracking/classes/class.ilLPCollections.php';
		ilLPCollections::releaseGrouping($this->getObjId(), (array) $_POST['item_ids']);

		// refresh learning progress
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		ilLPStatusWrapper::_refreshStatus($this->getObjId());

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
			include_once './Services/Tracking/classes/class.ilLPCollections.php';
			ilLPCollections::saveObligatoryMaterials($this->getObjId(), (array) $_POST['grp']);

			// refresh learning progress
			include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
			ilLPStatusWrapper::_refreshStatus($this->getObjId());

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