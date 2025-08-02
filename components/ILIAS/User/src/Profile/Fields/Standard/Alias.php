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

namespace ILIAS\User\Profile\Fields\Standard;

use ILIAS\User\Context;
use ILIAS\User\Profile\Fields\NoOverrides;
use ILIAS\User\Profile\Fields\FieldDefinition;
use ILIAS\User\Profile\Fields\AvailableSections;
use ILIAS\Language\Language;

class Alias implements FieldDefinition
{
    use NoOverrides;

    public function getIdentifier(): string
    {
        return 'username';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt('login');
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::PersonalData;
    }

    public function visibleInRegistrationForcedTo(): ?bool
    {
        return true;
    }

    public function visibleToUserForcedTo(): ?bool
    {
        return true;
    }

    public function visibleInLocalUserAdministrationForcedTo(): ?bool
    {
        return true;
    }

    public function visibleInCoursesForcedTo(): ?bool
    {
        return true;
    }

    public function visibleInGroupsForcedTo(): ?bool
    {
        return true;
    }

    public function visibleInStudyProgrammesForcedTo(): ?bool
    {
        return false;
    }

    public function requiredForcedTo(): ?bool
    {
        return true;
    }

    public function searchableForcedTo(): ?bool
    {
        return true;
    }

    public function availableInCertificatesForcedTo(): ?bool
    {
        return true;
    }

    public function getLegacyInput(
        Language $lng,
        Context $context,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $input = new \ilUserLoginInputGUI($lng->txt('login'), 'login');
        if ($user === null) {
            return $input;
        }

        $input->setCurrentUserId($user->getId());
        $input->setValue(
            $this->retrieveValueFromUser($user)
        );

        $last_history_entry = $user->getLastHistoryData();
        if ($last_history_entry === null) {
            return $input;
        }

        $input->setInfo(
            sprintf(
                $lng->txt('usr_loginname_history_info'),
                \ilDatePresentation::formatDate(new \ilDateTime($last_history_entry[1], IL_CAL_UNIX)),
                $last_history_entry[0]
            )
        );
        return $input;
    }

    public function addValueToUserObject(
        \ilObjUser $user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        $user->setLogin($input);
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): string
    {
        return $user->getLogin();
    }
}
