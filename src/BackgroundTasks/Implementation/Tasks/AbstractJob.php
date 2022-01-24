<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Task\Job;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class AbstractJob
 * @package ILIAS\BackgroundTasks\Implementation\Tasks
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class AbstractJob extends AbstractTask implements Job
{
    
    /**
     * @inheritdoc
     */
    public function getInput() : array
    {
        return $this->input;
    }
}
