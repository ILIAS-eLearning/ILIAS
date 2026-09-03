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

class ilLPStatusPlugin extends ilLPStatus
{
    protected const string LNG_TEXT = 'trac_mode_plugin';
    protected const string LNG_TEXT_INFO = '';
    protected ilLanguage $lng;

    /**
     * @todo refactor return type
     */
    protected static function initPluginObj(int $a_obj_id): ilObjectPlugin|int
    {
        $olp = ilObjectLP::getInstance($a_obj_id);
        return $olp->getPluginInstance();
    }

    public static function _getNotAttempted(int $a_obj_id): array
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                return (array) $plugin->getLPNotAttempted();
            } else {
                // re-use existing data for inactive plugin
                return self::getLPStatusData(
                    $a_obj_id,
                    self::LP_STATUS_NOT_ATTEMPTED_NUM
                );
            }
        }
        return [];
    }

    public static function _getInProgress(int $a_obj_id): array
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                return (array) $plugin->getLPInProgress();
            } else {
                // re-use existing data for inactive plugin
                return self::getLPStatusData(
                    $a_obj_id,
                    self::LP_STATUS_IN_PROGRESS_NUM
                );
            }
        }
        return [];
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                return (array) $plugin->getLPCompleted();
            } else {
                // re-use existing data for inactive plugin
                return self::getLPStatusData(
                    $a_obj_id,
                    self::LP_STATUS_COMPLETED_NUM
                );
            }
        }
        return [];
    }

    public static function _getFailed(int $a_obj_id): array
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                return (array) $plugin->getLPFailed();
            } else {
                // re-use existing data for inactive plugin
                return self::getLPStatusData(
                    $a_obj_id,
                    self::LP_STATUS_FAILED_NUM
                );
            }
        }
        return [];
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                // :TODO: create read_event here to make sure?
                return $plugin->getLPStatusForUser($a_usr_id);
            } else {
                // re-use existing data for inactive plugin
                return self::getLPDataForUser($a_obj_id, $a_usr_id);
            }
        }
        // #11368
        return self::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    public function determinePercentage(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                if (method_exists($plugin, "getPercentageForUser")) {
                    return $plugin->getPercentageForUser($a_usr_id);
                }
            }
            // re-use existing data for inactive plugin
            return self::getPercentageForUser($a_obj_id, $a_usr_id);
        }
        // #11368
        return 0;
    }

    protected static function getLPStatusData(
        int $a_obj_id,
        int $a_status
    ): array {
        global $DIC;
        return (new TrackingDBFactory($DIC->database()))->lpMarks()->repository()->readAllEntriesWithStatusOfObject($a_obj_id, $a_status)
            ->asUserIdArray();
    }

    protected static function getLPDataForUser(
        int $a_obj_id,
        int $a_user_id
    ): int {
        global $DIC;
        $lp_mark = (new TrackingDBFactory($DIC->database()))->lpMarks()->repository()->readEntryForUserOfObject($a_obj_id, $a_user_id);
        return is_null($lp_mark) ? self::LP_STATUS_NOT_ATTEMPTED_NUM : $lp_mark->getStatus();
    }

    protected static function getPercentageForUser(
        int $a_obj_id,
        int $a_user_id
    ): int {
        global $DIC;
        $lp_mark = (new TrackingDBFactory($DIC->database()))->lpMarks()->repository()->readEntryForUserOfObject($a_obj_id, $a_user_id);
        return is_null($lp_mark) ? 0 : $lp_mark->getPercentage();
    }

    public function init(
        Container $DIC
    ): void {
        $this->lng = $DIC->language();
    }

    public function getLPStatusId(): string
    {
        return (string) ilLPObjSettings::LP_MODE_PLUGIN;
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
