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

namespace ILIAS\User\Settings\User;

use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Listing\Unordered as UnorderedListing;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

class Setting
{
    public function __construct(
        private SettingConfiguration $configuration,
        private bool $visible_in_personal_data,
        private bool $visible_in_local_user_administration,
        private bool $changeable_in_profile,
        private bool $changeable_in_local_user_administration,
        private bool $export
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->configuration->getIdentifier();
    }

    public function getLanguageVariable(): string
    {
        return $this->configuration->getLanguageVariable();
    }

    public function isVisibleInPersonalData(): bool
    {
        return $this->visible_in_personal_data;
    }

    public function isVisibleInLocalUserAdministration(): bool
    {
        return $this->visible_in_local_user_administration;
    }

    public function isChangeableInProfile(): bool
    {
        return $this->changeable_in_profile;
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
        return $this->configuration->getSettingsPage();
    }

    public function getSection(): AvailableSections
    {
        return $this->configuration->getSection();
    }

    public function getTableRow(
        DataRowBuilder $row_builder,
        Language $lng,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        Refinery $refinery,
        \ilSetting $settings
    ): DataRow {
        return $row_builder->buildDataRow(
            $this->configuration->getIdentifier(),
            [
                'field' => $lng->txt($this->configuration->getLanguageVariable()),
                'changeable_in_profile' => $this->changeable_in_profile,
                'changeable_in_local_user_administration' => $this->changeable_in_local_user_administration,
                'export' => $this->export
            ]
        );
    }

    public function getInput(
        Language $lng,
        \ilObjUser $current_user
    ): \ilFormPropertyGUI {
        return $this->configuration->getInput(
            $lng,
            $current_user
        );
    }

    public function getForm(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery
    ): array {
        return [
            'setting' => $ff->group([
                'visible_in_personal_data' => $ff->checkbox($lng->txt('user_visible_in_profile'))
                    ->withValue($this->visible_in_personal_data),
                'visible_in_local_user_administration' => $ff->checkbox($lng->txt('usr_settings_visib_lua'))
                    ->withValue($this->visible_in_local_user_administration),
                'changeable_in_profile' => $ff->checkbox($lng->txt('changeable'))
                    ->withValue($this->changeable_in_profile),
                'changeable_in_local_user_administration' => $ff->checkbox($lng->txt('usr_settings_changeable_lua'))
                    ->withValue($this->changeable_in_local_user_administration),
                'export' => $ff->checkbox($lng->txt('export'))
                    ->withValue($this->export),
            ])->withAdditionalTransformation(
                $this->buildCreateSettingTransformation($refinery)
            )
        ];
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings
    ): string {
        $default_value = $this->configuration->getDefaultValueForDisplay($lng, $refinery, $settings);
        if ($default_value === null) {
            return '';
        }
        return $default_value;
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $current_user
    ): bool {
        return $this->configuration->hasUserPersonalizedSetting($settings, $current_user);
    }

    public function storeUserChoice(
        \ilObjUser $current_user,
        mixed $input,
        ?\ilPropertyFormGUI $form
    ): void {
        $this->configuration->storeUserChoice($current_user, $input, $form);
    }

    public function validateUserChoice(
        \ilGlobalTemplateInterface $tpl,
        Language $lng,
        \ilPropertyFormGUI $form
    ): ?string {
        return $this->configuration->validateUserChoice($tpl, $lng, $form);
    }

    private function buildAccessibilityListing(
        Language $lng,
        UIFactory $ui_factory
    ): UnorderedListing {
        $granted_accesses = [];

        if ($this->visible_in_personal_data) {
            $granted_accesses[] = $lng->txt('user_visible_in_profile');
        }

        if ($this->visible_in_local_user_administration) {
            $granted_accesses[] = $lng->txt('usr_settings_visib_lua');
        }

        if ($this->changeable_in_profile) {
            $granted_accesses[] = $lng->txt('changeable');
        }

        if ($this->changeable_in_local_user_administration) {
            $granted_accesses[] = $lng->txt('usr_settings_changeable_lua');
        }

        return $ui_factory->listing()->unordered($granted_accesses);
    }

    private function buildCreateSettingTransformation(
        Refinery $refinery
    ): Transformation {
        return $refinery->custom()->transformation(
            function (array $vs): self {
                $clone = clone $this;
                $clone->visible_in_personal_data = $vs['visible_in_personal_data'];
                $clone->visible_in_local_user_administration = $vs['visible_in_local_user_administration'];
                $clone->changeable_in_profile = $vs['changeable_in_profile'];
                $clone->changeable_in_local_user_administration = $vs['changeable_in_local_user_administration'];
                $clone->export = $vs['export'];
                return $clone;
            }
        );
    }
}
