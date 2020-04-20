<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

/**
 * Class NotFoundUserInteraction
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotFoundUserInteraction extends AbstractUserInteraction
{
    public function getInputTypes()
    {
        return [];
    }

    public function getOutputType()
    {
        return null;
    }

    public function getOptions(array $input)
    {
        return [];
    }

    public function interaction(array $input, Option $user_selected_option, Bucket $bucket)
    {
        // TODO: Implement interaction() method.
    }

}
