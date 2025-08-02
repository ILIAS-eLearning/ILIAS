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

namespace ILIAS\User\Profile\Fields\Custom;

use ILIAS\User\Profile\Fields\FieldDefinition;
use ILIAS\User\Profile\Fields\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Factory as Refinery;

class Custom implements FieldDefinition
{
    public function __construct(
        private readonly Type $type,
        private readonly string $identifier,
        private readonly AvailableSections $section
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLanguageVariable(): string
    {
        return $this->getIdentifier();
    }

    public function getSection(): AvailableSections
    {
        return $this->section;
    }

    public function hiddenInLists(): bool
    {
        return true;
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

    public function buildAdditionalEditFormInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery
    ): ?FormInput {
        $this->type->getAdditionalEditFormInputs($lng, $ff, $refinery);
    }

    public function storeAdditionalEditFormInputs(mixed $value): void
    {
        $this->type->storeAdditionalEditFormInputs($value);
    }

    public function getInput(
        Language $lng,
        \ilObjUser $current_user
    ): \ilFormPropertyGUI {
        $this->type->getInput($lng, $current_user);
    }

    public function storeUserInput(
        \ilObjUser $current_user,
        mixed $input
    ): void {
        $this->type->storeUserInput($current_user, $input);
    }

    public function getValueForUser(\ilObjUser $current_user): string
    {
        return $this->type->getValueForUser($current_user);
    }
}
