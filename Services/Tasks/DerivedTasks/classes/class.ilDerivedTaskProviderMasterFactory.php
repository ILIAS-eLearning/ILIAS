<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Derived task providers factory
 *
 * @author killing@leifos.de
 * @ingroup ServicesTasks
 */
class ilDerivedTaskProviderMasterFactory
{
	/**
	 * @var ilTaskService
	 */
	protected $service;

	/**
	 * @var ilDerivedTaskProviderFactory[]
	 */
	protected $default_provider_factories = array(
		ilExerciseDerivedTaskProviderFactory::class,
		\ilForumDerivedTaskProviderFactory::class,
	);

	/**
	 * @var ilDerivedTaskProviderFactory[]
	 */
	protected $provider_factories;

	/**
	 * Constructor
	 */
	public function __construct(ilTaskService $service, $provider_factories = null)
	{
		if (is_null($provider_factories))
		{
			$this->provider_factories = array_map(function ($class) use ($service){
				return new $class($service);
			}, $this->default_provider_factories);
		}
		else
		{
			$this->provider_factories = $provider_factories;
		}
		$this->service = $service;
	}

	/**
	 * Get all task providers
	 *
	 * @param bool $active_only get only active providers
	 * @param int $user_id get instances for user with user id
	 * @return ilLearningHistoryProviderInterface[]
	 */
	public function getAllProviders($active_only = false, $user_id = null)
	{
		$providers = array();

		if ($user_id == 0) {
			$user_id = $this->service->getDependencies()->user()->getId();
		}

		foreach ($this->provider_factories as $provider_factory) {
			foreach ($provider_factory->getProviders() as $provider)
			{
				if (!$active_only || $provider->isActive())
				{
					$providers[] = $provider;
				}
			}
		}

		return $providers;
	}
}
