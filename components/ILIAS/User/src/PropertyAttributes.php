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
    private const string SETTINGS_ACCESS_PREFIX_CHANGEABLE_BY_USER = 'usr_settings_changeable_by_user';
    private const string SETTINGS_ACCESS_PREFIX_CHANGEABLE_IN_LUA = 'usr_settings_changeable_lua';
    private const string SETTINGS_ACCESS_PREFIX_EXPORT = 'usr_settings_export';

    case VisibleInRegistration = 'header_visible_registration';
    case VisibleToUser = 'user_visible_in_profile';
    case VisibleInLocalUserAdministration = 'usr_settings_visib_lua';
    case VisibleInCourses = 'course_export';
    case VisibleInGroups = 'group_export';
    case VisibleInStudyProgrammes = 'prg_export';
    case ChangeableByUser = 'changeable';
    case ChangeableInLocalUserAdministration = 'usr_settings_changeable_lua';
    case Required = 'required_field';
    case Export = 'export';
    case Searchable = 'header_searchable';
    case AvailableInCertificates = 'certificate';

    public function getSettingsAccessPrefix(): string
    {
        return match($this) {
            self::ChangeableByUser => self::SETTINGS_ACCESS_PREFIX_CHANGEABLE_BY_USER,
            self::ChangeableInLocalUserAdministration => self::SETTINGS_ACCESS_PREFIX_CHANGEABLE_IN_LUA,
            self::Export => self::SETTINGS_ACCESS_PREFIX_EXPORT,
            default => throw new \Exception('Not a valid setting!')
        };
    }
}
