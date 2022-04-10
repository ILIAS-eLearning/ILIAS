<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\Survey\Tasks\DerivedTaskProviderFactory;

/**
 * Derived task providers factory
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDerivedTaskProviderMasterFactory
{
    protected ilTaskService $service;

    /**
     * @var ilDerivedTaskProviderFactory[]
     */
    protected array $default_provider_factories = array(
        ilExerciseDerivedTaskProviderFactory::class,
        ilForumDerivedTaskProviderFactory::class,
        DerivedTaskProviderFactory::class,
        ilBlogDerivedTaskProviderFactory::class
    );

    /**
     * @var ilDerivedTaskProviderFactory[]
     */
    protected $provider_factories = [];

    /**
     * Constructor
     */
    public function __construct(ilTaskService $service, $provider_factories = null)
    {
        if (is_null($provider_factories)) {
            $this->provider_factories = array_map(fn ($class) : ilDerivedTaskProviderFactory => new $class($service), $this->default_provider_factories);
        } else {
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
    public function getAllProviders(bool $active_only = false, int $user_id = null) : array
    {
        $providers = array();

        if ($user_id == 0) {
            $user_id = $this->service->getDependencies()->user()->getId();
        }

        foreach ($this->provider_factories as $provider_factory) {
            foreach ($provider_factory->getProviders() as $provider) {
                if (!$active_only || $provider->isActive()) {
                    $providers[] = $provider;
                }
            }
        }

        return $providers;
    }
}
