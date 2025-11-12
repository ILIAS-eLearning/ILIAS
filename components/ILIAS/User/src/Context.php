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

namespace ILIAS\User;

use ILIAS\User\Profile\Fields\Field;
use ILIAS\User\Settings\Setting;

enum Context
{
    case Registration;
    case User;
    case UserAdministration;
    case LocalUserAdministration;
    case Certificate;
    case Course;
    case Group;
    case LearningSequence;
    case StudyProgramme;
    case Search;
    case Export;

    public function isFieldVisible(
        Field $field,
        ?\ilObjUser $user
    ): bool {
        return match($this) {
            self::Registration => $field->isVisibleInRegistration(),
            self::User => $field->isVisibleToUser()
                || $field->isRequired() && ($user === null || empty($field->retrieveValueFromUser($user))),
            self::LocalUserAdministration => $field->isVisibleInLocalUserAdministration()
                || $field->isRequired() && ($user === null || empty($field->retrieveValueFromUser($user))),
            self::Certificate => $field->isAvailableInCertificates(),
            self::Course => $field->isVisibleInCourses(),
            self::Group => $field->isVisibleInGroups(),
            self::StudyProgramme => $field->isVisibleInStudyProgrammes(),
            self::Search => $field->isSearchable(),
            self::Export => $field->export(),
            self::UserAdministration => true,
            default => false
        };
    }

    public function isFieldChangeable(
        Field $field,
        ?\ilObjUser $user
    ): bool {
        return match($this) {
            self::Registration => $field->isVisibleInRegistration(),
            self::User => $field->isChangeableByUser()
                || $field->isRequired() && ($user === null || empty($field->retrieveValueFromUser($user))),
            self::LocalUserAdministration => $field->isChangeableInLocalUserAdministration()
                || $field->isRequired() && ($user === null || empty($field->retrieveValueFromUser($user))),
            self::UserAdministration => true,
            default => false
        };
    }

    public function isSettingAvailable(
        Setting $setting
    ): bool {
        return match($this) {
            self::UserAdministration => true,
            self::LocalUserAdministration => $setting->isChangeableInLocalUserAdministration(),
            self::User => $setting->isChangeableByUser(),
            self::Export => $setting->export(),
            default => false
        };
    }

    public static function buildFromObjectType(string $type): ?self
    {
        return match($type) {
            'crs' => self::Course,
            'grp' => self::Group,
            'prg' => self::StudyProgramme,
            'lso' => self::LearningSequence,
            default => null
        };
    }
}
