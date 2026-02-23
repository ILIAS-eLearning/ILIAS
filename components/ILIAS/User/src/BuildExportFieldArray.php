<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

declare(strict_types=1);

namespace ILIAS\User;

use ILIAS\User\Context;
use ILIAS\User\Property;
use ILIAS\User\Profile\Profile;
use ILIAS\User\Settings\SettingsImplementation;
use ILIAS\Language\Language;

trait BuildExportFieldArray
{
    /**
     * @return array<string, string> of exportable fields and there text
     * representation in the language of the current user;
     */
    private function getExportFieldArray(
        Language $lng,
        Profile $profile,
        SettingsImplementation $settings
    ): array {
        return array_reduce(
            array_merge(
                $profile->getVisibleFields(Context::Export),
                $settings->getExportableSettings()
            ),
            function (array $c, Property $v) use ($lng): array {
                $c[$v->getIdentifier()] = $v->getLabel($lng);
                return $c;
            },
            [
                'usr_id' => $lng->txt('usr_id'),
                'login' => $lng->txt('login'),
                'last_login' => $lng->txt('last_login'),
                'last_update' => $lng->txt('last_update'),
                'create_date' => $lng->txt('create_date'),
                'time_limit_unlimited' => $lng->txt('time_limit_unlimited'),
                'time_limit_from' => $lng->txt('time_limit_from'),
                'time_limit_until' => $lng->txt('time_limit_until'),
                'time_limit_message' => $lng->txt('time_limit_message'),
                'active' => $lng->txt('active'),
                'approve_date' => $lng->txt('approve_date'),
                'agree_date' => $lng->txt('agree_date'),
                'auth_mode' => $lng->txt('auth_mode'),
                'ext_account' => $lng->txt('user_ext_account'),
                'feedhash' => $lng->txt('feedhash')
            ]
        );
    }
}
