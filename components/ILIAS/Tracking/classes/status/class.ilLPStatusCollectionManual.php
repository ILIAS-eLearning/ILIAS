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

class ilLPStatusCollectionManual extends ilLPStatus
{
    protected const string LNG_TEXT = 'trac_mode_collection_manual';
    protected const string LNG_TEXT_INFO = 'trac_mode_collection_manual_info';
    protected ilLanguage $lng;

    public static function _getInProgress(int $a_obj_id): array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        // find any completed item
        $users = [];
        if (isset($status_info['completed'])) {
            foreach ($status_info['completed'] as $in_progress) {
                $users = array_merge($users, $in_progress);
            }
            $users = array_unique($users);
        }
        // remove all users which have completed ALL items
        return array_diff($users, ilLPStatusWrapper::_getCompleted($a_obj_id));
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $counter = 0;
        $users = [];
        foreach ($status_info['items'] as $item_id) {
            $tmp_users = $status_info['completed'][$item_id];

            if (!$counter++) {
                $users = $tmp_users;
            } else {
                $users = array_intersect($users, $tmp_users);
            }
        }
        return array_unique($users);
    }

    public static function _getStatusInfo(int $a_obj_id): array
    {
        $status_info = [];
        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            // @todo check if obj_id can be removed
            $status_info["items"] = $collection->getItems($a_obj_id);
            foreach ($status_info["items"] as $item_id) {
                $status_info["completed"][$item_id] = [];
            }
            $ref_ids = ilObject::_getAllReferences($a_obj_id);
            $ref_id = end($ref_ids);
            $possible_items = $collection->getPossibleItems($ref_id);
            $chapter_ids = array_intersect(
                array_keys($possible_items),
                $status_info["items"]
            );
            // fix order (adapt from possible items)
            $status_info["items"] = $chapter_ids;
            if ($chapter_ids) {
                $status = self::_getObjectStatus($a_obj_id);

                foreach ($chapter_ids as $item_id) {
                    $status_info["item_titles"][$item_id] = $possible_items[$item_id]["title"];

                    if (isset($status[$item_id])) {
                        foreach ($status[$item_id] as $user_id => $user_status) {
                            if ($user_status) {
                                $status_info["completed"][$item_id][] = $user_id;
                            }
                        }
                    }
                }
            }
        }
        return $status_info;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $info = self::_getStatusInfo($a_obj_id);
        if (isset($info["completed"])) {
            $completed = true;
            $in_progress = false;
            foreach ($info["completed"] as $user_ids) {
                // has completed at least 1 item
                if (in_array($a_usr_id, $user_ids)) {
                    $in_progress = true;
                } // must have completed all items to complete collection
                else {
                    $completed = false;
                }
            }
            if ($completed) {
                return self::LP_STATUS_COMPLETED_NUM;
            }
            if ($in_progress) {
                return self::LP_STATUS_IN_PROGRESS_NUM;
            }
        }

        return self::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    public static function _getObjectStatus(
        $a_obj_id,
        $a_user_id = null
    ): array {
        global $DIC;
        $res = [];
        $collection = (new TrackingDBFactory($DIC->database()))->lpCollectionManual()->repository()->readEntriesOfObject($a_obj_id);
        foreach ($collection as $entry) {
            if (!$a_user_id) {
                $res[$entry->getSubitemId()][$entry->getUserId()] = (int) $entry->isCompleted();
            } else {
                $res[$entry->getSubitemId()] = [(int) $entry->isCompleted(), $entry->getLastChanged()];
            }
        }
        return $res;
    }

    public static function _setObjectStatus(
        int $a_obj_id,
        int $a_user_id,
        ?array $a_completed = null
    ): void {
        global $DIC;
        $a_completed = is_null($a_completed) ? [] : $a_completed;
        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        $db_factory = (new TrackingDBFactory($DIC->database()))->lpCollectionManual();
        if ($collection) {
            $existing = self::_getObjectStatus($a_obj_id, $a_user_id);
            foreach ($collection->getItems() as $item_id) {
                // value changed
                $completed = in_array($item_id, $a_completed);
                if (
                    isset($existing[$item_id]) &&
                    (!$existing[$item_id][0] && $completed) ||
                    ($existing[$item_id][0] && !$completed)
                ) {
                    $entry = $db_factory->repository()->readEntryForUserOfSubitemOfObject(
                        $a_obj_id,
                        $a_user_id,
                        $item_id
                    )
                        ->withCompletedStatus($completed)
                        ->withLastChanged(time());
                    $db_factory->repository()->write($entry);
                } elseif ($completed) {
                    $entry = $db_factory->element()->lpCollectionManualEntry()
                        ->withObjectId($a_obj_id)
                        ->withUserId($a_user_id)
                        ->withSubitemId($item_id)
                        ->withCompletedStatus($completed)
                        ->withLastChanged(time());
                    $db_factory->repository()->write($entry);
                }
            }
        }
        ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id);
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
