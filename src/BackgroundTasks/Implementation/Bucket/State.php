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

namespace ILIAS\BackgroundTasks\Implementation\Bucket;

class State
{
    /**
     * @var int In the database, not yet started by a worker.
     */
    public const SCHEDULED = 0;
    /**
     * @var int A worker is currently doing something with the observed tasks.
     */
    public const RUNNING = 1;
    /**
     * @var int The user needs to do some interaction for the observed tasks to continue.
     */
    public const USER_INTERACTION = 2;
    /**
     * @var int Everything's done here.
     */
    public const FINISHED = 3;
    /**
     * @var int Something went wrong during the execution!
     */
    public const ERROR = 4;
}
