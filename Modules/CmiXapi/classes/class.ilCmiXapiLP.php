<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiLP
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiLP extends ilObjectLP
{
	public function initModeOptions(ilRadioGroupInputGUI $modeRadio)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$modeCompleted = new ilRadioOption('Deactivated', ilLPObjSettings::LP_MODE_DEACTIVATED);
		$modeRadio->addOption($modeCompleted);
		
		$modeCompleted = new ilRadioOption('Completed when Completed', ilLPObjSettings::LP_MODE_CMIX_COMPLETED);
		$modeRadio->addOption($modeCompleted);
		$modeCompletedFailed = new ilCheckboxInputGUI('Also Consider Failed',
			'modus_'.ilLPObjSettings::LP_MODE_CMIX_COMPLETED.'_failed'
		);
		$modeCompleted->addSubItem($modeCompletedFailed);
		
		$modePassed = new ilRadioOption('Completed when Passed', ilLPObjSettings::LP_MODE_CMIX_PASSED);
		$modeRadio->addOption($modePassed);
		$modePassedFailed = new ilCheckboxInputGUI('Also Consider Failed',
			'modus_'.ilLPObjSettings::LP_MODE_CMIX_PASSED.'_failed'
		);
		$modePassed->addSubItem($modePassedFailed);
		
		$modePassedOrCompleted = new ilRadioOption('Completed when Passed or Completed', ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED);
		$modeRadio->addOption($modePassedOrCompleted);
		$modePassedOrCompletedFailed = new ilCheckboxInputGUI('Also Consider Failed',
			'modus_'.ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED.'_failed'
		);
		$modePassedOrCompleted->addSubItem($modePassedOrCompletedFailed);
		
		switch($this->getCurrentMode())
		{
			case ilLPObjSettings::LP_MODE_CMIX_COMPLETED:
				$modeRadio->setValue(ilLPObjSettings::LP_MODE_CMIX_COMPLETED);
				break;
			case ilLPObjSettings::LP_MODE_CMIX_COMPL_WITH_FAILED:
				$modeRadio->setValue(ilLPObjSettings::LP_MODE_CMIX_COMPLETED);
				$modeCompletedFailed->setChecked(true);
				break;
			case ilLPObjSettings::LP_MODE_CMIX_PASSED:
				$modeRadio->setValue(ilLPObjSettings::LP_MODE_CMIX_PASSED);
				break;
			case ilLPObjSettings::LP_MODE_CMIX_PASSED_WITH_FAILED:
				$modeRadio->setValue(ilLPObjSettings::LP_MODE_CMIX_PASSED);
				$modePassedFailed->setChecked(true);
				break;
			case ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED:
				$modeRadio->setValue(ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED);
				break;
			case ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED:
				$modeRadio->setValue(ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED);
				$modePassedOrCompletedFailed->setChecked(true);
				break;
		}
	}
	
	public function fetchModeOption(ilPropertyFormGUI $form)
	{
		$mainMode = (int)$form->getInput('modus');
		$failedOpt = (int)$form->getInput('modus_'.$mainMode.'_failed');
		
		if( $failedOpt )
		{
			switch($mainMode)
			{
				case ilLPObjSettings::LP_MODE_CMIX_COMPLETED:
					return ilLPObjSettings::LP_MODE_CMIX_COMPL_WITH_FAILED;

				case ilLPObjSettings::LP_MODE_CMIX_PASSED:
					return ilLPObjSettings::LP_MODE_CMIX_PASSED_WITH_FAILED;

				case ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED:
					return ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED;
			}
		}
		
		return $mainMode;
	}
	
	public static function getDefaultModes($a_lp_active)
	{
		return array(
			ilLPObjSettings::LP_MODE_DEACTIVATED,
			ilLPObjSettings::LP_MODE_CMIX_COMPLETED,
			ilLPObjSettings::LP_MODE_CMIX_COMPL_WITH_FAILED,
			ilLPObjSettings::LP_MODE_CMIX_PASSED,
			ilLPObjSettings::LP_MODE_CMIX_PASSED_WITH_FAILED,
			ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED,
			ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED
		);
	}
	
	public function getDefaultMode()
	{
		return ilLPObjSettings::LP_MODE_DEACTIVATED;
	}
	
	public function getValidModes()
	{
		return array(
			ilLPObjSettings::LP_MODE_DEACTIVATED,
			ilLPObjSettings::LP_MODE_CMIX_COMPLETED,
			ilLPObjSettings::LP_MODE_CMIX_COMPL_WITH_FAILED,
			ilLPObjSettings::LP_MODE_CMIX_PASSED,
			ilLPObjSettings::LP_MODE_CMIX_PASSED_WITH_FAILED,
			ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED,
			ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED
		);
	}
}
