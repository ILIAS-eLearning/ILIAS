<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Task service
 *
 * @author killing@leifos.de
 * @ingroup ServiceTasks
 */
class ilTaskService
{
	/**
	 * @var ilTaskServiceDependencies
	 */
	protected $_deps;

	/**
	 * This constructor contains all evil dependencies, that should e.g. be replaced for testing.
	 * ilDerivedTaskProviderMasterFactory is such a dependency, because it collects all "consumers" of the
	 * derived task service.
	 *
	 * @param ilObjUser $user
	 * @param ilLanguage $lng
	 * @param \ILIAS\DI\UIServices $ui
	 * @param ilAccessHandler $access
	 * @param ilDerivedTaskProviderMasterFactory $derived_task_provider_master_factory
	 */
	public function __construct(ilObjUser $user, ilLanguage $lng, \ILIAS\DI\UIServices $ui,
								\ilAccessHandler $access, ilDerivedTaskProviderMasterFactory $derived_task_provider_master_factory = null)
	{
		if (is_null($derived_task_provider_master_factory))
		{
			$derived_task_provider_master_factory = new ilDerivedTaskProviderMasterFactory($this);
		}
		$this->_deps = new ilTaskServiceDependencies($user, $lng, $ui, $access, $derived_task_provider_master_factory);
	}

	/**
	 * Get dependencies
	 *
	 * This function is not part of the API and for internal use only.
	 *
	 * @return ilTaskServiceDependencies
	 */
	public function getDependencies(): ilTaskServiceDependencies
	{
		return $this->_deps;
	}



	/**
	 * Subservice for derived tasks
	 *
	 * @return ilDerivedTaskService
	 */
	public function derived()
	{
		return new ilDerivedTaskService($this);
	}


}