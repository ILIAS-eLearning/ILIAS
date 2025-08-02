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

class OrganisationalUnits implements FieldDefinition
{
    use NoOverrides;

    public function getIdentifier(): string
    {
        return 'organisational_units';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt('objs_orgu');
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Access;
    }

    public function hiddenInLists(): bool
    {
        return false;
    }

    public function exportForcedTo(): ?bool
    {
        return false;
    }

    public function changeableByUserForcedTo(): ?bool
    {
        return false;
    }

    public function requiredForcedTo(): ?bool
    {
        return false;
    }

    public function searchableForcedTo(): ?bool
    {
        return false;
    }

    public function availableInCertificatesForcedTo(): ?bool
    {
        return false;
    }

    public function getInput(
        Language $lng,
        ?\ilObjUser $current_user = null
    ): \ilFormPropertyGUI {
        $input = new \ilNonEditableValueGUI($this->getLabel($lng));
        if ($current_user === null) {
            return $input;
        }
        $input->setValue(
            $this->retrieveValueFromUser($current_user)
        );
        return $input;
    }

    public function addValueToUserObject(
        \ilObjUser $current_user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        throw new Exception('This Value cannot be set here!');
    }

    public function retrieveValueFromUser(\ilObjUser $current_user): string
    {
        return $current_user->getOrgUnitsRepresentation();
    }
}
