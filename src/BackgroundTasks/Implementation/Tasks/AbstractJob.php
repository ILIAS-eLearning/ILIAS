<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Task\Job;

/**
 * Class AbstractJob
 *
 * @package ILIAS\BackgroundTasks\Implementation\Tasks
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class AbstractJob extends AbstractTask implements Job
{

    /**
     * @inheritdoc
     */
    public function getInput()
    {
        return $this->input;
    }
}
