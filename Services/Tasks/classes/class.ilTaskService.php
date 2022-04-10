<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\DI\UIServices;

/**
 * Task service
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaskService
{
    protected ilTaskServiceDependencies $_deps;

    /**
     * This constructor contains all evil dependencies, that should e.g. be replaced for testing.
     * ilDerivedTaskProviderFactory[] is such a dependency, because it collects all "consumers" of the
     * derived task service.
     *
     * @param ilDerivedTaskProviderFactory[] $derived_task_provider_factories
     */
    public function __construct(
        ilObjUser $user,
        ilLanguage $lng,
        UIServices $ui,
        ilAccessHandler $access,
        array $derived_task_provider_factories = null
    ) {
        $derived_task_provider_master_factory = new ilDerivedTaskProviderMasterFactory($this, $derived_task_provider_factories);
        $this->_deps = new ilTaskServiceDependencies($user, $lng, $ui, $access, $derived_task_provider_master_factory);
    }

    /**
     * Get dependencies
     *
     * This function is not part of the API and for internal use only.
     */
    public function getDependencies() : ilTaskServiceDependencies
    {
        return $this->_deps;
    }



    /**
     * Subservice for derived tasks
     *
     * @return ilDerivedTaskService
     */
    public function derived() : ilDerivedTaskService
    {
        return new ilDerivedTaskService($this);
    }
}
