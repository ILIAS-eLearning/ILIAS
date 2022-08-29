<?php

declare(strict_types=0);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    ): ilLTIConsumerResult {
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
        $ltiResult = $this->getLtiUserResult($a_obj_id, $a_usr_id);

        if ($ltiResult instanceof ilLTIConsumerResult) {
            $object = $this->ensureObject($a_obj_id, $a_obj);
            $ltiMasteryScore = $object->getMasteryScore();

            if ($ltiResult->getResult() >= $ltiMasteryScore) {
                return self::LP_STATUS_COMPLETED_NUM;
            }

            return self::LP_STATUS_IN_PROGRESS_NUM;
        }

        return self::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    public function determinePercentage(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $ltiResult = $this->getLtiUserResult($a_obj_id, $a_usr_id);

        if ($ltiResult instanceof ilLTIConsumerResult) {
            return $ltiResult->getResult() * 100;
        }

        return 0;
    }
}
