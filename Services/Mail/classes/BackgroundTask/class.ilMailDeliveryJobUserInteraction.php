<?php

declare(strict_types=1);

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

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

/**
 * Class ilMailDeliveryJobUserInteraction
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailDeliveryJobUserInteraction extends AbstractUserInteraction
{
    public const OPTION_CANCEL = 'cancel';

    public function getOptions(array $input): array
    {
        return [];
    }

    public function getRemoveOption(): Option
    {
        return new UserInteractionOption('remove', self::OPTION_CANCEL);
    }

    public function getInputTypes(): array
    {
        return [];
    }

    public function getOutputType(): Type
    {
        return new SingleType(StringValue::class);
    }

    public function interaction(
        array $input,
        ILIAS\BackgroundTasks\Task\UserInteraction\Option $user_selected_option,
        ILIAS\BackgroundTasks\Bucket $bucket
    ): Value {
        return $input[0];
    }

    public function getMessage(array $input): string
    {
        return '';
    }

    public function canBeSkipped(array $input): bool
    {
        return true;
    }
}
