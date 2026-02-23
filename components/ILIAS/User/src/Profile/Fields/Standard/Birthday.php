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

class Birthday implements FieldDefinition
{
    use NoOverrides;

    public function getIdentifier(): string
    {
        return 'birthday';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt($this->getIdentifier());
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::PersonalData;
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
        $input = new \ilBirthdayInputGUI($this->getLabel($lng));
        $value = $user === null ? '' : $this->retrieveValueFromUser($user);

        if ($value === '') {
            return $input;
        }

        $input->setDate(
            new \ilDateTime($value, IL_CAL_DATE)
        );
        return $input;
    }

    public function addValueToUserObject(
        \ilObjUser $user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        $user->setBirthday($input);
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): ?string
    {
        return $user->getBirthday();
    }
}
