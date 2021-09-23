<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;

/**
 * Class ilMailDeliveryJobUserInteraction
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailDeliveryJobUserInteraction extends AbstractUserInteraction
{
    public const OPTION_CANCEL = 'cancel';

    /**
     * @inheritdoc
     */
    public function getOptions(array $input) : array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getRemoveOption() : UserInteractionOption
    {
        return new UserInteractionOption('remove', self::OPTION_CANCEL);
    }

    /**
     * @inheritdoc
     */
    public function getInputTypes() : array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getOutputType() : SingleType
    {
        return new SingleType(StringValue::class);
    }

    /**
     * @inheritdoc
     */
    public function interaction(array $input, ILIAS\BackgroundTasks\Task\UserInteraction\Option $user_selected_option, ILIAS\BackgroundTasks\Bucket $bucket) : array
    {
        return $input;
    }

    /**
     * @inheritDoc
     */
    public function getMessage(array $input) : string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function canBeSkipped(array $input) : bool
    {
        return true;
    }
}
