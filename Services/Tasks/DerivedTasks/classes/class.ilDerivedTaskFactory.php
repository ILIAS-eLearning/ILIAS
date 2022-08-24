<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Factory for derived task subservice
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDerivedTaskFactory
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
    public function task(string $title, int $ref_id, int $deadline, int $starting_time, int $wsp_id = 0): ilDerivedTask
    {
        return new ilDerivedTask($title, $ref_id, $deadline, $starting_time, $wsp_id);
    }

    /**
     * Entry collector
     */
    public function collector(): ilDerivedTaskCollector
    {
        return new ilDerivedTaskCollector($this->service);
    }

    /**
     * Get all task providers
     *
     * @param bool $active_only get only active providers
     * @param int|null $user_id get instances for user with user id
     * @return ilLearningHistoryProviderInterface[]
     */
    public function getAllProviders(bool $active_only = false, int $user_id = null): array
    {
        $master_factory = $this->service->getDependencies()->getDerivedTaskProviderMasterFactory();
        return $master_factory->getAllProviders($active_only, $user_id);
    }
}
