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
 
class ilObjLearningSequenceAccess extends ilObjectAccess
{
    public static bool $using_code = false;

    /**
     * @return array<array<string|bool>>
     */
    public static function _getCommands() : array
    {
        return array(
            [
                'cmd' => ilObjLearningSequenceGUI::CMD_VIEW,
                'permission' => 'read',
                'lang_var' => 'show',
                'default' => true
            ],
            [
                'cmd' => ilObjLearningSequenceGUI::CMD_LEARNER_VIEW,
                'permission' => 'read',
                'lang_var' => 'show',
            ],
            [
                'cmd' => ilObjLearningSequenceGUI::CMD_CONTENT,
                'permission' => 'write',
                'lang_var' => 'edit_content'
            ],
            [
                'cmd' => ilObjLearningSequenceGUI::CMD_SETTINGS,
                'permission' => 'write',
                'lang_var' => 'settings'
            ],
            [
                'cmd' => ilObjLearningSequenceGUI::CMD_UNPARTICIPATE,
                'permission' => 'unparticipate',
                'lang_var' => 'unparticipate'
            ]
        );
    }

    public function usingRegistrationCode() : bool
    {
        return self::$using_code;
    }

    public static function isOffline(int $ref_id) : bool
    {
        $obj = ilObjectFactory::getInstanceByRefId($ref_id);
        $act = $obj->getLSActivation();
        $online = $act->getIsOnline();

        if (!$online
            && ($act->getActivationStart() !== null ||
                $act->getActivationEnd() !== null)
        ) {
            $ts_now = time();
            $activation_start = $act->getActivationStart();
            if ($activation_start !== null) {
                $after_activation_start = $ts_now >= $activation_start->getTimestamp();
            } else {
                $after_activation_start = true;
            }
            $activation_end = $act->getActivationEnd();
            if ($activation_end !== null) {
                $before_activation_end = $ts_now <= $activation_end->getTimestamp();
            } else {
                $before_activation_end = true;
            }

            $online = ($after_activation_start && $before_activation_end);
        }

        if ($act->getEffectiveOnlineStatus() === false && $online === true) {
            $obj->setEffectiveOnlineStatus(true);
            $obj->announceLSOOnline();
        }
        if ($act->getEffectiveOnlineStatus() === true && $online === false) {
            $obj->setEffectiveOnlineStatus(false);
            $obj->announceLSOOffline();
        }


        return !$online;
    }

    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null) : bool
    {
        list($rbacsystem, $il_access, $lng) = $this->getDICDependencies();

        switch ($permission) {
            case 'visible':
                $has_any_administrative_permission = false;
                if (!is_null($user_id)) {
                    $has_any_administrative_permission = (
                        $rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id) ||
                        $rbacsystem->checkAccessOfUser($user_id, 'edit_members', $ref_id) ||
                        $rbacsystem->checkAccessOfUser($user_id, 'edit_learning_progress', $ref_id)
                    );
                }

                $is_offine = $this->isOffline($ref_id);

                if ($is_offine && !$has_any_administrative_permission) {
                    $il_access->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                return true;

            default:
                if (is_null($user_id)) {
                    return false;
                }
                return $rbacsystem->checkAccessOfUser($user_id, $permission, $ref_id);
        }
    }

    /**
     * @return array<ilRbacSystem|ilAccess|ilLanguage>
     */
    protected function getDICDependencies() : array
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $il_access = $DIC['ilAccess'];
        $lng = $DIC['lng'];

        return [
            $rbacsystem,
            $il_access,
            $lng
        ];
    }
}
