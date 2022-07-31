<?php declare(strict_types=1);


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

/**
 * Class ilLTIAppEventListener
 */
class ilLTIAppEventListener implements \ilAppEventListener
{
    private static ?\ilLTIAppEventListener $instance = null;

    private ?\ilLogger $logger = null;

    private ?\ilLTIDataConnector $connector = null;


    /**
     * ilLTIAppEventListener constructor.
     */
    protected function __construct()
    {
        global $DIC;

        $this->logger = ilLoggerFactory::getLogger('ltis');
        $this->connector = new ilLTIDataConnector();
    }

    protected static function getInstance() : \ilLTIAppEventListener
    {
        if (!self::$instance instanceof \ilLTIAppEventListener) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Handle update status
     */
    protected function handleUpdateStatus(int $a_obj_id, int $a_usr_id, int $a_status, int $a_percentage) : void
    {
        $this->logger->debug('Handle update status');
        $auth_mode = ilObjUser::_lookupAuthMode($a_usr_id);
        if (!$this->isLTIAuthMode($auth_mode)) {
            $this->logger->debug('Ignoring update for non-LTI-user.');
            return;
        }
        $ext_account = ilObjUser::_lookupExternalAccount($a_usr_id);
        list($lti, $consumer) = explode('_', $auth_mode);

        // iterate through all references
        $refs = ilObject::_getAllReferences($a_obj_id);
        foreach ((array) $refs as $ref_id) {
            $resources = $this->connector->lookupResourcesForUserObjectRelation(
                $ref_id,
                $ext_account,
                (int) $consumer
            );

            $this->logger->debug('Resources for update:');
            $this->logger->dump($resources, ilLogLevel::DEBUG);

            foreach ($resources as $resource) {
                $this->tryOutcomeService($resource, $ext_account, $a_status, $a_percentage);
            }
        }
    }


    /**
     * @param ilDateTime $since
     * @throws ilDateTimeException
     */
    protected function doCronUpdate(ilDateTime $since) : void
    {
        $this->logger->debug('Starting cron update for lti outcome service');

        $resources = $this->connector->lookupResourcesForAllUsersSinceDate($since);
        foreach ($resources as $consumer_ext_account => $user_resources) {
            list($consumer, $ext_account) = explode('__', $consumer_ext_account, 2);

            $login = ilObjUser::_checkExternalAuthAccount('lti_' . $consumer, $ext_account);
            if (!$login) {
                $this->logger->info('No user found for lti_' . $consumer . ' -> ' . $ext_account);
                continue;
            }
            $usr_id = ilObjUser::_lookupId($login);
            foreach ($user_resources as $resource_info) {
                $this->logger->debug('Found resource: ' . $resource_info);
                list($resource_id, $resource_ref_id) = explode('__', $resource_info);

                // lookup lp status
                $status = ilLPStatus::_lookupStatus(
                    ilObject::_lookupObjId((int) $resource_ref_id),
                    $usr_id
                );
                $percentage = ilLPStatus::_lookupPercentage(
                    ilObject::_lookupObjId((int) $resource_ref_id),
                    $usr_id
                );
                $this->tryOutcomeService($resource_id, $ext_account, $status, $percentage);
            }
        }
    }

    protected function isLTIAuthMode(string $auth_mode) : bool
    {
        return strpos($auth_mode, 'lti_') === 0;
    }


    /**
     * try outcome service
     */
    protected function tryOutcomeService($resource, string $ext_account, int $a_status, int $a_percentage) : void
    {
        $resource_link = \ILIAS\LTI\ToolProvider\ResourceLink::fromRecordId($resource, $this->connector);
        if (!$resource_link->hasOutcomesService()) {
            $this->logger->debug('No outcome service available for resource id: ' . $resource);
            return;
        }
        $this->logger->debug('Trying outcome service with status ' . $a_status . ' and percentage ' . $a_percentage);
        $user = \ILIAS\LTI\ToolProvider\UserResult::fromResourceLink($resource_link, $ext_account);

        if ($a_status == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
            $score = 1;
        } elseif (
            $a_status == ilLPStatus::LP_STATUS_FAILED_NUM ||
            $a_status == ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM
        ) {
            $score = 0;
        } elseif (!$a_percentage) {
            $score = 0;
        } else {
            $score = (int) round($a_percentage / 100);
        }

        $this->logger->debug('Sending score: ' . (string) $score);

        $outcome = new \ILIAS\LTI\ToolProvider\Outcome((string) $score);

        $resource_link->doOutcomesService(
            \ILIAS\LTI\ToolProvider\ResourceLink::EXT_WRITE,
            $outcome,
            $user
        );
    }


    /**
     * @inheritdoc
     */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        $logger = ilLoggerFactory::getLogger('ltis');
        $logger->debug('Handling event: ' . $a_event . ' from ' . $a_component);

        if ($a_component == 'Services/Tracking') {
            if ($a_event == 'updateStatus') {
                $listener = self::getInstance();
                $listener->handleUpdateStatus(
                    $a_parameter['obj_id'],
                    $a_parameter['usr_id'],
                    $a_parameter['status'],
                    $a_parameter['percentage']
                );
            }
        }
    }

    /**
     * @param ilDateTime $since
     * @return bool
     * @throws ilDateTimeException
     */
    public static function handleCronUpdate(ilDateTime $since) : bool
    {
        $listener = self::getInstance();
        $listener->doCronUpdate($since);
        return true;
    }


    public static function handleOutcomeWithoutLP(int $a_obj_id, int $a_usr_id, ?float $a_percentage) : void
    {
        global $DIC;
        $score = 0;
        $logger = ilLoggerFactory::getLogger('ltis');

        $auth_mode = ilObjUser::_lookupAuthMode($a_usr_id);
        if (strpos($auth_mode, 'lti_') === false) {
            $logger->debug('Ignoring outcome for non-LTI-user.');
            return;
        }
        //check if LearningPress enabled
        $olp = ilObjectLP::getInstance($a_obj_id);
        if (ilLPObjSettings::LP_MODE_DEACTIVATED != $olp->getCurrentMode()) {
            $logger->debug('Ignoring outcome if LP is activated.');
            return;
        }

        if ($a_percentage && $a_percentage > 0) {
            $score = round($a_percentage / 100, 4);
        }

        $connector = new ilLTIDataConnector();
        $ext_account = ilObjUser::_lookupExternalAccount($a_usr_id);
        list($lti, $consumer) = explode('_', $auth_mode);

        // iterate through all references
        $refs = ilObject::_getAllReferences($a_obj_id);
        foreach ((array) $refs as $ref_id) {
            $resources = $connector->lookupResourcesForUserObjectRelation(
                $ref_id,
                $ext_account,
                (int) $consumer
            );

            $logger->debug('Resources for update: ' . dump($resources));

            foreach ($resources as $resource) {
                // $this->tryOutcomeService($resource, $ext_account, $a_status, $a_percentage);
                $resource_link = \ILIAS\LTI\ToolProvider\ResourceLink::fromRecordId($resource, $connector);
                if ($resource_link->hasOutcomesService()) {
                    $user = \ILIAS\LTI\ToolProvider\UserResult::fromResourceLink($resource_link, $ext_account);
                    $logger->debug('Sending score: ' . (string) $score);
                    $outcome = new \ILIAS\LTI\ToolProvider\Outcome((string) $score);

                    $resource_link->doOutcomesService(
                        \ILIAS\LTI\ToolProvider\ResourceLink::EXT_WRITE,
                        $outcome,
                        $user
                    );
                }
            }
        }
    }
}
