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
 * Dummy derived task provider
 *
 * @author killing@leifos.de
 */
class ilDummyDerivedTaskProvider implements ilDerivedTaskProvider
{
    protected ilTaskService $task_service;

    /**
     * Constructor
     */
    public function __construct(ilTaskService $task_service)
    {
        $this->task_service = $task_service;
    }

    /**
     * @inheritdoc
     */
    public function isActive() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getTasks(int $user_id) : array
    {
        $tasks = [];

        $tasks[] = $this->task_service->derived()->factory()->task(
            "title",
            123,
            1234,
            1000
        );

        return $tasks;
    }
}
