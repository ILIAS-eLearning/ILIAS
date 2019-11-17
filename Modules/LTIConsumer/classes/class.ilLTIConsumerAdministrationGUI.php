<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLTIConsumingAdministrationGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerAdministrationGUI
{
	const REDIRECTION_CMD_PARAMETER = 'redirectCmd';
	
	const CMD_SHOW_GLOBAL_PROVIDER = 'showGlobalProvider';
	const CMD_APPLY_GLOBAL_PROVIDER_FILTER = 'applyGlobalProviderFilter';
	const CMD_RESET_GLOBAL_PROVIDER_FILTER = 'resetGlobalProviderFilter';
	const CMD_SHOW_GLOBAL_PROVIDER_FORM = 'showGlobalProviderForm';
	const CMD_SAVE_GLOBAL_PROVIDER_FORM = 'saveGlobalProviderForm';
	
	const CMD_SHOW_USER_PROVIDER = 'showUserProvider';
	const CMD_SHOW_USER_PROVIDER_FORM = 'showUserProviderForm';
	const CMD_SAVE_USER_PROVIDER_FORM = 'saveUserProviderForm';
	
	const CMD_ACCEPT_PROVIDER_AS_GLOBAL = 'acceptProviderAsGlobal';
	const CMD_ACCEPT_PROVIDER_AS_GLOBAL_MULTI = 'acceptProviderAsGlobalMulti';
	const CMD_RESET_PROVIDER_TO_USER_SCOPE = 'resetProviderToUserScope';
	const CMD_RESET_PROVIDER_TO_USER_SCOPE_MULTI = 'resetProviderToUserScopeMulti';
	
	const CMD_DELETE_GLOBAL_PROVIDER = 'deleteGlobalProvider';
	const CMD_DELETE_GLOBAL_PROVIDER_MULTI = 'deleteGlobalProviderMulti';
	const CMD_DELETE_USER_PROVIDER = 'deleteUserProvider';
	const CMD_DELETE_USER_PROVIDER_MULTI = 'deleteUserProviderMulti';
	const CMD_PERFORM_DELETE_PROVIDERS = 'performDeleteProviders';

	const CMD_SHOW_SETTINGS = 'showSettings';
	const CMD_SAVE_SETTINGS = 'saveSettings';
	const CMD_ROLE_AUTOCOMPLETE = 'roleAutocomplete';
	
	const CMD_SHOW_USAGES = 'showUsages';
	
	public function __construct()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$DIC->language()->loadLanguageModule("rep");
	}
	
	protected function initSubTabs()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$DIC->tabs()->clearSubTabs();
		
		$DIC->tabs()->addSubTab('global_provider',
			$DIC->language()->txt('global_provider_subtab'),
			$DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_GLOBAL_PROVIDER)
		);
		
		$DIC->tabs()->addSubTab('user_provider',
			$DIC->language()->txt('user_provider_subtab'),
			$DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_USER_PROVIDER)
		);
		
		/* currently no settings at all
		$DIC->tabs()->addSubTab('settings',
			$DIC->language()->txt('settings_subtab'),
			$DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_SETTINGS)
		);*/
		
		// TODO: Implement Screen showing all Objects in Reporsitory
		/*$DIC->tabs()->addSubTab('usage',
			$DIC->language()->txt('usage_subtab'),
			$DIC->ctrl()->getLinkTarget($this, 'showUsage')
		);*/
	}
	
	public function executeCommand()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$this->initSubTabs();
		
		switch( $DIC->ctrl()->getNextClass() )
		{
			default:
				
				$cmd = $DIC->ctrl()->getCmd(self::CMD_SHOW_GLOBAL_PROVIDER).'Cmd';
				$this->{$cmd}();
		}
	}
	
	protected function applyGlobalProviderFilterCmd()
	{
		$table = $this->buildProviderTable($this, self::CMD_SHOW_GLOBAL_PROVIDER);
		$table->writeFilterToSession();
		$table->resetOffset();
		$this->showGlobalProviderCmd();
	}
	
	protected function resetGlobalProviderFilterCmd()
	{
		$table = $this->buildProviderTable($this, self::CMD_SHOW_GLOBAL_PROVIDER);
		$table->resetFilter();
		$table->resetOffset();
		$this->showGlobalProviderCmd();
	}
	
	protected function showGlobalProviderCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$DIC->tabs()->activateSubTab('global_provider');
		
		$button = $DIC->ui()->factory()->button()->standard(
			$DIC->language()->txt('lti_add_global_provider'),
			$DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_GLOBAL_PROVIDER_FORM)
		);
		
		$DIC->toolbar()->addComponent($button);
		
		$table = $this->buildProviderTable($this, self::CMD_SHOW_GLOBAL_PROVIDER);
		$table->setEditProviderCmd(self::CMD_SHOW_GLOBAL_PROVIDER_FORM);
		$table->setDeleteProviderCmd(self::CMD_DELETE_GLOBAL_PROVIDER);
		$table->setDeleteProviderMultiCmd(self::CMD_DELETE_GLOBAL_PROVIDER_MULTI);
		$table->setResetProviderToUserScopeCmd(self::CMD_RESET_PROVIDER_TO_USER_SCOPE);
		$table->setResetProviderToUserScopeMultiCmd(self::CMD_RESET_PROVIDER_TO_USER_SCOPE_MULTI);
		
		$table->init();
		
		$providerList = new ilLTIConsumeProviderList();
		$providerList->setScopeFilter(ilLTIConsumeProviderList::SCOPE_GLOBAL);
		
		if( $table->getFilterItemByPostVar('title')->getValue() )
		{
			$providerList->setTitleFilter($table->getFilterItemByPostVar('title')->getValue());
		}
		
		if( $table->getFilterItemByPostVar('category')->getValue() )
		{
			$providerList->setCategoryFilter($table->getFilterItemByPostVar('category')->getValue());
		}
		
		if( $table->getFilterItemByPostVar('keyword')->getValue() )
		{
			$providerList->setKeywordFilter($table->getFilterItemByPostVar('keyword')->getValue());
		}
		
		if( $table->getFilterItemByPostVar('outcome')->getChecked() )
		{
			$providerList->setHasOutcomeFilter(true);
		}
		
		if( $table->getFilterItemByPostVar('internal')->getChecked() )
		{
			$providerList->setIsExternalFilter(false);
		}
		
		if( $table->getFilterItemByPostVar('with_key')->getChecked() )
		{
			$providerList->setIsProviderKeyCustomizableFilter(false);
		}

		$providerList->load();
		
		$table->setData($providerList->getTableData());
		
		$DIC->ui()->mainTemplate()->setContent($table->getHTML());
	}
	
	protected function showGlobalProviderFormCmd(ilLTIConsumeProviderFormGUI $form = null)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$DIC->tabs()->activateSubTab('global_provider');
		
		if( $form === null )
		{
			if( isset($_GET['provider_id']) )
			{
				$DIC->ctrl()->saveParameter($this, 'provider_id');
				$provider = new ilLTIConsumeProvider((int)$_GET['provider_id']);
			}
			else
			{
				$provider = new ilLTIConsumeProvider();
			}
			
			$form = $this->buildProviderForm(
				$provider, self::CMD_SAVE_GLOBAL_PROVIDER_FORM, self::CMD_SHOW_GLOBAL_PROVIDER
			);
		}
		
		$DIC->ui()->mainTemplate()->setContent($form->getHTML());
	}
	
	protected function saveGlobalProviderFormCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$provider = $this->fetchProvider();
		
		$form = $this->buildProviderForm(
			$provider, self::CMD_SAVE_GLOBAL_PROVIDER_FORM, self::CMD_SHOW_GLOBAL_PROVIDER
		);
		
		if( $form->checkInput() )
		{
			$form->initProvider($provider);
			
			if( !$provider->getCreator() )
			{
				$provider->setCreator($DIC->user()->getId());
			}
			
			$provider->setIsGlobal(true);
			$provider->save();
			
			$DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
		}
		
		$this->showGlobalProviderFormCmd($form);
	}
	
	protected function showUserProviderCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$DIC->tabs()->activateSubTab('user_provider');
		
		$providerList = new ilLTIConsumeProviderList();
		$providerList->setScopeFilter(ilLTIConsumeProviderList::SCOPE_USER);
		$providerList->load();
		
		$table = $this->buildProviderTable($this, self::CMD_SHOW_USER_PROVIDER);
		$table->setEditProviderCmd(self::CMD_SHOW_USER_PROVIDER_FORM);
		$table->setAcceptProviderAsGlobalMultiCmd(self::CMD_ACCEPT_PROVIDER_AS_GLOBAL_MULTI);
		$table->setAcceptProviderAsGlobalCmd(self::CMD_ACCEPT_PROVIDER_AS_GLOBAL);
		$table->setDeleteProviderCmd(self::CMD_DELETE_USER_PROVIDER);
		$table->setDeleteProviderMultiCmd(self::CMD_DELETE_USER_PROVIDER_MULTI);

		$table->setData($providerList->getTableData());

		$table->init();
		
		$DIC->ui()->mainTemplate()->setContent($table->getHTML());
	}
	
	protected function showUserProviderFormCmd(ilLTIConsumeProviderFormGUI $form = null)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$DIC->tabs()->activateSubTab('user_provider');
		
		if( $form === null )
		{
			if( isset($_GET['provider_id']) )
			{
				$DIC->ctrl()->saveParameter($this, 'provider_id');
				$provider = new ilLTIConsumeProvider((int)$_GET['provider_id']);
			}
			else
			{
				$provider = new ilLTIConsumeProvider();
			}
			
			$form = $this->buildProviderForm(
				$provider, self::CMD_SAVE_USER_PROVIDER_FORM, self::CMD_SHOW_USER_PROVIDER
			);
		}
		
		$DIC->ui()->mainTemplate()->setContent($form->getHTML());
	}
	
	protected function saveUserProviderFormCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$provider = $this->fetchProvider();
		
		$form = $this->buildProviderForm(
			$provider, self::CMD_SAVE_USER_PROVIDER_FORM, self::CMD_SHOW_USER_PROVIDER
		);
		
		if( $form->checkInput() )
		{
			$form->initProvider($provider);
			$provider->setIsGlobal(false);
			$provider->save();
			
			$DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
		}
		
		$this->showUserProviderFormCmd($form);
	}
	
	protected function acceptProviderAsGlobalMultiCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$providers = $this->fetchProviderMulti();
		
		if( !count($providers) )
		{
			ilUtil::sendFailure($DIC->language()->txt('lti_no_provider_selected'), true);
			$DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
		}
		
		foreach($providers as $provider)
		{
			if( !$provider->isAcceptableAsGlobal() )
			{
				ilUtil::sendFailure($DIC->language()->txt('lti_at_least_one_not_acceptable_as_global'), true);
				$DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
			}
		}
		
		$this->performAcceptProvidersAsGlobal($providers);
		
		ilUtil::sendSuccess($DIC->language()->txt('lti_success_accept_as_global_multi'), true);
		$DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
	}
	
	protected function acceptProviderAsGlobalCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$provider = $this->fetchProvider();
		
		if( $provider->isAcceptableAsGlobal() )
		{
			$this->performAcceptProvidersAsGlobal([$provider]);
		}
		
		ilUtil::sendSuccess($DIC->language()->txt('lti_success_accept_as_global'), true);
		$DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
	}
	
	/**
	 * @param ilLTIConsumeProvider[] $providers
	 */
	protected function performAcceptProvidersAsGlobal(array $providers)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		foreach($providers as $provider)
		{
			$provider->setIsGlobal(true);
			$provider->setAcceptedBy($DIC->user()->getId());
			$provider->save();
		}
	}
	
	protected function resetProviderToUserScopeMultiCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$providers = $this->fetchProviderMulti();
		
		if( !count($providers) )
		{
			ilUtil::sendFailure($DIC->language()->txt('lti_no_provider_selected'), true);
			$DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
		}
		
		foreach($providers as $provider)
		{
			if( !$provider->isResetableToUserDefined() )
			{
				ilUtil::sendFailure($DIC->language()->txt('lti_at_least_one_not_resetable_to_usr_def'), true);
				$DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
			}
		}
		
		$this->performResetProvidersToUserScope($providers);
		
		ilUtil::sendSuccess($DIC->language()->txt('lti_success_reset_to_usr_def_multi'), true);
		$DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
	}
	
	protected function resetProviderToUserScopeCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$provider = $this->fetchProvider();
		
		if( $provider->isResetableToUserDefined() )
		{
			$this->performResetProvidersToUserScope([$provider]);
		}
		
		ilUtil::sendSuccess($DIC->language()->txt('lti_success_reset_to_usr_def'), true);
		$DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
	}
	
	/**
	 * @param ilLTIConsumeProvider[] $providers
	 */
	protected function performResetProvidersToUserScope(array $providers)
	{
		foreach($providers as $provider)
		{
			$provider->setIsGlobal(false);
			$provider->setAcceptedBy(0);
			$provider->save();
		}
	}
	
	protected function deleteGlobalProviderMultiCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$DIC->tabs()->activateSubTab('global_provider');
		
		$DIC->ctrl()->setParameter($this, self::REDIRECTION_CMD_PARAMETER, self::CMD_SHOW_GLOBAL_PROVIDER);
		
		$providers = $this->fetchProviderMulti();
		
		if( !$this->validateProviderDeletionSelection($providers) )
		{
			$DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
		}
		
		$this->confirmDeleteProviders($providers, self::CMD_SHOW_GLOBAL_PROVIDER);
	}
	
	protected function deleteGlobalProviderCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$DIC->tabs()->activateSubTab('global_provider');
		
		$DIC->ctrl()->setParameter($this, self::REDIRECTION_CMD_PARAMETER, self::CMD_SHOW_GLOBAL_PROVIDER);
		
		$provider = $this->fetchProvider();
		$providers = [$provider->getId() => $provider];
		
		if( !$this->validateProviderDeletionSelection($providers) )
		{
			$DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
		}
		
		$this->confirmDeleteProviders($providers, self::CMD_SHOW_GLOBAL_PROVIDER);
	}
	
	protected function deleteUserProviderMultiCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$DIC->tabs()->activateSubTab('user_provider');
		
		$DIC->ctrl()->setParameter($this, self::REDIRECTION_CMD_PARAMETER, self::CMD_SHOW_USER_PROVIDER);
		
		$providers = $this->fetchProviderMulti();
		
		if( !$this->validateProviderDeletionSelection($providers) )
		{
			$DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
		}
		
		$this->confirmDeleteProviders($providers, self::CMD_SHOW_USER_PROVIDER);
	}
	
	protected function deleteUserProviderCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$DIC->tabs()->activateSubTab('global_provider');
		
		$DIC->ctrl()->setParameter($this, self::REDIRECTION_CMD_PARAMETER, self::CMD_SHOW_USER_PROVIDER);
		
		$provider = $this->fetchProvider();
		$providers = [$provider->getId() => $provider];
		
		if( !$this->validateProviderDeletionSelection($providers) )
		{
			$DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
		}
		
		$this->confirmDeleteProviders($providers, self::CMD_SHOW_USER_PROVIDER);
	}
	
	protected function validateProviderDeletionSelection(array $providers)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		if( !count($providers) )
		{
			ilUtil::sendFailure($DIC->language()->txt('lti_no_provider_selected'), true);
			return false;
		}
		
		$providerList = $this->getProviderListForIds(array_keys($providers));
		
		foreach($providers as $provider)
		{
			if( $providerList->hasUsages($provider->getId()) )
			{
				ilUtil::sendFailure($DIC->language()->txt('lti_at_least_one_prov_has_usages'), true);
				return false;
			}
		}
		
		return true;
	}
	
	protected function confirmDeleteProviders(array $providers, string $cancelCommand)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$confirmationGUI = new ilConfirmationGUI();
		
		$confirmationGUI->setFormAction($DIC->ctrl()->getFormAction($this));
		$confirmationGUI->setCancel($DIC->language()->txt('cancel'), $cancelCommand);
		$confirmationGUI->setConfirm($DIC->language()->txt('delete'), self::CMD_PERFORM_DELETE_PROVIDERS);
		
		$confirmationGUI->setHeaderText($DIC->language()->txt('lti_confirm_delete_providers'));
		
		foreach($providers as $provider)
		{
			/* @var ilLTIConsumeProvider $provider */
			
			if( $provider->getProviderIcon()->exists() )
			{
				$providerIcon = $provider->getProviderIcon()->getAbsoluteFilePath();
			}
			else
			{
				$providerIcon = ilObject::_getIcon("", "small", "lti");
			}
			
			$confirmationGUI->addItem(
				'provider_ids[]',
				$provider->getId(),
				$provider->getTitle(),
				$providerIcon
			);
		}
		
		$DIC->ui()->mainTemplate()->setContent($confirmationGUI->getHTML());
	}
	
	protected function performDeleteProvidersCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$providers = $this->fetchProviderMulti();
		
		if( $this->validateProviderDeletionSelection($providers) )
		{
			foreach($providers as $provider)
			{
				$provider->delete();
			}
			
			ilUtil::sendSuccess($DIC->language()->txt('lti_success_delete_provider_multi'), true);
		}
		
		$DIC->ctrl()->redirect($this, $_GET[self::REDIRECTION_CMD_PARAMETER]);
	}
	
	/**
	 * @param $parentGui
	 * @param $parentCmd
	 * @param $editCmd
	 * @return ilLTIConsumerProviderTableGUI
	 */
	protected function buildProviderTable($parentGui, $parentCmd)
	{
		$table = new ilLTIConsumerProviderTableGUI(
			$parentGui, $parentCmd
		);
		
		$table->setFilterCommand(self::CMD_APPLY_GLOBAL_PROVIDER_FILTER);
		$table->setResetCommand(self::CMD_RESET_GLOBAL_PROVIDER_FILTER);
		
		$table->setAvailabilityColumnEnabled(true);
		$table->setProviderCreatorColumnEnabled(true);
		
		$table->setActionsColumnEnabled(true);
		$table->setDetailedUsagesEnabled(true);
		
		return $table;
	}
	
	protected function showUsageCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$DIC->tabs()->activateSubTab('usage');
		
		$DIC->ui()->mainTemplate()->setContent(__METHOD__);
	}
	
	/**
	 * @return ilLTIConsumeProviderFormGUI
	 */
	protected function buildProviderForm(ilLTIConsumeProvider $provider, $saveCmd, $cancelCmd)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$form = new ilLTIConsumeProviderFormGUI($provider);
		$form->setAdminContext(true);
		$form->initForm($DIC->ctrl()->getFormAction($this), $saveCmd, $cancelCmd);
		
		return $form;
	}
	
	/**
	 * @return ilLTIConsumeProvider
	 */
	protected function fetchProvider()
	{
		if (isset($_GET['provider_id'])) {
			$provider = new ilLTIConsumeProvider((int)$_GET['provider_id']);
		} else {
			$provider = new ilLTIConsumeProvider();
		}
		return $provider;
	}
	
	/**
	 * @return ilLTIConsumeProvider[]
	 */
	protected function fetchProviderMulti()
	{
		$providers = [];
		
		if( !isset($_POST['provider_ids']) || !is_array($_POST['provider_ids']) )
		{
			return $providers;
		}
		
		foreach($_POST['provider_ids'] as $providerId)
		{
			$providers[(int)$providerId] = new ilLTIConsumeProvider((int)$providerId);
		}
		
		return $providers;
	}
	
	/**
	 * @return string
	 */
	protected function getContextRelatedRedirectionCommand()
	{
		if( isset($_GET[self::CONTEXT_PARAMETER]) )
		{
			switch($_GET[self::CONTEXT_PARAMETER])
			{
				case self::CONTEXT_GLOBAL_PROVIDER:
					
					return self::CMD_SHOW_GLOBAL_PROVIDER;
					
				case self::CONTEXT_USER_PROVIDER:
					
					return self::CMD_SHOW_USER_PROVIDER;
			}
		}
		
		return '';
	}
	
	protected function showSettingsCmd(ilPropertyFormGUI $form = null)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		return ''; // no settings at all currently
		
		$DIC->tabs()->activateSubTab('settings');
		
		if( $form === null )
		{
			$form = $this->buildSettingsForm();
		}
		
		$DIC->ui()->mainTemplate()->setContent($form->getHTML());
	}
	
	protected function saveSettingsCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		return ''; // no settings at all currently
		
		$form = $this->buildSettingsForm();
		
		if( !$form->checkInput() )
		{
			$this->showSettingsCmd($form);
			return;
		}
		
		$DIC->ctrl()->redirect($this, self::CMD_SHOW_SETTINGS);
	}
	
	/**
	 * @return ilPropertyFormGUI
	 */
	protected function buildSettingsForm()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$form = new ilPropertyFormGUI();
		
		$form->setFormAction($DIC->ctrl()->getFormAction($this));
		$form->addCommandButton(self::CMD_SAVE_SETTINGS, $DIC->language()->txt('save'));
		$form->setTitle($DIC->language()->txt('lti_global_settings_form'));
		
		return $form;
	}
	
	/**
	 * @param array $providerIds
	 * @return ilLTIConsumeProviderList
	 */
	protected function getProviderListForIds(array $providerIds): ilLTIConsumeProviderList
	{
		$providerList = new ilLTIConsumeProviderList();
		$providerList->setIdsFilter($providerIds);
		$providerList->load();
		$providerList->loadUsages();
		return $providerList;
	}
}
