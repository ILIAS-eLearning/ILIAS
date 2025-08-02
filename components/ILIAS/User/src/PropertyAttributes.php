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

namespace ILIAS\User;

enum PropertyAttributes: string
{
    private const string LANG_VAR_VISIBLE_IN_REGISTRATION = 'header_visible_registration';
    private const string LANG_VAR_HIDDEN_FROM_USER = 'user_visible_in_profile';
    private const string LANG_VAR_VISIBLE_IN_LOCAL_USER_ADMINISTRATION = 'usr_settings_visib_lua';
    private const string LANG_VAR_VISIBLE_IN_COURSES = 'course_export';
    private const string LANG_VAR_VISIBLE_IN_GROUPS = 'group_export';
    private const string LANG_VAR_VISIBLE_IN_STUDY_PROGRAMMES = 'prg_export';
    private const string LANG_VAR_UNCHANGEABLE_BY_USER = 'changeable';
    private const string LANG_VAR_CHANGEABLE_IN_LOCAL_USER_ADMINISTRATION = 'usr_settings_changeable_lua';
    private const string LANG_VAR_REQUIRED = 'required_field';
    private const string LANG_VAR_EXPORT = 'export';
    private const string LANG_VAR_SEARCHABLE = 'header_searchable';
    private const string LANG_VAR_AVAILABLE_IN_CERTIFICATES = 'certificate';

    case VisibleInRegistration = 'usr_settings_visib_reg';
    case HiddenFromUser = 'usr_settings_hide';
    case VisibleInLocalUserAdministration = 'usr_settings_visib_lua';
    case VisibleInCourses = 'usr_settings_course_export';
    case VisibleInGroups = 'usr_settings_group_export';
    case VisibleInStudyProgrammes = 'usr_settings_prg_export';
    case UnchangeableByUser = 'usr_settings_disable';
    case ChangeableInLocalUserAdministration = 'usr_settings_changeable_lua';
    case Required = 'require';
    case Export = 'usr_settings_export';
    case Searchable = 'search_enabled';
    case AvailableInCertificates = 'certificate';

    public function getLanguageVariable(): string
    {
        return match($this) {
            self::VisibleInRegistration => self::LANG_VAR_VISIBLE_IN_REGISTRATION,
            self::HiddenFromUser => self::LANG_VAR_HIDDEN_FROM_USER,
            self::VisibleInLocalUserAdministration => self::LANG_VAR_VISIBLE_IN_LOCAL_USER_ADMINISTRATION,
            self::VisibleInCourses => self::LANG_VAR_VISIBLE_IN_COURSES,
            self::VisibleInGroups => self::LANG_VAR_VISIBLE_IN_GROUPS,
            self::VisibleInStudyProgrammes => self::LANG_VAR_VISIBLE_IN_STUDY_PROGRAMMES,
            self::UnchangeableByUser => self::LANG_VAR_UNCHANGEABLE_BY_USER,
            self::ChangeableInLocalUserAdministration => self::LANG_VAR_CHANGEABLE_IN_LOCAL_USER_ADMINISTRATION,
            self::Required => self::LANG_VAR_REQUIRED,
            self::Export => self::LANG_VAR_EXPORT,
            self::Searchable => self::LANG_VAR_SEARCHABLE,
            self::AvailableInCertificates => self::LANG_VAR_AVAILABLE_IN_CERTIFICATES
        };
    }

    public function retrieve(
        \ilSetting $settings,
        Property $property
    ): bool {
        return $settings->get("{$this->value}_{$property->getIdentifier()}", '0') === '1';
    }

    public function store(
        \ilSetting $settings,
        Property $property,
        bool $value
    ): void {
        $settings->set("{$this->value}_{$property->getIdentifier()}", $value ? '1' : '0');
    }
}
