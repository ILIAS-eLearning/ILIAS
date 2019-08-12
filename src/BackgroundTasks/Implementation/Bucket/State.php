<?php

namespace ILIAS\BackgroundTasks\Implementation\Bucket;

class State
{

    /**
     * @var int In the database, not yet started by a worker.
     */
    const SCHEDULED = 0;
    /**
     * @var int A worker is currently doing something with the observed tasks.
     */
    const RUNNING = 1;
    /**
     * @var int The user needs to do some interaction for the observed tasks to continue.
     */
    const USER_INTERACTION = 2;
    /**
     * @var int Everything's done here.
     */
    const FINISHED = 3;
    /**
     * @var int Something went wrong during the execution!
     */
    const ERROR = 4;
}