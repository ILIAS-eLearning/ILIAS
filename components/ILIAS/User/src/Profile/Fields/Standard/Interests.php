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

use ILIAS\User\Profile\Fields\NoOverrides;
use ILIAS\User\Profile\Fields\FieldDefinition;
use ILIAS\User\Profile\Fields\AvailableSections;
use ILIAS\Language\Language;

class Interest implements FieldDefinition
{
    use NoOverrides;

    public function getIdentifier(): string
    {
        return 'interests';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt('interests_general');
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Interests;
    }

    public function hiddenInLists(): bool
    {
        return true;
    }

    public function visibleInCoursesForcedTo(): ?bool
    {
        return false;
    }

    public function visibleInGroupsForcedTo(): ?bool
    {
        return false;
    }

    public function visibleInStudyProgrammesForcedTo(): ?bool
    {
        return false;
    }

    public function availableInCertificatesForcedTo(): ?bool
    {
        return false;
    }

    public function getInput(
        Language $lng,
        \ilObjUser $current_user
    ): \ilFormPropertyGUI {
        $input = new \ilTextInputGUI($this->getLabel($lng));
        $input->setMulti(true);
        $input->setMaxLength(40);
        $input->setValue(
            $this->getValueForUser($current_user)
        );
        return $input;
    }

    public function addValueToUserObject(
        \ilObjUser $current_user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        $current_user->setGeneralInterests($input);
        return $current_user;
    }

    public function getValueForUser(\ilObjUser $current_user): array
    {
        return $current_user->getGeneralInterests();
    }
}
