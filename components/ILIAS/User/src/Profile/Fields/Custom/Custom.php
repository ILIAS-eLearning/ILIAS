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

use ILIAS\User\Context;
use ILIAS\User\Profile\Fields\NoOverrides;
use ILIAS\User\Profile\Fields\FieldDefinition;
use ILIAS\User\Profile\Fields\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\UUID\Uuid;

class Custom implements FieldDefinition
{
    use NoOverrides;

    public function __construct(
        private readonly Uuid $identifier,
        private ?Type $type = null,
        private string $label = '',
        private ?AvailableSections $section = null,
        private ?string $additional_edit_form_data = null
    ) {
    }

    public function isUnspecific(): bool
    {
        return $this->type === null;
    }

    public function getIdentifier(): string
    {
        return $this->identifier->toString();
    }

    public function getLabel(Language $lng): string
    {
        return $this->label;
    }

    public function withLabel(string $label): self
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    public function getTypeLabel(Language $lng): string
    {
        return $this->type?->getLabel($lng) ?? '';
    }

    public function withType(Type $type): self
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function getSection(): AvailableSections
    {
        return $this->section;
    }

    public function withSection(AvailableSections $section): self
    {
        $clone = clone $this;
        $clone->section = $section;
        return $clone;
    }

    public function getAdditionalEditFormInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery
    ): ?FormInput {
        return $this->type?->getAdditionalEditFormInputs(
            $lng,
            $ff,
            $refinery,
            $this->additional_edit_form_data
        );
    }

    public function withAdditionalEditFormData(?string $data): self
    {
        $clone = clone $this;
        $clone->additional_edit_form_data = $data;
        return $clone;
    }

    public function toStorage(): array
    {
        return [
            'field_name' => [
                \ilDBConstants::T_TEXT,
                $this->label
            ],
            'field_type' => [
                \ilDBConstants::T_TEXT,
                $this->type::class
            ],
            'field_values' => [
                \ilDBConstants::T_TEXT,
                $this->additional_edit_form_data
            ],
            'section' => [
                \ilDBConstants::T_TEXT,
                $this->section->value
            ]
        ];
    }

    public function getLegacyInput(
        Language $lng,
        Context $context,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $value = $user === null
            ? []
            : $user->getProfileData()->getAdditionalFieldByIdentifier($this->getIdentifier()) ?? [];
        return $this->type->getLegacyInput(
            $lng,
            $context,
            $value,
            $this->label,
            $this->additional_edit_form_data
        );
    }

    public function addValueToUserObject(
        \ilObjUser $user,
        mixed $input,
        \ilPropertyFormGUI $form
    ): \ilObjUser {
        return $user->withProfileData(
            $user->getProfileData()->withAdditionalFieldByIdentifier(
                $this->getIdentifier(),
                $this->type->prepareUserInputForStorage($input)
            )
        );
    }

    public function retrieveValueFromUser(\ilObjUser $user): string
    {
        return $this->type->buildPresentationValueFromUserValue(
            $user->getProfileData()->getAdditionalFieldByIdentifier($this->getIdentifier()) ?? [],
            $this->additional_edit_form_data
        );
    }
}
