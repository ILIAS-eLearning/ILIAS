<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Task service dependencies
 *
 * @author killing@leifos.de
 * @ingroup ServiceTasks
 */
class ilTaskServiceDependencies
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var \ilObjUser
	 */
	protected $user;

	/**
	 * @var \ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilDerivedTaskProviderMasterFactory
	 */
	protected $derived_task_provider_master_factory;

	/**
	 * Constructor
	 * @param ilObjUser $user
	 * @param ilLanguage $lng
	 * @param \ILIAS\DI\UIServices $ui
	 */
	public function __construct(ilObjUser $user, ilLanguage $lng, \ILIAS\DI\UIServices $ui,
								\ilAccessHandler $access, \ilDerivedTaskProviderMasterFactory $derived_task_provider_master_factory)
	{
		$this->lng = $lng;
		$this->ui = $ui;
		$this->user = $user;
		$this->access = $access;
		$this->derived_task_provider_master_factory = $derived_task_provider_master_factory;
	}

	/**
	 * Get derived task provider master factory
	 *
	 * @return ilDerivedTaskProviderMasterFactory
	 */
	public function getDerivedTaskProviderMasterFactory(): \ilDerivedTaskProviderMasterFactory
	{
		return $this->derived_task_provider_master_factory;
	}

	/**
	 * Get language object
	 *
	 * @return \ilLanguage
	 */
	public function language(): \ilLanguage
	{
		return $this->lng;
	}

	/**
	 * Get current user
	 *
	 * @return \ilObjUser
	 */
	public function user(): \ilObjUser
	{
		return $this->user;
	}

	/**
	 * Get ui service
	 *
	 * @return \ILIAS\DI\UIServices
	 */
	public function ui(): \ILIAS\DI\UIServices
	{
		return $this->ui;
	}

	/**
	 * Get access
	 *
	 * @return \ilAccessHandler
	 */
	protected function getAccess(): \ilAccessHandler
	{
		return $this->access;
	}

}