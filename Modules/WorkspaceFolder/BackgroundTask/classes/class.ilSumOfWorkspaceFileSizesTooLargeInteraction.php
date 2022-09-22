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

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

class ilSumOfWorkspaceFileSizesTooLargeInteraction extends AbstractUserInteraction
{
    public const OPTION_OK = 'ok';
    public const OPTION_SKIP = 'skip';
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('background_tasks');
    }

    public function getInputTypes(): array
    {
        return [
            new SingleType(ilWorkspaceCopyDefinition::class),
        ];
    }

    public function getOutputType(): Type
    {
        return new SingleType(ilWorkspaceCopyDefinition::class);
    }

    public function getRemoveOption(): Option
    {
        return new UserInteractionOption('ok', self::OPTION_OK);
    }

    public function interaction(
        array $input,
        Option $user_selected_option,
        Bucket $bucket
    ): Value {
        if ($user_selected_option->getValue() == self::OPTION_OK) {
            // Set state to finished to stop the BackgroundTask and remove it from the popover.
            $bucket->setState(3);
        }

        return $input[0];
    }

    public function getOptions(array $input): array
    {
        return array();
    }

    public function getMessage(array $input): string
    {
        return $this->lng->txt('ui_msg_files_violate_maxsize');
    }

    public function canBeSkipped(array $input): bool
    {
        $copy_definition = $input[0];
        if ($copy_definition->getAdheresToLimit()->getValue()) {
            // skip the user interaction if the adherence to the global limit for the sum of file sizes
            // hasn't been violated (as this interaction is used as an error message and mustn't be
            // shown when everything is fine))

            return true;
        } else {
            return false;
        }
    }

    public function getSkippedValue(array $input): Value
    {
        return $input[0];
    }

    public function isFinal(): bool
    {
        return false;
    }

}
