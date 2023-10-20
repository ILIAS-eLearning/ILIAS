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

namespace ILIAS\User\Profile;

class ilUserProfileDefaultFields
{
    private array $default_profile_fields = [
        'username' => [
            'input' => 'login',
            'maxlength' => 190,
            'size' => 190,
            'method' => 'getLogin',
            'course_export_fix_value' => 1,
            'group_export_fix_value' => 1,
            'changeable_hide' => true,
            'required_hide' => true,
            'group' => 'personal_data'
        ],
        'password' => [
            'input' => 'password',
            'required_hide' => true,
            'visib_reg_hide' => true,
            'visib_lua_fix_value' => 0,
            'course_export_hide' => true,
            'export_hide' => false,
            'group_export_hide' => true,
            'lists_hide' => true,
            'group' => 'personal_data'
        ],
        'firstname' => [
            'input' => 'text',
            'maxlength' => 128,
            'size' => 40,
            'method' => 'getFirstname',
            'required_fix_value' => 1,
            'visib_reg_fix_value' => 1,
            'visib_lua_fix_value' => 1,
            'course_export_fix_value' => 1,
            'group_export_fix_value' => 1,
            'group' => 'personal_data'
        ],
        'lastname' => [
            'input' => 'text',
            'maxlength' => 128,
            'size' => 40,
            'method' => 'getLastname',
            'required_fix_value' => 1,
            'visib_reg_fix_value' => 1,
            'visib_lua_fix_value' => 1,
            'course_export_fix_value' => 1,
            'group_export_fix_value' => 1,
            'group' => 'personal_data'
        ],
        'title' => [
            'input' => 'text',
            'lang_var' => 'person_title',
            'maxlength' => 32,
            'size' => 40,
            'method' => 'getUTitle',
            'group' => 'personal_data'
        ],
        'birthday' => [
            'input' => 'birthday',
            'lang_var' => 'birthday',
            'maxlength' => 32,
            'size' => 40,
            'method' => 'getBirthday',
            'group' => 'personal_data'
        ],
        'gender' => [
            'input' => 'radio',
            'values' => ['n' => 'salutation_n', 'f' => 'salutation_f', 'm' => 'salutation_m'],
            'method' => 'getGender',
            'group' => 'personal_data'
        ],
        'upload' => [
            'input' => 'picture',
            'required_hide' => true,
            'visib_reg_hide' => true,
            'course_export_hide' => true,
            'group_export_hide' => true,
            'lists_hide' => true,
            'lang_var' => 'personal_picture',
            'group' => 'personal_data'
        ],
        'roles' => [
            'input' => 'roles',
            'changeable_hide' => true,
            'required_hide' => true,
            'visib_reg_hide' => true,
            'export_hide' => true,
            'course_export_hide' => true,
            'group_export_hide' => true,
            'lists_hide' => true,
            'group' => 'personal_data'
        ],
        'interests_general' => [
            'input' => 'multitext',
            'maxlength' => 40,
            'size' => 40,
            'method' => 'getGeneralInterests',
            'course_export_hide' => true,
            'group_export_hide' => true,
            'lists_hide' => true,
            'group' => 'interests'
        ],
        'interests_help_offered' => [
            'input' => 'multitext',
            'maxlength' => 40,
            'size' => 40,
            'method' => 'getOfferingHelp',
            'course_export_hide' => true,
            'group_export_hide' => true,
            'lists_hide' => true,
            'group' => 'interests'
        ],
        'interests_help_looking' => [
            'input' => 'multitext',
            'maxlength' => 40,
            'size' => 40,
            'method' => 'getLookingForHelp',
            'course_export_hide' => true,
            'group_export_hide' => true,
            'lists_hide' => true,
            'group' => 'interests'
        ],
        'org_units' => [
            'input' => 'noneditable',
            'lang_var' => 'objs_orgu',
            'required_hide' => true,
            'visib_reg_hide' => true,
            'course_export_hide' => false,
            'group_export_hide' => false,
            'export_hide' => true,
            'changeable_hide' => true,
            'changeable_fix_value' => 0,
            'changeable_lua_hide' => true,
            'changeable_lua_fix_value' => 0,
            'method' => 'getOrgUnitsRepresentation',
            'group' => 'contact_data'
        ],
        'institution' => [
            'input' => 'text',
            'maxlength' => 80,
            'size' => 40,
            'method' => 'getInstitution',
            'group' => 'contact_data'
        ],
        'department' => [
            'input' => 'text',
            'maxlength' => 80,
            'size' => 40,
            'method' => 'getDepartment',
            'group' => 'contact_data'
        ],
        'street' => [
            'input' => 'text',
            'maxlength' => 40,
            'size' => 40,
            'method' => 'getStreet',
            'group' => 'contact_data'
        ],
        'zipcode' => [
            'input' => 'text',
            'maxlength' => 10,
            'size' => 10,
            'method' => 'getZipcode',
            'group' => 'contact_data'
        ],
        'city' => [
            'input' => 'text',
            'maxlength' => 40,
            'size' => 40,
            'method' => 'getCity',
            'group' => 'contact_data'
        ],
        'country' => [
            'input' => 'text',
            'maxlength' => 40,
            'size' => 40,
            'method' => 'getCountry',
            'group' => 'contact_data'
        ],
        'sel_country' => [
            'input' => 'sel_country',
            'method' => 'getSelectedCountry',
            'group' => 'contact_data'
        ],
        'phone_office' => [
            'input' => 'text',
            'maxlength' => 40,
            'size' => 40,
            'method' => 'getPhoneOffice',
            'group' => 'contact_data'
        ],
        'phone_home' => [
            'input' => 'text',
            'maxlength' => 40,
            'size' => 40,
            'method' => 'getPhoneHome',
            'group' => 'contact_data'
        ],
        'phone_mobile' => [
            'input' => 'text',
            'maxlength' => 40,
            'size' => 40,
            'method' => 'getPhoneMobile',
            'group' => 'contact_data'
        ],
        'fax' => [
            'input' => 'text',
            'maxlength' => 40,
            'size' => 40,
            'method' => 'getFax',
            'group' => 'contact_data'
        ],
        'email' => [
            'input' => 'email',
            'maxlength' => 128,
            'size' => 40,
            'method' => 'getEmail',
            'group' => 'contact_data'
        ],
        'second_email' => [
            'input' => 'second_email',
            'maxlength' => 40,
            'size' => 40,
            'method' => 'getSecondEmail',
            'group' => 'contact_data',
            'change_listeners' => [
                ilMailUserFieldChangeListener::class,
            ]
        ],
        'hobby' => [
            'input' => 'textarea',
            'rows' => 3,
            'cols' => 45,
            'method' => 'getHobby',
            'lists_hide' => true,
            'group' => 'contact_data'
        ],
        'referral_comment' => [
            'input' => 'textarea',
            'rows' => 3,
            'cols' => 45,
            'method' => 'getComment',
            'course_export_hide' => true,
            'group_export_hide' => true,
            'lists_hide' => true,
            'group' => 'contact_data'
        ],
        'matriculation' => [
            'input' => 'text',
            'maxlength' => 40,
            'size' => 40,
            'method' => 'getMatriculation',
            'group' => 'other'
        ],
        'language' => [
            'input' => 'language',
            'method' => 'getLanguage',
            'required_hide' => true,
            'visib_reg_hide' => true,
            'course_export_hide' => true,
            'group_export_hide' => true,
            'group' => 'settings'
        ],
        'skin_style' => [
            'input' => 'skinstyle',
            'required_hide' => true,
            'visib_reg_hide' => true,
            'course_export_hide' => true,
            'group_export_hide' => true,
            'group' => 'settings'
        ],
        'hits_per_page' => [
            'input' => 'hitsperpage',
            'default' => 10,
            'options' => [
                10 => 10, 15 => 15, 20 => 20, 30 => 30, 40 => 40,
                50 => 50, 100 => 100, 9999 => 9999
            ],
            'required_hide' => true,
            'visib_reg_hide' => true,
            'course_export_hide' => true,
            'group_export_hide' => true,
            'group' => 'settings'
        ],
        'hide_own_online_status' => [
            'input' => 'selection',
            'lang_var' => 'awrn_user_show',
            'required_hide' => true,
            'visib_reg_hide' => true,
            'course_export_hide' => true,
            'group_export_hide' => true,
            'group' => 'settings',
            'default' => 'y',
            'options' => [
                'y' => 'user_awrn_hide',
                'n' => 'user_awrn_show'
            ]
        ],
        'bs_allow_to_contact_me' => [
            'input' => 'selection',
            'lang_var' => 'buddy_allow_to_contact_me',
            'required_hide' => true,
            'visib_reg_hide' => true,
            'course_export_hide' => true,
            'group_export_hide' => true,
            'group' => 'settings',
            'default' => 'y',
            'options' => [
                'n' => 'buddy_allow_to_contact_me_no',
                'y' => 'buddy_allow_to_contact_me_yes'
            ]
        ],
        'chat_osc_accept_msg' => [
            'input' => 'selection',
            'lang_var' => 'chat_osc_accept_msg',
            'required_hide' => true,
            'visib_reg_hide' => true,
            'course_export_hide' => true,
            'group_export_hide' => true,
            'group' => 'settings',
            'default' => 'y',
            'options' => [
                'n' => 'chat_osc_accepts_messages_no',
                'y' => 'chat_osc_accepts_messages_yes'
            ]
        ],
        'chat_broadcast_typing' => [
            'input' => 'selection',
            'lang_var' => 'chat_broadcast_typing',
            'required_hide' => true,
            'visib_reg_hide' => true,
            'course_export_hide' => true,
            'group_export_hide' => true,
            'group' => 'settings',
            'default' => 'y',
            'options' => [
                'n' => 'chat_no_use_typing_broadcast',
                'y' => 'chat_use_typing_broadcast'
            ]
        ],
        'preferences' => [
            'visible_fix_value' => 1,
            'changeable_fix_value' => 1,
            'required_hide' => true,
            'visib_reg_hide' => true,
            'course_export_hide' => true,
            'group_export_hide' => true,
            'group' => 'preferences'],
        'mail_incoming_mail' => [
            'input' => 'selection',
            'default' => 'y',
            'options' => [
                \ilMailOptions::INCOMING_LOCAL => 'mail_incoming_local',
                \ilMailOptions::INCOMING_EMAIL => 'mail_incoming_smtp',
                \ilMailOptions::INCOMING_BOTH => 'mail_incoming_both'],
            'required_hide' => true,
            'visib_reg_hide' => true,
            'course_export_hide' => true,
            'group_export_hide' => true,
            'export_hide' => true,
            'search_hide' => true,
            'group' => 'settings'
        ]
    ];

    public function getDefaultProfileFields(): array
    {
        return $this->default_profile_fields;
    }
}
