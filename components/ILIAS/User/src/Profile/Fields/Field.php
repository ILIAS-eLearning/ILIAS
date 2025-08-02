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

namespace ILIAS\User\Profile\Fields;

use ILIAS\User\Property;
use ILIAS\User\PropertyAttributes;
use ILIAS\User\Profile\ChangeListeners\ChangedUserFieldAttribute;
use ILIAS\User\Profile\Fields\FieldDefinition;
use ILIAS\User\Profile\Fields\AvailableSections;
use ILIAS\User\Profile\Fields\Custom\Custom as CustomField;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Listing\Unordered as UnorderedListing;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

class Field implements Property
{
    public function __construct(
        private FieldDefinition $definition,
        private bool $visible_in_registration,
        private bool $visible_in_personal_data,
        private bool $visible_in_local_user_administration,
        private bool $visible_in_courses,
        private bool $visible_in_groups,
        private bool $visible_in_study_programmes,
        private bool $changeable_by_user,
        private bool $changeable_in_local_user_administration,
        private bool $required,
        private bool $export,
        private bool $searchable
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->definition->getIdentifier();
    }

    public function getLanguageVariable(): string
    {
        return $this->definition->getLanguageVariable();
    }

    public function isVisibleInRegistration(): bool
    {
        return $this->visible_in_registration;
    }

    public function isVisibleInPersonalData(): bool
    {
        return $this->visible_in_personal_data;
    }

    public function isVisibleInLocalUserAdministration(): bool
    {
        return $this->visible_in_local_user_administration;
    }

    public function isVisibleInCourses(): bool
    {
        return $this->visible_in_courses;
    }

    public function isVisibleInGroups(): bool
    {
        return $this->visible_in_groups;
    }

    public function isVisibleInStudyProgrammes(): bool
    {
        return $this->visible_in_study_programmes;
    }

    public function isChangeableByUser(): bool
    {
        return $this->changeable_by_user;
    }

