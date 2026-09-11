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
use ILIAS\Data\Privacy\Purpose\Purposes;
use ILIAS\Data\Privacy\Source\Sources;

class City implements FieldDefinition
{
    use NoOverrides;

    public function __construct(
        private readonly Purposes $purposes,
        private readonly Sources $sources,
    ) {
    }

    public function getIdentifier(): string
    {
        return 'city';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt($this->getIdentifier());
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::ContactData;
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
        $input = new \ilTextInputGUI($this->getLabel($lng));
        $input->setMaxLength(40);
        if ($user === null) {
            return $input;
        }
        $input->setValue(
            $user->getProfileData()->getPostalAddress()
                ?->resolve($this->purposes->displayToUser('profile_form'))->city ?? ''
        );
        return $input;
    }

    public function addValueToUserObject(
        \ilObjUser $user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        $address = $user->getProfileData()->getPostalAddress()
            ?->withCity((string) $input, $this->sources->userInput('profile_form'));
        if ($address === null) {
            $user->setCity($input);
            return $user;
        }
        return $user->withProfileData(
            $user->getProfileData()->withPostalAddress($address)
        );
    }

    public function retrieveValueFromUser(\ilObjUser $user): string
    {
        return $user->getCity();
    }
}
