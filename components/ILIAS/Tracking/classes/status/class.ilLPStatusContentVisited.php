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

class ilLPStatusContentVisited extends ilLPStatus
{
    protected const string LNG_TEXT = 'trac_mode_content_visited';
    protected const string LNG_TEXT_INFO = 'trac_mode_content_visited_info';
    protected ilLanguage $lng;

    public static function _getCompleted(int $a_obj_id): array
    {
        $userIds = [];
        $allReadEvents = \ilChangeEvent::_lookupReadEvents($a_obj_id);
        foreach ($allReadEvents as $event) {
            $userIds[] = $event['usr_id'];
        }
        return $userIds;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        if (
            in_array($this->ilObjDataCache->lookupType($a_obj_id), ['file', 'copa', 'htlm']) &&
            \ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)
        ) {
            $status = self::LP_STATUS_COMPLETED_NUM;
        }
        return $status;
    }

    public function init(
        Container $DIC
    ): void {
        $this->lng = $DIC->language();
    }

    public function getLPStatusId(): string
    {
        return (string) ilLPObjSettings::LP_MODE_CONTENT_VISITED;
    }

    public function getLabel(): string
    {
        return $this->lng->txt(self::LNG_TEXT);
    }

    public function getInfo(): string
    {
        return $this->lng->txt(self::LNG_TEXT_INFO);
    }
}