    public function isChangeableInLocalUserAdministration(): bool
    {
        return $this->changeable_in_local_user_administration;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function export(): bool
    {
        return $this->export;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isAvailableInCertificates(): bool
    {
        return true;
    }

    public function getSection(): AvailableSections
    {
        return $this->definition->getSection();
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
            $this->definition->getIdentifier(),
            [
                'field' => $lng->txt($this->definition->getLanguageVariable()),
                'access' => $ui_renderer->render($this->buildAccessibilityListing($lng, $ui_factory)),
                'required' => $this->required,
                'export' => $this->export,
                'searchable' => $this->searchable,
                'available_in_certificates' => true
            ]
        );
    }

    public function getForm(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery
    ): array {
        $fields = [];
        if ($this->definition instanceof CustomField) {
            $fields['edit_field'] = $this->definition->getAdditionalEditFormInputs($lng, $ff, $refinery);
        }
        return [
            'field' => $ff->group([
                'access' => $ff->section(
                    [
                        'visible_in_registration' => $ff->checkbox(
                            $lng->txt(PropertyAttributes::VisibleInRegistration->getLanguageVariable())
                        )->withDisabled($this->definition->requiredForcedTo() === true)
                            ->withValue($this->visible_in_registration),
                        'visible_in_personal_data' => $ff->checkbox(
                            $lng->txt(PropertyAttributes::HiddenFromUser->getLanguageVariable())
                        )->withDisabled($this->definition->visibleInPersonalDataForcedTo() !== null)
                            ->withValue($this->visible_in_personal_data),
                        'visible_in_local_user_administration' => $ff->checkbox(
                            $lng->txt(PropertyAttributes::VisibleInLocalUserAdministration->getLanguageVariable())
                        )->withDisabled($this->definition->visibleInLocalUserAdministrationForcedTo() !== null)
                            ->withValue($this->visible_in_local_user_administration),
                        'visible_in_courses' => $ff->checkbox(
                            $lng->txt(PropertyAttributes::VisibleInCourses->getLanguageVariable())
                        )->withDisabled($this->definition->visibleInCoursesForcedTo() !== null)
                            ->withValue($this->visible_in_courses),
                        'visible_in_groups' => $ff->checkbox(
                            $lng->txt(PropertyAttributes::VisibleInGroups->getLanguageVariable())
                        )->withDisabled($this->definition->visibleInGroupsForcedTo() !== null)
                            ->withValue($this->visible_in_groups),
                        'visible_in_study_programmes' => $ff->checkbox(
                            $lng->txt(PropertyAttributes::VisibleInStudyProgrammes->getLanguageVariable())
                        )->withDisabled($this->definition->visibleInStudyProgrammesForcedTo() !== null)
                            ->withValue($this->visible_in_study_programmes),
                        'changeable_by_user' => $ff->checkbox(
                            $lng->txt(PropertyAttributes::UnchangeableByUser->getLanguageVariable())
                        )->withDisabled($this->definition->changeableByUserForcedTo() !== null)
                            ->withValue($this->changeable_by_user),
                        'changeable_in_local_user_administration' => $ff->checkbox(
                            $lng->txt(PropertyAttributes::ChangeableInLocalUserAdministration->getLanguageVariable())
                        )->withDisabled($this->definition->changeableInLocalUserAdministrationForcedTo() !== null)
                            ->withValue($this->changeable_in_local_user_administration)
                    ],
                    $lng->txt('access')
                ),
                'settings' => $ff->section(
                    [
                        'required' => $ff->checkbox(
                            $lng->txt(PropertyAttributes::Required->getLanguageVariable())
                        )->withDisabled($this->definition->requiredForcedTo() !== null)
                            ->withValue($this->definition->requiredForcedTo() ?? $this->required),
                        'export' => $ff->checkbox(
                            $lng->txt(PropertyAttributes::Export->getLanguageVariable())
                        )->withDisabled($this->definition->exportForcedTo() !== null)
                            ->withValue($this->export),
                        'searchable' => $ff->checkbox(
                            $lng->txt(PropertyAttributes::Searchable->getLanguageVariable())
                        )->withDisabled($this->definition->searchableForcedTo() !== null)
                            ->withValue($this->searchable),
                    ],
                    $lng->txt('settings')
                )
            ])->withAdditionalTransformation(
                $this->buildCreateFieldTransformation($refinery)
            )
        ];
    }

    public function getHiddenForm(
        FieldFactory $ff,
        Refinery $refinery
    ): array {
        return [
            'field' => $ff->group([
                'access' => $ff->group(
                    [
                        'visible_in_registration' => $ff->hidden()
                            ->withDisabled($this->definition->requiredForcedTo() === true)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->visible_in_registration ? '1' : '0'),
                        'visible_in_personal_data' => $ff->hidden()
                            ->withDisabled($this->definition->visibleInPersonalDataForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->visible_in_personal_data ? '1' : '0'),
                        'visible_in_local_user_administration' => $ff->hidden()
                            ->withDisabled($this->definition->visibleInLocalUserAdministrationForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->visible_in_local_user_administration ? '1' : '0'),
                        'visible_in_courses' => $ff->hidden()
                            ->withDisabled($this->definition->visibleInCoursesForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->visible_in_courses ? '1' : '0'),
                        'visible_in_groups' => $ff->hidden()
                            ->withDisabled($this->definition->visibleInGroupsForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->visible_in_groups ? '1' : '0'),
                        'visible_in_study_programmes' => $ff->hidden()
                            ->withDisabled($this->definition->visibleInStudyProgrammesForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->visible_in_study_programmes ? '1' : '0'),
                        'changeable_by_user' => $ff->hidden()
                            ->withDisabled($this->definition->changeableByUserForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->changeable_by_user ? '1' : '0'),
                        'changeable_in_local_user_administration' => $ff->hidden()
                            ->withDisabled($this->definition->changeableInLocalUserAdministrationForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->changeable_in_local_user_administration ? '1' : '0')
                    ]
                ),
                'settings' => $ff->group(
                    [
                        'required' => $ff->hidden()
                            ->withDisabled($this->definition->requiredForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->required ? '1' : '0'),
                        'export' => $ff->hidden()
                            ->withDisabled($this->definition->exportForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->export ? '1' : '0'),
                        'searchable' => $ff->hidden()
                            ->withDisabled($this->definition->searchableForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->searchable ? '1' : '0'),
                    ]
                )
            ])->withAdditionalTransformation(
                $this->buildCreateFieldTransformation($refinery)
            )
        ];
    }

    public function getChangedAttributes(self $new_field): array
    {
        return array_reduce(
            PropertyAttributes::cases(),
            function (array $c, PropertyAttributes $v) use ($new_field): array {
                $old_value = $this->retrieveValueByPropertyAttribute($v);
                $new_value = $new_field->retrieveValueByPropertyAttribute($v);
                if ($old_value !== $new_value) {
                    $c[$v->value] = new ChangedUserFieldAttribute(
                        $v,
                        $old_value,
                        $new_value
                    );
                }
                return $c;
            },
            []
        );
    }

