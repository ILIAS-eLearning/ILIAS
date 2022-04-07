<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Task service
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDerivedTaskService
{
    protected ilTaskServiceDependencies $_deps;

    protected ilTaskService $service;

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
     */
    public function factory() : ilDerivedTaskFactory
    {
        return new ilDerivedTaskFactory($this->service);
    }
}
