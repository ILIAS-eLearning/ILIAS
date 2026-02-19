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

declare(strict_types=1);

/**
 * Class ilLPStatusLtiOutcome
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 */
class ilLPStatusLtiOutcome extends ilLPStatus
{
    private static array $userResultCache = array();

    private function getLtiUserResult(
        int $objId,
        int $usrId
    ): ?ilLTIConsumerResult {
        if (!isset(self::$userResultCache[$objId])) {
            self::$userResultCache[$objId] = array();
        }

        if (!isset(self::$userResultCache[$objId][$usrId])) {
            $ltiUserResult = ilLTIConsumerResult::getByKeys($objId, $usrId);
            self::$userResultCache[$objId][$usrId] = $ltiUserResult;
        }
        return self::$userResultCache[$objId][$usrId];
    }

    private function ensureObject(int $objId, $object): ilObjLTIConsumer
    {
        if (!($object instanceof ilObjLTIConsumer)) {
            $object = ilObjectFactory::getInstanceByObjId($objId);
        }
        return $object;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ): int {
        global $DIC;
        $logger = $DIC->logger()->root();
        $ltiResult = $this->getLtiUserResult($a_obj_id, $a_usr_id);

        if ($ltiResult instanceof ilLTIConsumerResult) {
            $object = $this->ensureObject($a_obj_id, $a_obj);
            $ltiMasteryScore = $object->getMasteryScore();
            $logger->info("Getting LTI result for user $a_usr_id: " . $ltiResult->getResult());

            if ($ltiResult->getResult() === 0 || is_null($ltiResult->getResult()) && $ltiResult->isAttended()) {
                return self::LP_STATUS_FAILED_NUM;
            } elseif (is_null($ltiResult->getResult()) && !$ltiResult->isAttended()) {
                return self::LP_STATUS_IN_PROGRESS_NUM;
            } elseif ($ltiResult->getResult() >= $ltiMasteryScore) {
                return self::LP_STATUS_COMPLETED_NUM;
            } else {
                return self::LP_STATUS_FAILED_NUM;
            }
        } else {
            $logger->info("No LTI result for user $a_usr_id");
            return self::LP_STATUS_NOT_ATTEMPTED_NUM;
        }
    }

    public function determinePercentage(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $ltiResult = $this->getLtiUserResult($a_obj_id, $a_usr_id);

        if ($ltiResult instanceof ilLTIConsumerResult) {
            return (int) $ltiResult->getResult() * 100;
        }

        return 0;
    }
}