    public function retrieveValueByPropertyAttribute(
        PropertyAttributes $attribute
    ): bool {
        return match ($attribute) {
            PropertyAttributes::VisibleInRegistration => $this->visible_in_registration,
            PropertyAttributes::HiddenFromUser => $this->visible_in_personal_data,
            PropertyAttributes::VisibleInLocalUserAdministration => $this->visible_in_local_user_administration,
            PropertyAttributes::VisibleInCourses => $this->visible_in_courses,
            PropertyAttributes::VisibleInGroups => $this->visible_in_groups,
            PropertyAttributes::VisibleInStudyProgrammes => $this->visible_in_study_programmes,
            PropertyAttributes::UnchangeableByUser => $this->changeable_by_user,
            PropertyAttributes::ChangeableInLocalUserAdministration => $this->changeable_in_local_user_administration,
            PropertyAttributes::Required => $this->required,
            PropertyAttributes::Export => $this->export,
            PropertyAttributes::Searchable => $this->searchable,
            PropertyAttributes::AvailableInCertificates => true
        };
    }

    public function getInput(
        Language $lng,
        \ilObjUser $current_user
    ): \ilFormPropertyGUI {
        $input = $this->definition->getInput(
            $lng,
            $current_user
        );
        $input->setPostVar($this->definition->getIdentifier());
        $input->setRequired($this->required);
        return $input;
    }

    public function storeUserInput(
        \ilObjUser $current_user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): void {
        $this->definition->storeUserInput($current_user, $input, $form);
    }

    public function getValueForUser(\ilObjUser $current_user): mixed
    {
        return $this->definition->getValueForUser($current_user);
    }

    private function buildAccessibilityListing(
        Language $lng,
        UIFactory $ui_factory
    ): UnorderedListing {
        $granted_accesses = [];
        if ($this->visible_in_registration) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::VisibleInRegistration->getLanguageVariable()
            );
        }

        if ($this->visible_in_personal_data) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::HiddenFromUser->getLanguageVariable()
            );
        }

        if ($this->visible_in_local_user_administration) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::VisibleInLocalUserAdministration->getLanguageVariable()
            );
        }

        if ($this->visible_in_courses) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::VisibleInCourses->getLanguageVariable()
            );
        }

        if ($this->visible_in_groups) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::VisibleInGroups->getLanguageVariable()
            );
        }

        if ($this->visible_in_study_programmes) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::VisibleInStudyProgrammes->getLanguageVariable()
            );
        }

        if ($this->changeable_by_user) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::UnchangeableByUser->getLanguageVariable()
            );
        }

        if ($this->changeable_in_local_user_administration) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::ChangeableInLocalUserAdministration->getLanguageVariable()
            );
        }

        return $ui_factory->listing()->unordered($granted_accesses);
    }

    private function buildCreateFieldTransformation(
        Refinery $refinery
    ): Transformation {
        return $refinery->custom()->transformation(
            function (array $vs): self {
                $clone = clone $this;
                $clone->visible_in_registration = $this->definition->requiredForcedTo() === true
                    ? true
                    : $vs['access']['visible_in_registration'];
                $clone->visible_in_personal_data = $this->definition->visibleInPersonalDataForcedTo()
                    ?? $vs['access']['visible_in_personal_data'];
                $clone->visible_in_local_user_administration = $this->definition->visibleInLocalUserAdministrationForcedTo()
                    ?? $vs['access']['visible_in_local_user_administration'];
                $clone->visible_in_courses = $this->definition->visibleInCoursesForcedTo()
                    ?? $vs['access']['visible_in_courses'];
                $clone->visible_in_groups = $this->definition->visibleInGroupsForcedTo()
                    ?? $vs['access']['visible_in_groups'];
                $clone->visible_in_study_programmes = $this->definition->visibleInStudyProgrammesForcedTo()
                    ?? $vs['access']['visible_in_study_programmes'];
                $clone->changeable_by_user = $this->definition->changeableByUserForcedTo()
                    ?? $vs['access']['changeable_by_user'];
                $clone->changeable_in_local_user_administration = $this->definition->changeableInLocalUserAdministrationForcedTo()
                    ?? $vs['access']['changeable_in_local_user_administration'];
                $clone->required = $this->definition->requiredForcedTo()
                    ?? $vs['settings']['required'];
                $clone->export = $this->definition->exportForcedTo()
                    ?? $vs['settings']['export'];
                $clone->searchable = $this->definition->searchableForcedTo()
                    ?? $vs['settings']['searchable'];
                return $clone;
            }
        );
    }
}
