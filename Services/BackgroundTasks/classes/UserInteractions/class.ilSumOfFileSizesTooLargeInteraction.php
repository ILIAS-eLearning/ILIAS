<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Value;

/**
 * Class ilSumOfFileSizesTooLargeInteraction
 *
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilSumOfFileSizesTooLargeInteraction extends AbstractUserInteraction
{

    const OPTION_OK = 'ok';
    const OPTION_SKIP = 'skip';
    /**
     * @var \Monolog\Logger
     */
    private $logger = null;
    /**
     * @var ilLanguage
     */
    protected $lng;


    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->cal();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('background_tasks');
    }


    /**
     * @inheritdoc
     */
    public function getInputTypes()
    {
        return [
            new SingleType(ilCopyDefinition::class),
        ];
    }


    /**
     * @inheritDoc
     */
    public function getOutputType()
    {
        return new SingleType(ilCopyDefinition::class);
    }


    /**
     * @inheritdoc
     */
    public function getRemoveOption()
    {
        return new UserInteractionOption('ok', self::OPTION_OK);
    }


    /**
     * @inheritDoc
     */
    public function interaction(array $input, Option $user_selected_option, Bucket $bucket)
    {
        if ($user_selected_option->getValue() == self::OPTION_OK) {
            // Set state to finished to stop the BackgroundTask and remove it from the popover.
            $bucket->setState(3);
        }

        return $definition = $input[0];
    }


    /**
     * @inheritdoc
     */
    public function getOptions(array $input)
    {
        return array();
    }


    /**
     * @inheritdoc
     */
    public function getMessage(array $input)
    {
        return $message = $this->lng->txt('ui_msg_files_violate_maxsize');
    }


    /**
     * @inheritdoc
     */
    public function canBeSkipped(array $input) : bool
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


    /**
     * @inheritdoc
     */
    public function getSkippedValue(array $input) : Value
    {
        return $input[0];
    }
}
