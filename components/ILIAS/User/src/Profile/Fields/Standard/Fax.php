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

use ILIAS\User\Profile\Fields\FieldDefinition;
use ILIAS\User\Profile\Fields\AvailableSections;
use ILIAS\Language\Language;

class Fax implements FieldDefinition
{
    public function getIdentifier(): string
    {
        return 'fax';
    }

    public function getLanguageVariable(): string
    {
        return $this->getIdentifier();
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::ContactData;
    }

    public function hiddenInLists(): bool
    {
        return null;
    }

    public function visibleInPersonalDataForcedTo(): ?bool
    {
        return null;
    }

    public function visibleInLocalUserAdministrationForcedTo(): ?bool
    {
        return null;
    }

    public function visibleInCoursesForcedTo(): ?bool
    {
        return null;
    }

    public function visibleInGroupsForcedTo(): ?bool
    {
        return null;
    }

    public function visibleInStudyProgrammesForcedTo(): ?bool
    {
        return null;
    }

    public function changeableByUserForcedTo(): ?bool
    {
        return null;
    }

    public function changeableInLocalUserAdministrationForcedTo(): ?bool
    {
        return null;
    }

    public function requiredForcedTo(): ?bool
    {
        return null;
    }

    public function exportForcedTo(): ?bool
    {
        return null;
    }

    public function searchableForcedTo(): ?bool
    {
        return null;
    }

    public function getInput(
        Language $lng,
        \ilObjUser $current_user
    ): \ilFormPropertyGUI {
        $input = new \ilTextInputGUI($lng->txt($this->getLanguageVariable()));
        $input->setMaxLength(40);
        $input->setValue(
            $this->getValueForUser($current_user)
        );
        return $input;
    }

    public function storeUserInput(
        \ilObjUser $current_user,
        mixed $input
    ): void {
        $current_user->setFax($input);
    }

    public function getValueForUser(\ilObjUser $current_user): string
    {
        return $current_user->getFax();
    }
}
