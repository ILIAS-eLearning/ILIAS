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

class HelpOffered implements FieldDefinition
{
    use NoOverrides;
    use BuildAutocompletionUrl;

    public function __construct(
        private readonly \ilCtrl $ctrl
    ) {
    }

    public function getIdentifier(): string
    {
        return 'interests_help_offered';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt('interests_help_offered');
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

    public function getLegacyInput(
        Language $lng,
        Context $context,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $input = new \ilTextInputGUI($lng->txt('interests_help_offered'));
        $input->setMulti(true);
        $input->setDataSource(
            $this->getAutocompleteUrl($this->ctrl) . '&f=' . $this->getIdentifier()
        );
        $input->setMaxLength(40);
        if ($user === null) {
            return $input;
        }
        $input->setValue(
            $this->retrieveValueFromUser($user)
        );
        return $input;
    }

    public function addValueToUserObject(
        \ilObjUser $user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        $user->setOfferingHelp($input);
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): array
    {
        return $user->getOfferingHelp();
    }
}
