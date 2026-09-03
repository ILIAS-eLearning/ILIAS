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

namespace ILIAS\User\Settings;

use ILIAS\User\Context;
use ILIAS\User\Property;
use ILIAS\User\PropertyAttributes;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;

class Setting implements Property
{
    public function __construct(
        private SettingDefinition $definition,
        private bool $changeable_by_user,
        private bool $changeable_in_local_user_administration,
        private bool $export
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->definition->getIdentifier();
    }

    public function getLabel(Language $lng): string
    {
        return $this->definition->getLabel($lng);
    }

    public function isChangeableByUser(): bool
    {
        return $this->changeable_by_user;
    }

    public function isChangeableInLocalUserAdministration(): bool
    {
        return $this->changeable_in_local_user_administration;
    }

    public function export(): bool
    {
        return $this->export;
    }

    public function getSettingsPage(): AvailablePages
    {
        return $this->definition->getSettingsPage();
    }

    public function getSection(): AvailableSections
    {
        return $this->definition->getSection();
    }

    public function getTableRow(
        DataRowBuilder $row_builder,
        Language $lng
    ): DataRow {
        return $row_builder->buildDataRow(
            $this->definition->getIdentifier(),
            [
                'field' => $this->definition->getLabel($lng),
                'changeable_by_user' => $this->changeable_by_user,
                'changeable_in_local_user_administration' => $this->changeable_in_local_user_administration,
                'export' => $this->export
            ]
        );
    }

    public function getInput(
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input {
        return $this->definition->getInput($field_factory, $lng, $refinery, $settings, $user);
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $input = $this->definition->getLegacyInput(
            $lng,
            $settings,
            $user
        );

        $input->setPostVar($this->definition->getIdentifier());
        return $input;
    }

    /**
     * @return array<string, Input>
     */
    public function getForm(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery
    ): array {
        return [
            'setting' => $ff->group([
                'changeable_by_user' => $ff->checkbox($lng->txt(
                    PropertyAttributes::ChangeableByUser->value
                ))->withValue($this->changeable_by_user),
                'changeable_in_local_user_administration' => $ff->checkbox($lng->txt(
                    PropertyAttributes::ChangeableInLocalUserAdministration->value
                ))->withValue($this->changeable_in_local_user_administration),
                'export' => $ff->checkbox($lng->txt(
                    PropertyAttributes::Export->value
                ))->withValue($this->export),
            ])->withAdditionalTransformation(
                $this->buildCreateSettingTransformation($refinery)
            )
        ];
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): string {
        $default_value = $this->definition->getDefaultValueForDisplay($lng, $settings);
        if ($default_value === null) {
            return '';
        }
        return $default_value;
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        ?\ilObjUser $user
    ): bool {
        if ($user === null) {
            return false;
        }
        return $this->definition->hasUserPersonalizedSetting($settings, $user);
    }

    public function persistUserInput(
        \ilObjUser $user,
        Context $context,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        if (!$context->isSettingAvailable($this)) {
            throw new \DomainException('It is not possible to Change this from here!');
        }
        return $this->definition->persistUserInput($user, $input, $form);
    }

    public function retrieveValueFromUser(\ilObjUser $user): mixed
    {
        return $this->definition->retrieveValueFromUser($user);
    }

    public function validateUserChoice(
        \ilGlobalTemplateInterface $tpl,
        Language $lng,
        \ilPropertyFormGUI $form
    ): ?bool {
        return $this->definition->validateUserChoice($tpl, $lng, $form);
    }

    private function buildCreateSettingTransformation(
        Refinery $refinery
    ): Transformation {
        return $refinery->custom()->transformation(
            function (array $vs): self {
                $clone = clone $this;
                $clone->changeable_by_user = $vs['changeable_by_user'];
                $clone->changeable_in_local_user_administration = $vs['changeable_in_local_user_administration'];
                $clone->export = $vs['export'];
                return $clone;
            }
        );
    }
}
