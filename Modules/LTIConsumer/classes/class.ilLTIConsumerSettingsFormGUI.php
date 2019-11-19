<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLTIConsumerSettingsFormGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerSettingsFormGUI extends ilPropertyFormGUI
{
	/**
	 * @var ilObjLTIConsumer
	 */
	protected $object;
	
	/**
	 * ilLTIConsumerSettingsFormGUI constructor.
	 * @param ilObjLTIConsumer $object
	 * @param $formaction
	 * @param $saveCommand
	 * @param $cancelCommand
	 */
	public function __construct(ilObjLTIConsumer $object, $formaction, $saveCommand, $cancelCommand)
	{
		$this->object = $object;
		
		parent::__construct();
		
		$this->initForm($formaction, $saveCommand, $cancelCommand);
	}
	
	/**
	 * @param $formaction
	 * @param $saveCommand
	 * @param $cancelCommand
	 */
	protected function initForm($formaction, $saveCommand, $cancelCommand)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		$DIC->language()->loadLanguageModule('lti');
		$this->setFormAction($formaction);
		$this->addCommandButton($saveCommand, $DIC->language()->txt('save'));
		$this->addCommandButton($cancelCommand, $DIC->language()->txt('cancel'));
		
		$this->setTitle($DIC->language()->txt('lti_settings_form'));
		
		$item = new ilNonEditableValueGUI($DIC->language()->txt('provider_info'));
		$item->setValue($this->object->getProvider()->getTitle());
		$item->setInfo($this->object->getProvider()->getDescription());
		$this->addItem($item);
		
		$item = new ilTextInputGUI($DIC->language()->txt('title'), 'title');
		$item->setSize(40);
		$item->setMaxLength(128);
		$item->setRequired(true);
		$item->setInfo($DIC->language()->txt('title_info'));
		$item->setValue($this->object->getTitle());
		$this->addItem($item);
		
		$item = new ilTextAreaInputGUI($DIC->language()->txt('description'), 'description');
		$item->setInfo($DIC->language()->txt('description_info'));
		$item->setRows(2);
		$item->setValue($this->object->getDescription());
		$this->addItem($item);
		
		$item = new ilCheckboxInputGUI($DIC->language()->txt('online'), 'online');
		$item->setInfo($DIC->language()->txt("online_info"));
		$item->setValue("1");
		if (!$this->object->getOfflineStatus())
		{
			$item->setChecked(true);
		}
		$this->addItem($item);
		
		if( $this->object->getProvider()->isProviderKeyCustomizable() )
		{
			$sectionHeader = new ilFormSectionHeaderGUI();
			$sectionHeader->setTitle($DIC->language()->txt('lti_con_prov_authentication'));
			$this->addItem($sectionHeader);
			
			$providerKeyInp = new ilTextInputGUI($DIC->language()->txt('lti_con_prov_key'), 'provider_key');
			$providerKeyInp->setValue($this->object->getCustomLaunchKey());
			$providerKeyInp->setRequired(true);
			$this->addItem($providerKeyInp);
			
			$providerSecretInp = new ilTextInputGUI($DIC->language()->txt('lti_con_prov_secret'), 'provider_secret');
			$providerSecretInp->setValue($this->object->getCustomLaunchSecret());
			$providerSecretInp->setRequired(true);
			$this->addItem($providerSecretInp);
		}

		if ( $this->object->getProvider()->getHasOutcome() )
        {
            $sectionHeader = new ilFormSectionHeaderGUI();
            $sectionHeader->setTitle($DIC->language()->txt('learning_progress_options'));
            $this->addItem($sectionHeader);
            $masteryScore = new ilNumberInputGUI($DIC->language()->txt('mastery_score'), 'mastery_score');
            $masteryScore->setInfo($DIC->language()->txt('mastery_score_info'));
            $masteryScore->setSuffix('%');
            $masteryScore->allowDecimals(true);
            $masteryScore->setDecimals(2);
            $masteryScore->setMinvalueShouldBeGreater(false);
            $masteryScore->setMinValue(0);
            $masteryScore->setMaxvalueShouldBeLess(false);
            $masteryScore->setMaxValue(100);
            $masteryScore->setSize(4);
            $masteryScore->setValue($this->object->getMasteryScorePercent());
            $this->addItem($masteryScore);
        }
		
		$item = new ilFormSectionHeaderGUI();
		$item->setTitle($DIC->language()->txt('lti_form_section_appearance'));
		$this->addItem($item);
		
		$item = new ilRadioGroupInputGUI($DIC->language()->txt('launch_method'), 'launch_method');
		$item->setRequired(true);
		$item->setValue($this->object->getLaunchMethod());
		$optOwnWindow = new ilRadioOption(
			$DIC->language()->txt('launch_method_own_win'), ilObjLTIConsumer::LAUNCH_METHOD_OWN_WIN
		);
		$optOwnWindow->setInfo($DIC->language()->txt('launch_method_own_win_info'));
		$item->addOption($optOwnWindow);
		$optAnyWindow = new ilRadioOption(
			$DIC->language()->txt('launch_method_new_win'), ilObjLTIConsumer::LAUNCH_METHOD_NEW_WIN
		);
		$optAnyWindow->setInfo($DIC->language()->txt('launch_method_new_win_info'));
		$item->addOption($optAnyWindow);
		$optEmbedded = new ilRadioOption(
			$DIC->language()->txt('launch_method_embedded'), ilObjLTIConsumer::LAUNCH_METHOD_EMBEDDED
		);
		$optEmbedded->setInfo($DIC->language()->txt('launch_method_embedded_info'));
		$item->addOption($optEmbedded);
		$this->addItem($item);
		
		
		if($this->object->getProvider()->getUseXapi())
		{
			$item = new ilCheckboxInputGUI($DIC->language()->txt('use_xapi'), 'use_xapi');
			$item->setInfo($DIC->language()->txt("use_xapi_info"));
			$item->setValue("1");
			if ($this->object->getUseXapi())
			{
				$item->setChecked(true);
			}
			
			if( !strlen($this->object->getProvider()->getXapiActivityId()) )
			{
				$subitem = new ilTextInputGUI($DIC->language()->txt('activity_id'), 'activity_id');
				$subitem->setSize(40);
				$subitem->setMaxLength(128);
				$subitem->setRequired(true);
				$subitem->setInfo($DIC->language()->txt('activity_id_info'));
				$subitem->setValue($this->object->getCustomActivityId());
				$item->addSubItem($subitem);
			}
			
			$subitem = new ilCheckboxInputGUI($DIC->language()->txt('show_statements'), 'show_statements');
			$subitem->setInfo($DIC->language()->txt("show_statements_info"));
			$subitem->setValue("1");
			if($this->object->isStatementsReportEnabled())
			{
				$subitem->setChecked(true);
			}
			$item->addSubItem($subitem);
			
			$highscore = new ilCheckboxInputGUI($DIC->language()->txt("highscore_enabled"), "highscore_enabled");
			$highscore->setValue(1);
			$highscore->setChecked($this->object->getHighscoreEnabled());
			$highscore->setInfo($DIC->language()->txt("highscore_description"));
			$highscore_tables = new ilRadioGroupInputGUI($DIC->language()->txt('highscore_mode'), 'highscore_mode');
			$highscore_tables->setRequired(true);
			$highscore_tables->setValue($this->object->getHighscoreMode());
			$highscore_table_own = new ilRadioOption($DIC->language()->txt('highscore_own_table'), ilObjLTIConsumer::HIGHSCORE_SHOW_OWN_TABLE);
			$highscore_table_own->setInfo($DIC->language()->txt('highscore_own_table_description'));
			$highscore_tables->addOption($highscore_table_own);
			$highscore_table_other = new ilRadioOption($DIC->language()->txt('highscore_top_table'), ilObjLTIConsumer::HIGHSCORE_SHOW_TOP_TABLE);
			$highscore_table_other->setInfo($DIC->language()->txt('highscore_top_table_description'));
			$highscore_tables->addOption($highscore_table_other);
			$highscore_table_other = new ilRadioOption($DIC->language()->txt('highscore_all_tables'), ilObjLTIConsumer::HIGHSCORE_SHOW_ALL_TABLES);
			$highscore_table_other->setInfo($DIC->language()->txt('highscore_all_tables_description'));
			$highscore_tables->addOption($highscore_table_other);
			$highscore->addSubItem($highscore_tables);
			$highscore_top_num = new ilNumberInputGUI($DIC->language()->txt("highscore_top_num"), "highscore_top_num");
			$highscore_top_num->setSize(4);
			$highscore_top_num->setRequired(true);
			$highscore_top_num->setMinValue(1);
			$highscore_top_num->setSuffix($DIC->language()->txt("highscore_top_num_unit"));
			$highscore_top_num->setValue($this->object->getHighscoreTopNum(null));
			$highscore_top_num->setInfo($DIC->language()->txt("highscore_top_num_description"));
			$highscore->addSubItem($highscore_top_num);
			$highscore_achieved_ts = new ilCheckboxInputGUI($DIC->language()->txt("highscore_achieved_ts"), "highscore_achieved_ts");
			$highscore_achieved_ts->setValue(1);
			$highscore_achieved_ts->setChecked($this->object->getHighscoreAchievedTS());
			$highscore_achieved_ts->setInfo($DIC->language()->txt("highscore_achieved_ts_description"));
			$highscore->addSubItem($highscore_achieved_ts);
			$highscore_percentage = new ilCheckboxInputGUI($DIC->language()->txt("highscore_percentage"), "highscore_percentage");
			$highscore_percentage->setValue(1);
			$highscore_percentage->setChecked($this->object->getHighscorePercentage());
			$highscore_percentage->setInfo($DIC->language()->txt("highscore_percentage_description"));
			$highscore->addSubItem($highscore_percentage);
			$highscore_wtime = new ilCheckboxInputGUI($DIC->language()->txt("highscore_wtime"), "highscore_wtime");
			$highscore_wtime->setValue(1);
			$highscore_wtime->setChecked($this->object->getHighscoreWTime());
			$highscore_wtime->setInfo($DIC->language()->txt("highscore_wtime_description"));
			$highscore->addSubItem($highscore_wtime);

			$item->addSubItem($highscore);
			$this->addItem($item);
		}
		
	}
	
	/**
	 * @param ilObjLTIConsumer $object
	 */
	public function initObject(ilObjLTIConsumer $object)
	{
		$object->setTitle($this->getInput('title'));
		$object->setDescription($this->getInput('description'));
		$object->setOfflineStatus(!(bool)$this->getInput('online'));
		
		if( $object->getProvider()->isProviderKeyCustomizable() )
		{
			$object->setCustomLaunchKey($this->getInput('provider_key'));
			$object->setCustomLaunchSecret($this->getInput('provider_secret'));
		}

		if( $object->getProvider()->getHasOutcome() )
        {
            $object->setMasteryScorePercent($this->getInput('mastery_score'));
        }

		$object->setLaunchMethod($this->getInput('launch_method'));
		$object->setUseXapi((bool)$this->getInput('use_xapi'));
		if ($object->getUseXapi()) {
			if( !strlen($this->object->getProvider()->getXapiActivityId()) )
			{
				$object->setCustomActivityId($this->getInput('activity_id'));
			}
			$object->setStatementsReportEnabled((bool)$this->getInput('show_statements'));
			$object->setHighscoreEnabled((bool) $this->getInput('highscore_enabled'));
			if( $object->getHighscoreEnabled())
			{
				// highscore settings
				$object->setHighscoreEnabled((bool) $this->getInput('highscore_enabled'));
				$object->setHighscoreAchievedTS((bool) $this->getInput('highscore_achieved_ts'));
				$object->setHighscorePercentage((bool) $this->getInput('highscore_percentage'));
				$object->setHighscoreWTime((bool) $this->getInput('highscore_wtime'));
				$object->setHighscoreMode((int) $this->getInput('highscore_mode'));
				$object->setHighscoreTopNum((int) $this->getInput('highscore_top_num'));
			}

		}
	}
}
