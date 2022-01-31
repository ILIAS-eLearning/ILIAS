<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
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
 * Class NotFoundUserInteraction
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotFoundUserInteraction extends AbstractUserInteraction
{
    /**
     * @return mixed[]
     */
    public function getInputTypes() : array
    {
        return [];
    }
    
    public function getOutputType() : Type
    {
        return new SingleType('none');
    }
    
    /**
     * @return mixed[]
     */
    public function getOptions(array $input) : array
    {
        return [];
    }
    
    public function interaction(array $input, Option $user_selected_option, Bucket $bucket) : Value
    {
        return new BooleanValue();
    }
}
