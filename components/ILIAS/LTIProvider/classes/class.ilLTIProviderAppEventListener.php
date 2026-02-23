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

use ceLTIc\LTI\Enum\ServiceAction;
use ceLTIc\LTI\Outcome;
use ceLTIc\LTI\ResourceLink;
use ceLTIc\LTI\UserResult;

class ilLTIProviderAppEventListener
{
    private ?ilLogger $logger = null;
    private ?ilLTIDataConnector $connector = null;
    private static ?ilLTIProviderAppEventListener $instance = null;


    protected function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->root();
        $this->connector = new ilLTIDataConnector();
    }

    public static function handleCronUpdate(ilDateTime $since): bool
    {
        $listener = self::getInstance();
        $listener->doCronUpdate($since);
        return true;
    }

    protected static function getInstance(): ilLTIProviderAppEventListener
    {
        if (!self::$instance instanceof ilLTIProviderAppEventListener) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function doCronUpdate(ilDateTime $since): void
    {
        $this->logger->info('Starting cron update for lti outcome service');

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
                list($resource_id, $resource_ref_id) = explode('__', $resource_info);
                $this->logger->info('Found resource: ' . $resource_info . " for user: " . $usr_id . " resource_id: " . $resource_id . " resource_ref_id: " . $resource_ref_id);

                // lookup lp status
                $status = ilLPStatus::_lookupStatus(
                    ilObject::_lookupObjId((int) $resource_ref_id),
                    $usr_id
                );
                $percentage = ilLPStatus::_lookupPercentage(
                    ilObject::_lookupObjId((int) $resource_ref_id),
                    $usr_id
                );
                $percentage = $this->definePercentageByObjectId($status, $resource_ref_id, $percentage);
                $this->tryOutcomeService((int) $resource_id, $ext_account, $status, $percentage);
            }
        }
    }

    protected function definePercentageByObjectId(int|null $status, string $obj_id, int|null $percentage): int
    {
        global $DIC;
        $logger = $DIC->logger()->root();
        $logger->debug('definePercentageByObjectId');
        $indentifier = ilObjectFactory::getInstanceByRefId((int) $obj_id)->getType();
        $logger->info('Object type: ' . $indentifier . " for object id: " . $obj_id);
        if (in_array($indentifier, ['crs', 'grp'])) {
            if ($status == ilLPStatus::LP_STATUS_COMPLETED_NUM || $status == ilLPStatus::LP_STATUS_FAILED_NUM) {
                $percentage = 100;
            }
        }
        return $percentage;
    }

    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        global $DIC;
        $logger = $DIC->logger()->root();
        $logger->info('Handling event: ' . $a_event . ' from ' . $a_component);
        $logger->info("public static function handleEvent --- ilLTIProviderAppEventListener " . $a_event . ' from ' . $a_component);
        if ($a_component == 'components/ILIAS/Tracking') {
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

    protected function isLTIAuthMode(string $auth_mode): bool
    {
        return strpos($auth_mode, 'lti_') === 0;
    }

    protected function handleUpdateStatus(int $a_obj_id, int $a_usr_id, int $a_status, int $a_percentage): void
    {
        global $DIC;
        $logger = $DIC->logger()->root();
        $logger->info('Handle update status');
        $auth_mode = ilObjUser::_lookupAuthMode($a_usr_id);
        if (!$this->isLTIAuthMode($auth_mode)) {
            $this->logger->info('Ignoring update for non-LTI-user.');
            return;
        }
        $ext_account = ilObjUser::_lookupExternalAccount($a_usr_id);
        list($lti, $consumer) = explode('_', $auth_mode);

        // iterate through all references
        $refs = ilObject::_getAllReferences($a_obj_id);
        $this->logger->info('Refs for : ' . $a_obj_id . ': ' . count($refs));
        foreach ((array) $refs as $ref_id) {
            $resources = $this->connector->lookupResourcesForUserObjectRelation(
                $ref_id,
                $ext_account,
                (int) $consumer
            );

            $this->logger->info('Resources for update:');
            $this->logger->info("resources: " . json_encode($resources));

            foreach ($resources as $resource) {
                $this->tryOutcomeService((int) $resource, $ext_account, $a_status, $a_percentage);
            }
        }
    }

    protected function tryOutcomeService(int $resource, string $ext_account, int $a_status, int $a_percentage): void
    {
        $resource_link = ResourceLink::fromRecordId($resource, $this->connector);
        if (!$resource_link->hasOutcomesService()) {
            $this->logger->info('No outcome service available for resource id: ' . $resource);
            return;
        }
        $this->logger->info('Trying outcome service with status ' . $a_status . ' and percentage ' . $a_percentage);
        $user = UserResult::fromResourceLink($resource_link, $ext_account);

        if (!$a_percentage && $a_status != ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM) {
            $score = 0;
        } else {
            if ($a_status == ilLPStatus::LP_STATUS_COMPLETED_NUM || $a_status == ilLPStatus::LP_STATUS_FAILED_NUM) {
                $score = $a_percentage / 100;
            } elseif (
                $a_status == ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM
            ) {
                $score = null;
            } else {
                $score = 0;
            }
        }

        $this->logger->info('Sending score: ' . (string) $score);

        $outcome = new Outcome((string) $score);

        $status = $resource_link->doOutcomesService(
            ServiceAction::Write,
            $outcome,
            $user
        );
        $this->logger->info('Outcome service request status: ' . $status);
    }
}
