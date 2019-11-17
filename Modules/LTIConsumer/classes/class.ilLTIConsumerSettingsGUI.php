<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLTIConsumerSettingsGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 *
 * @ilCtrl_Calls ilLTIConsumerSettingsGUI: ilLTIConsumeProviderSettingsGUI
 */
class ilLTIConsumerSettingsGUI
{
	const SUBTAB_ID_OBJECT_SETTINGS = 'subtab_object_settings';
	const SUBTAB_ID_PROVIDER_SETTINGS = 'subtab_provider_settings';
	
	const CMD_SHOW_SETTINGS = 'showSettings';
	const CMD_SAVE_SETTINGS = 'saveSettings';
	
	/**
	 * @var ilObjLTIConsumer
	 */
	protected $object;
	
	/**
	 * @var ilLTIConsumerAccess
	 */
	protected $access;
	
	/**
	 * ilLTIConsumerAccess constructor.
	 * @param ilObjLTIConsumer $object
	 */
	public function __construct(ilObjLTIConsumer $object, ilLTIConsumerAccess $access)
	{
		$this->object = $object;
		$this->access = $access;
	}
	
	protected function initSubTabs()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		$DIC->language()->loadLanguageModule('lti');
		$DIC->tabs()->addSubTab(self::SUBTAB_ID_OBJECT_SETTINGS,
			$DIC->language()->txt(self::SUBTAB_ID_OBJECT_SETTINGS),
			$DIC->ctrl()->getLinkTarget($this)
		);
		
		if( $this->needsProviderSettingsSubTab() )
		{
			$DIC->tabs()->addSubTab(self::SUBTAB_ID_PROVIDER_SETTINGS,
				$DIC->language()->txt(self::SUBTAB_ID_PROVIDER_SETTINGS),
				$DIC->ctrl()->getLinkTargetByClass(ilLTIConsumeProviderSettingsGUI::class)
			);
		}
		
	}
	
	protected function needsProviderSettingsSubTab()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		if( $this->object->getProvider()->isGlobal() )
		{
			return false;
		}
		
		if( $this->object->getProvider()->getCreator() != $DIC->user()->getId() )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * Execute Command
	 */
	public function executeCommand()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$this->initSubTabs();
		
		switch( $DIC->ctrl()->getNextClass() )
		{
			case strtolower(ilLTIConsumeProviderSettingsGUI::class):
				
				$DIC->tabs()->activateSubTab(self::SUBTAB_ID_PROVIDER_SETTINGS);
				
				$gui = new ilLTIConsumeProviderSettingsGUI($this->object, $this->access);
				$DIC->ctrl()->forwardCommand($gui);
				break;
				
			default:
				
				$DIC->tabs()->activateSubTab(self::SUBTAB_ID_OBJECT_SETTINGS);
				
				$command = $DIC->ctrl()->getCmd(self::CMD_SHOW_SETTINGS).'Cmd';
				$this->{$command}();
		}
	}
	
	protected function showSettingsCmd(ilLTIConsumerSettingsFormGUI $form = null)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		if( $form === null )
		{
			$form = $this->buildForm();
		}
		
		$DIC->ui()->mainTemplate()->setContent($form->getHTML());
	}
	
	protected function saveSettingsCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$form = $this->buildForm();
		
		if( $form->checkInput() )
		{
			$form->initObject($this->object);
			$this->object->update();
			
			$DIC->ctrl()->redirect($this, self::CMD_SHOW_SETTINGS);
		}
		
		$this->showSettingsCmd($form);
	}
	
	protected function buildForm()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$form = new ilLTIConsumerSettingsFormGUI(
			$this->object, $DIC->ctrl()->getFormAction($this),
			self::CMD_SAVE_SETTINGS,self::CMD_SHOW_SETTINGS
		);
		
		return $form;
	}
}
