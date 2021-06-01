<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Task service
 *
 * @author killing@leifos.de
 * @ingroup ServiceTasks
 */
class ilDerivedTaskService
{
    protected \ilTaskServiceDependencies $_deps;

    protected \ilTaskService $service;

    /**
     * Constructor
     */
    public function __construct(ilTaskService $service)
    {
        $this->_deps = $service->getDependencies();
        $this->service = $service;
    }

    /**
     * Subservice for derived tasks
     *
     * @return ilDerivedTaskService
     */
    public function factory() : ilDerivedTaskFactory
    {
        return new ilDerivedTaskFactory($this->service);
    }
}
