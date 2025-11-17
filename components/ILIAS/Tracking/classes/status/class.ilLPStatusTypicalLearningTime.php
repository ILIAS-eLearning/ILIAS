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

use ILIAS\DI\Container;

class ilLPStatusTypicalLearningTime extends ilLPStatus
{
    protected const string LNG_TEXT = 'trac_mode_tlt';
    protected const string LNG_TEXT_INFO = 'trac_mode_tlt_info';
    protected ilLanguage $lng;

    public static function _getInProgress(int $a_obj_id): array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $tlt = $status_info['tlt'];
        $all = ilChangeEvent::_lookupReadEvents($a_obj_id);
        $user_ids = [];
        foreach ($all as $event) {
            if ($event['spent_seconds'] < $tlt) {
                $user_ids[] = (int) $event['usr_id'];
            }
        }
        return $user_ids;
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $tlt = $status_info['tlt'];
        // TODO: move to status info
        $all = ilChangeEvent::_lookupReadEvents($a_obj_id);
        $user_ids = [];
        foreach ($all as $event) {
            if ($event['spent_seconds'] >= $tlt) {
                $user_ids[] = (int) $event['usr_id'];
            }
        }
        return $user_ids;
    }

    public static function _getStatusInfo(int $a_obj_id): array
    {
        global $DIC;
        /** @var ilObjectDataCache $ilObjDataCache */
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $status_info['tlt'] = parent::_getTypicalLearningTime(
            $ilObjDataCache->lookupType($a_obj_id),
            $a_obj_id
        );
        return $status_info;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        if(
            strcmp($this->ilObjDataCache->lookupType($a_obj_id), 'lm') === 0 &&
            ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)
        ) {
            $status = self::LP_STATUS_IN_PROGRESS_NUM;
            $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
            $tlt = $status_info['tlt'];
            $re = ilChangeEvent::_lookupReadEvents(
                $a_obj_id,
                $a_usr_id
            );
            if ($re[0]['spent_seconds'] >= $tlt) {
                $status = self::LP_STATUS_COMPLETED_NUM;
            }
        }
        return $status;
    }

    public function determinePercentage(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $tlt = parent::_getTypicalLearningTime(
            $this->ilObjDataCache->lookupType($a_obj_id),
            $a_obj_id
        );
        $re = ilChangeEvent::_lookupReadEvents($a_obj_id, $a_usr_id);
        $spent = (int) ($re[0]["spent_seconds"] ?? 0);

        if ($tlt > 0) {
            $per = (int) min(100, 100 / $tlt * $spent);
        } else {
            $per = 100;
        }
        return $per;
    }

    public function init(
        Container $DIC
    ): void {
        parent::init($DIC);
        $this->lng = $DIC->language();
    }

    public function getLPStatusId(): string
    {
        return (string) ilLPObjSettings::LP_MODE_TLT;
    }

    public function getLabel(): string
    {
        return $this->lng->txt(self::LNG_TEXT);
    }

    public function getInfo(): string
    {
        return sprintf($this->lng->txt(self::LNG_TEXT_INFO), ilObjUserTracking::_getValidTimeSpan());
    }
}
