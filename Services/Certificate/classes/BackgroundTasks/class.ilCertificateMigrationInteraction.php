<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;

/**
 * Class ilCertificateMigrationInteraction
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
class ilCertificateMigrationInteraction extends AbstractUserInteraction
{
    const OPTION_GOTO_LIST = 'listCertificates';
    const OPTION_CANCEL = 'cancel';

    /**
     * @return array|\ILIAS\BackgroundTasks\Types\Type[]
     */
    public function getInputTypes() : array
    {
        return [
            new SingleType(IntegerValue::class),
            new SingleType(IntegerValue::class),
        ];
    }

    /**
     * @return SingleType|\ILIAS\BackgroundTasks\Types\Type
     */
    public function getOutputType() : SingleType
    {
        return new SingleType(StringValue::class);
    }

    /**
     * @inheritDoc
     */
    public function getRemoveOption()
    {
        return new UserInteractionOption('remove', self::OPTION_CANCEL);
    }

    /**
     * @param array $input
     * @return array|\ILIAS\BackgroundTasks\Task\UserInteraction\Option[]
     */
    public function getOptions(array $input) : array
    {
        return [
            new UserInteractionOption('my_certificates', self::OPTION_GOTO_LIST),
        ];
    }

    /**
     * @param array $input
     * @param \ILIAS\BackgroundTasks\Task\UserInteraction\Option $user_selected_option
     * @param \ILIAS\BackgroundTasks\Bucket $bucket
     * @return array|\ILIAS\BackgroundTasks\Value
     */
    public function interaction(array $input, \ILIAS\BackgroundTasks\Task\UserInteraction\Option $user_selected_option, \ILIAS\BackgroundTasks\Bucket $bucket)
    {
        global $DIC;

        $progress = $input[0]->getValue();
        $user_id = $input[1]->getValue();
        $logger = $DIC->logger()->cert();

        $logger->debug('User interaction certificate migration for user with id: ' . $user_id);
        $logger->debug('User interaction certificate migration State: ' . $bucket->getState());
        if ($user_selected_option->getValue() != self::OPTION_GOTO_LIST) {
            $logger->info('User interaction certificate migration canceled for user with id: ' . $user_id);
            return $input;
        }

        $DIC->ctrl()->redirectByClass([
            'ilPersonalDesktopGUI',
            'ilAchievementsGUI',
            'ilUserCertificateGUI'
        ], 'listCertificates');

        return $input;
    }
}
