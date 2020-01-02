<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCertificateMigrationReducedInteraction
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilCertificateMigrationReducedInteraction extends ilCertificateMigrationInteraction
{
    /** @var \ilLogger */
    protected $log;

    /**
     * ilCertificateMigrationReducedInteraction constructor.
     * @param ilLogger|null $log
     */
    public function __construct(
        \ilLogger $log = null
    ) {
        global $DIC;

        if (null === $log) {
            $log = $DIC->logger()->cert();
        }
        $this->log = $log;
    }

    /**
     * @param array $input
     * @return array|\ILIAS\BackgroundTasks\Task\UserInteraction\Option[]
     */
    public function getOptions(array $input) : array
    {
        return [];
    }

    /**
     * @param array $input
     * @param \ILIAS\BackgroundTasks\Task\UserInteraction\Option $user_selected_option
     * @param \ILIAS\BackgroundTasks\Bucket $bucket
     * @return array|\ILIAS\BackgroundTasks\Value
     */
    public function interaction(array $input, \ILIAS\BackgroundTasks\Task\UserInteraction\Option $user_selected_option, \ILIAS\BackgroundTasks\Bucket $bucket)
    {
        $progress = $input[0]->getValue();
        $user_id = $input[1]->getValue();

        $this->log->debug('User interaction certificate migration for user with id: ' . $user_id);
        $this->log->debug('User interaction certificate migration State: ' . $bucket->getState());
        $this->log->info('User interaction certificate migration canceled for user with id: ' . $user_id);

        return $input;
    }
}
