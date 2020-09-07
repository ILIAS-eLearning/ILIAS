<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Task\UserInteraction;
use ILIAS\BackgroundTasks\Value;

/**
 * Class AbstractUserInteraction
 *
 * @package ILIAS\BackgroundTasks\Implementation\Tasks
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractUserInteraction extends AbstractTask implements UserInteraction
{

    /**
     * @inheritDoc
     */
    public function getMessage(array $input)
    {
        return $message = "";
    }


    /**
     * @inheritDoc
     */
    public function canBeSkipped(array $input) : bool
    {
        return false;
    }


    /**
     * @param array $input
     *
     * @return Value
     */
    public function getSkippedValue(array $input) : Value
    {
        return $input[0];
    }
}
