<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Task\UserInteraction;
use ILIAS\BackgroundTasks\Value;

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
 * Class AbstractUserInteraction
 * @package ILIAS\BackgroundTasks\Implementation\Tasks
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractUserInteraction extends AbstractTask implements UserInteraction
{
    
    /**
     * @inheritDoc
     */
    public function getMessage(array $input) : string
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
    
    public function getSkippedValue(array $input) : Value
    {
        return $input[0];
    }
    
    public function isFinal() : bool
    {
        return true;
    }
}
