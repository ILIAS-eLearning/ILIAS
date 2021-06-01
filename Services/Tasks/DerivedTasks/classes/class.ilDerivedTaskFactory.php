<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for derived task subservice
 *
 * @author killing@leifos.de
 * @ingroup ServiceTasks
 */
class ilDerivedTaskFactory
{
    protected \ilTaskServiceDependencies $_deps;

    protected \ilTaskService $service;

    /**
     * Constructor
     * @param ilTaskServiceDependencies $_deps
     */
    public function __construct(ilTaskService $service)
    {
        $this->_deps = $service->getDependencies();
        $this->service = $service;
    }

    /**
     * Subservice for derived tasks
     */
    public function task(string $title, int $ref_id, int $deadline, int $starting_time, int $wsp_id = 0) : ilDerivedTask
    {
        return new ilDerivedTask($title, $ref_id, $deadline, $starting_time, $wsp_id);
    }

    /**
     * Entry collector
     *
     * @param
     * @return
     */
    public function collector() : \ilDerivedTaskCollector
    {
        return new ilDerivedTaskCollector($this->service);
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
        $master_factory = $this->service->getDependencies()->getDerivedTaskProviderMasterFactory();
        return $master_factory->getAllProviders($active_only, $user_id);
    }
}
