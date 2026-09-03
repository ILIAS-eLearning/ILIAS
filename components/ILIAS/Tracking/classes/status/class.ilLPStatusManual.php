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
use ILIAS\Tracking\DB\Factory as TrackingDBFactory;
use ILIAS\Tracking\DB\FactoryInterface as TrackingDBFactoryInterface;

class ilLPStatusManual extends ilLPStatus
{
    protected const string LNG_TEXT = 'trac_mode_manual';
    protected const string LNG_TEXT_INFO = 'trac_mode_manual_info';
    protected ilLanguage $lng;

    protected TrackingDBFactoryInterface $tracking_db_factory;

    public function __construct(int $a_obj_id)
    {
        global $DIC;
        parent::__construct($a_obj_id);
        $this->tracking_db_factory = new TrackingDBFactory($DIC->database());
    }

    public static function _getInProgress(int $a_obj_id): array
    {
        $users = ilChangeEvent::lookupUsersInProgress($a_obj_id);
        // Exclude all users with status completed.
        return array_diff($users, ilLPStatusWrapper::_getCompleted($a_obj_id));
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        global $DIC;
        return (new TrackingDBFactory($DIC->database()))->lpMarks()->repository()->readAllEntriesOfObject($a_obj_id)
            ->getSubCollectionOfElementsByCompletedStatus(true)
            ->getSubCollectionOfElementsWithDistinctUsers()
            ->asUserIdArray();
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        if (
            in_array($this->ilObjDataCache->lookupType($a_obj_id), ['lm', 'copa', 'file', 'htlm']) &&
            ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)
        ) {
            $lp_mark = $this->tracking_db_factory->lpMarks()->repository()->readEntryForUserOfObject(
                $a_obj_id,
                $a_usr_id
            );
            $status = $lp_mark->isCompleted() ? self::LP_STATUS_COMPLETED_NUM : self::LP_STATUS_IN_PROGRESS_NUM;
        }
        return $status;
    }

    public static function _lookupFailedForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        return [];
    }

    public function init(
        Container $DIC
    ): void {
        $this->lng = $DIC->language();
    }

    public function getLPStatusId(): string
    {
        return (string) ilLPObjSettings::LP_MODE_MANUAL;
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
