<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */
/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

class ilObjLearningSequenceAccess extends ilObjectAccess
{
    public static bool $using_code = false;

    /**
     * @return array<array<string|bool>>
     */
    public static function _getCommands() : array
    {
        $commands = array(
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
        return $commands;
    }

    public function usingRegistrationCode() : bool
    {
        return self::$using_code;
    }

    public static function isOffline($ref_id)
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

    public function _checkAccess($cmd, $permission, $ref_id, $obj_id, $usr_id = "")
    {
        list($rbacsystem, $il_access, $lng) = $this->getDICDependencies();

        switch ($permission) {
            case 'visible':
                $has_any_administrative_permission = (
                    $rbacsystem->checkAccessOfUser($usr_id, 'write', $ref_id) ||
                    $rbacsystem->checkAccessOfUser($usr_id, 'edit_members', $ref_id) ||
                    $rbacsystem->checkAccessOfUser($usr_id, 'edit_learning_progress', $ref_id)
                );

                $is_offine = $this->isOffline($ref_id);

                if ($is_offine && !$has_any_administrative_permission) {
                    $il_access->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                return true;

            default:
                return $rbacsystem->checkAccessOfUser($usr_id, $permission, $ref_id);
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
