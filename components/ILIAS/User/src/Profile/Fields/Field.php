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
use ILIAS\User\Profile\Fields\Custom\Type as CustomType;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Listing\Unordered as UnorderedListing;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

class Field implements Property
{
    public function __construct(
        private FieldDefinition $definition,
        private bool $visible_in_registration = false,
        private bool $visible_to_user = false,
        private bool $visible_in_local_user_administration = false,
        private bool $visible_in_courses = false,
        private bool $visible_in_groups = false,
        private bool $visible_in_study_programmes = false,
        private bool $changeable_by_user = false,
        private bool $changeable_in_local_user_administration = false,
        private bool $required = false,
        private bool $export = false,
        private bool $searchable = false,
        private bool $available_in_certificates = false
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->definition->getIdentifier();
    }

    public function getDefinition(): FieldDefinition
    {
        return $this->definition;
    }

    public function getLabel(Language $lng): string
    {
        return $this->definition->getLabel($lng);
    }

    public function isCustom(): bool
    {
        return $this->definition instanceof CustomField;
    }

    public function isVisibleInRegistration(): bool
    {
        return $this->visible_in_registration;
    }

    public function isVisibleToUser(): bool
    {
        return $this->definition->visibleToUserForcedTo()
            ?? $this->visible_to_user;
    }

    public function isVisibleInLocalUserAdministration(): bool
    {
        return $this->definition->visibleInLocalUserAdministrationForcedTo()
            ?? $this->visible_in_local_user_administration;
    }

    public function isVisibleInCourses(): bool
    {
        return $this->definition->visibleInCoursesForcedTo()
            ?? $this->visible_in_courses;
    }

    public function isVisibleInGroups(): bool
    {
        return $this->definition->visibleInGroupsForcedTo()
            ?? $this->visible_in_groups;
    }

    public function isVisibleInStudyProgrammes(): bool
    {
        return $this->definition->visibleInStudyProgrammesForcedTo()
            ?? $this->visible_in_study_programmes;
    }

    public function isChangeableByUser(): bool
    {
        return $this->definition->changeableByUserForcedTo()
            ?? $this->changeable_by_user;
    }

    public function isChangeableInLocalUserAdministration(): bool
    {
        return $this->definition->changeableInLocalUserAdministrationForcedTo()
            ?? $this->changeable_in_local_user_administration;
    }

    public function isRequired(): bool
    {
        return $this->definition->requiredForcedTo()
            ?? $this->required;
    }

    public function export(): bool
    {
        return $this->definition->requiredForcedTo()
            ?? $this->export;
    }

    public function isSearchable(): bool
    {
        return $this->definition->searchableForcedTo()
            ?? $this->searchable;
    }

    public function isAvailableInCertificates(): bool
    {
        return $this->definition->availableInCertificatesForcedTo()
            ?? $this->available_in_certificates;
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
                'field' => $this->definition->getLabel($lng),
                'type' => $this->definition instanceof CustomField
                    ? "{$lng->txt('field_type_custom')}: {$this->definition->getTypeLabel($lng)}"
                    : $lng->txt('default'),
                'access' => $ui_renderer->render($this->buildAccessibilityListing($lng, $ui_factory)),
                'required' => $this->isRequired(),
                'export' => $this->export(),
                'searchable' => $this->isSearchable(),
                'available_in_certificates' => $this->isAvailableInCertificates()
            ]
        )->withDisabledAction('delete', !$this->isCustom());
    }

    public function getEditForm(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery,
        array $custom_field_types,
        array $available_custom_fields
    ): array {
        $fields = [];
        if ($this->definition instanceof CustomField) {
            $fields['edit_field'] = $ff->section(
                $this->getCustomFieldInputs($lng, $ff, $refinery, $available_custom_fields),
                $lng->txt('properties')
            );
        }
        $fields['configuration'] = $this->getBaseInputs($lng, $ff, $refinery);
        return [
            'field' => $ff->group($fields)
                ->withAdditionalTransformation(
                    $this->buildCreateFieldTransformation($refinery, $custom_field_types)
                )
        ];
    }

    public function getHiddenForm(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery,
        array $custom_field_types,
        array $available_custom_fields
    ): array {
        $fields = [];
        if ($this->definition instanceof CustomField) {
            $fields['edit_field'] = $ff->section(
                $this->getHiddenCustomFieldInputs($lng, $ff, $refinery, $available_custom_fields),
                $lng->txt('properties')
            );
        }
        $fields['configuration'] = $this->getHiddenBaseInputs($ff, $refinery);
        return [
            'field' => $ff->group($fields)
                ->withAdditionalTransformation(
                    $this->buildCreateFieldTransformation($refinery, $custom_field_types)
                )
        ];
    }

    public function getCreateCustomFieldForm(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery,
        array $custom_field_types,
        array $available_custom_fields
    ): array {
        return [
            'field' => $ff->group([
                'edit_field' => $ff->section(
                    [
                        'type' => $ff->switchableGroup(
                            $this->buildCustomTypeSelectionGroups(
                                $lng,
                                $ff,
                                $refinery,
                                $custom_field_types
                            ),
                            $lng->txt('type')
                        )->withRequired(true),
                        'label' => $ff->text($lng->txt('field_name'))
                            ->withAdditionalTransformation(
                                $this->buildLabelConstraint($lng, $refinery, $available_custom_fields)
                            )->withRequired(true),
                        'section' => $ff->select(
                            $lng->txt('meta_section'),
                            array_reduce(
                                AvailableSections::cases(),
                                static function (array $c, AvailableSections $v) use ($lng): array {
                                    $c[$v->value] = $lng->txt($v->value);
                                    return $c;
                                },
                                []
                            )
                        )->withRequired(true)
                    ],
                    $lng->txt('properties')
                ),
                'configuration' => $this->getBaseInputs($lng, $ff, $refinery)
            ])->withAdditionalTransformation(
                $this->buildCreateFieldTransformation($refinery, $custom_field_types)
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
            PropertyAttributes::VisibleInRegistration => $this->isVisibleInRegistration(),
            PropertyAttributes::VisibleToUser => $this->isVisibleToUser(),
            PropertyAttributes::VisibleInLocalUserAdministration => $this->isVisibleInLocalUserAdministration(),
            PropertyAttributes::VisibleInCourses => $this->isVisibleInCourses(),
            PropertyAttributes::VisibleInGroups => $this->isVisibleInGroups(),
            PropertyAttributes::VisibleInStudyProgrammes => $this->isVisibleInStudyProgrammes(),
            PropertyAttributes::ChangeableByUser => $this->isChangeableByUser(),
            PropertyAttributes::ChangeableInLocalUserAdministration => $this->isChangeableInLocalUserAdministration(),
            PropertyAttributes::Required => $this->isRequired(),
            PropertyAttributes::Export => $this->export(),
            PropertyAttributes::Searchable => $this->isSearchable(),
            PropertyAttributes::AvailableInCertificates => $this->isAvailableInCertificates()
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

    public function addValueToUserObject(
        \ilObjUser $user,
        mixed $input,
        \ilPropertyFormGUI $form
    ): \ilObjUser {
        return $this->definition->addValueToUserObject($user, $input, $form);
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
        if ($this->isVisibleInRegistration()) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::VisibleInRegistration->value
            );
        }

        if ($this->isVisibleToUser()) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::VisibleToUser->value
            );
        }

        if ($this->isVisibleInLocalUserAdministration()) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::VisibleInLocalUserAdministration->value
            );
        }

        if ($this->isVisibleInCourses()) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::VisibleInCourses->value
            );
        }

        if ($this->isVisibleInGroups()) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::VisibleInGroups->value
            );
        }

        if ($this->isVisibleInStudyProgrammes()) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::VisibleInStudyProgrammes->value
            );
        }

        if ($this->isChangeableByUser()) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::ChangeableByUser->value
            );
        }

        if ($this->isChangeableInLocalUserAdministration()) {
            $granted_accesses[] = $lng->txt(
                PropertyAttributes::ChangeableInLocalUserAdministration->value
            );
        }

        return $ui_factory->listing()->unordered($granted_accesses);
    }

    private function getCustomFieldInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery,
        array $available_custom_fields
    ): array {
        return array_filter([
            'label' => $ff->text($lng->txt('field_name'))
                ->withAdditionalTransformation(
                    $this->buildLabelConstraint(
                        $lng,
                        $refinery,
                        $available_custom_fields
                    )
                )->withRequired(true)
                ->withValue($this->getLabel($lng)),
            'type' => $ff->select($lng->txt('type'), ['0' => $this->definition->getTypeLabel($lng)])
                ->withDisabled(true)
                ->withValue('0'),
            'data' => $this->definition->getAdditionalEditFormInputs($lng, $ff, $refinery),
            'section' => $ff->select(
                $lng->txt('meta_section'),
                array_reduce(
                    AvailableSections::cases(),
                    function (array $c, AvailableSections $v) use ($lng): array {
                        $c[$v->value] = $lng->txt($v->value);
                        return $c;
                    },
                    []
                )
            )->withRequired(true)
                ->withValue($this->definition->getSection()->value)
        ]);
    }

    private function getBaseInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery
    ): Section {
        return $ff->section(
            [
                'field' => $ff->group([
                    'access' => $ff->section(
                        [
                            'visible_in_registration' => $ff->checkbox(
                                $lng->txt(PropertyAttributes::VisibleInRegistration->value)
                            )->withDisabled($this->definition->requiredForcedTo() === true)
                                ->withValue($this->isVisibleInRegistration()),
                            'visible_in_personal_data' => $ff->checkbox(
                                $lng->txt(PropertyAttributes::VisibleToUser->value)
                            )->withDisabled($this->definition->visibleToUserForcedTo() !== null)
                                ->withValue($this->isVisibleToUser()),
                            'visible_in_local_user_administration' => $ff->checkbox(
                                $lng->txt(PropertyAttributes::VisibleInLocalUserAdministration->value)
                            )->withDisabled($this->definition->visibleInLocalUserAdministrationForcedTo() !== null)
                                ->withValue($this->isVisibleInLocalUserAdministration()),
                            'visible_in_courses' => $ff->checkbox(
                                $lng->txt(PropertyAttributes::VisibleInCourses->value)
                            )->withDisabled($this->definition->visibleInCoursesForcedTo() !== null)
                                ->withValue($this->isVisibleInCourses()),
                            'visible_in_groups' => $ff->checkbox(
                                $lng->txt(PropertyAttributes::VisibleInGroups->value)
                            )->withDisabled($this->definition->visibleInGroupsForcedTo() !== null)
                                ->withValue($this->isVisibleInGroups()),
                            'visible_in_study_programmes' => $ff->checkbox(
                                $lng->txt(PropertyAttributes::VisibleInStudyProgrammes->value)
                            )->withDisabled($this->definition->visibleInStudyProgrammesForcedTo() !== null)
                                ->withValue($this->isVisibleInStudyProgrammes()),
                            'changeable_by_user' => $ff->checkbox(
                                $lng->txt(PropertyAttributes::ChangeableByUser->value)
                            )->withDisabled($this->definition->changeableByUserForcedTo() !== null)
                                ->withValue($this->isChangeableByUser()),
                            'changeable_in_local_user_administration' => $ff->checkbox(
                                $lng->txt(PropertyAttributes::ChangeableInLocalUserAdministration->value)
                            )->withDisabled($this->definition->changeableInLocalUserAdministrationForcedTo() !== null)
                                ->withValue($this->isChangeableInLocalUserAdministration())
                        ],
                        $lng->txt('access')
                    ),
                    'settings' => $ff->section(
                        [
                            'required' => $ff->checkbox(
                                $lng->txt(PropertyAttributes::Required->value)
                            )->withDisabled($this->definition->requiredForcedTo() !== null)
                                ->withValue($this->isRequired()),
                            'export' => $ff->checkbox(
                                $lng->txt(PropertyAttributes::Export->value)
                            )->withDisabled($this->definition->exportForcedTo() !== null)
                                ->withValue($this->export()),
                            'searchable' => $ff->checkbox(
                                $lng->txt(PropertyAttributes::Searchable->value)
                            )->withDisabled($this->definition->searchableForcedTo() !== null)
                                ->withValue($this->isSearchable()),
                            'available_in_certificates' => $ff->checkbox(
                                $lng->txt(PropertyAttributes::AvailableInCertificates->value)
                            )->withDisabled($this->definition->availableInCertificatesForcedTo() !== null)
                                ->withValue($this->isAvailableInCertificates())
                        ],
                        $lng->txt('settings')
                    )
                ])
            ],
            $lng->txt('configuration')
        )->withAdditionalTransformation(
            $this->buildRequiredMustByVisibleInRegistrationConstraint($lng, $refinery)
        );
    }

    private function getHiddenCustomFieldInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery,
        array $available_custom_fields
    ): array {
        return array_filter([
            'label' => $ff->hidden()
                ->withAdditionalTransformation(
                    $this->buildLabelConstraint($lng, $refinery, $available_custom_fields)
                )->withValue($this->getLabel($lng)),
            'data' => $ff->hidden()->withValue($this->definition->getAdditionalEditFormData()),
            'section' => $ff->hidden()->withValue($this->getSection())
        ]);
    }

    private function getHiddenBaseInputs(
        FieldFactory $ff,
        Refinery $refinery
    ): Group {
        return $ff->group([
            'field' => $ff->group([
                'access' => $ff->group(
                    [
                        'visible_in_registration' => $ff->hidden()
                            ->withDisabled($this->definition->requiredForcedTo() === true)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->isVisibleInRegistration() ? '1' : '0'),
                        'visible_in_personal_data' => $ff->hidden()
                            ->withDisabled($this->definition->visibleToUserForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->isVisibleToUser() ? '1' : '0'),
                        'visible_in_local_user_administration' => $ff->hidden()
                            ->withDisabled($this->definition->visibleInLocalUserAdministrationForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->isVisibleInLocalUserAdministration() ? '1' : '0'),
                        'visible_in_courses' => $ff->hidden()
                            ->withDisabled($this->definition->visibleInCoursesForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->isVisibleInCourses() ? '1' : '0'),
                        'visible_in_groups' => $ff->hidden()
                            ->withDisabled($this->definition->visibleInGroupsForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->isVisibleInGroups() ? '1' : '0'),
                        'visible_in_study_programmes' => $ff->hidden()
                            ->withDisabled($this->definition->visibleInStudyProgrammesForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->isVisibleInStudyProgrammes() ? '1' : '0'),
                        'changeable_by_user' => $ff->hidden()
                            ->withDisabled($this->definition->changeableByUserForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->isChangeableByUser() ? '1' : '0'),
                        'changeable_in_local_user_administration' => $ff->hidden()
                            ->withDisabled($this->definition->changeableInLocalUserAdministrationForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->isChangeableInLocalUserAdministration() ? '1' : '0')
                    ]
                ),
                'settings' => $ff->group(
                    [
                        'required' => $ff->hidden()
                            ->withDisabled($this->definition->requiredForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->isRequired() ? '1' : '0'),
                        'export' => $ff->hidden()
                            ->withDisabled($this->definition->exportForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->export() ? '1' : '0'),
                        'searchable' => $ff->hidden()
                            ->withDisabled($this->definition->searchableForcedTo() !== null)
                            ->withAdditionalTransformation($refinery->kindlyTo()->bool())
                            ->withValue($this->isSearchable() ? '1' : '0'),
                    ]
                )
            ])
        ]);
    }

    private function buildCustomTypeSelectionGroups(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery,
        array $custom_field_types
    ): array {
        return array_reduce(
            $custom_field_types,
            function (array $c, Custom\Type $v) use ($lng, $ff, $refinery): array {
                $edit_inputs = $v->getAdditionalEditFormInputs($lng, $ff, $refinery, null);
                $c[$v::class] = $ff->group(
                    $edit_inputs === null ? [] : ['data' => $edit_inputs],
                    $v->getLabel($lng)
                );
                return $c;
            },
            []
        );
    }

    private function buildLabelConstraint(
        Language $lng,
        Refinery $refinery,
        array $available_custom_fields
    ): Constraint {
        return $refinery->custom()->constraint(
            fn(string $label): bool => array_filter(
                $available_custom_fields,
                fn(self $v) => $v->getLabel($lng) === $label
                    && $v->getIdentifier() !== $this->getIdentifier()
            ) === [],
            $lng->txt('udf_name_already_exists')
        );
    }

    private function buildRequiredMustByVisibleInRegistrationConstraint(
        Language $lng,
        Refinery $refinery
    ): Constraint {
        return $refinery->custom()->constraint(
            static fn(array $vs): bool => !$vs['field']['settings']['required'] || $vs['field']['access']['visible_in_registration'],
            $lng->txt('invalid_visible_required_options_selected')
        );
    }

    private function buildCreateFieldTransformation(
        Refinery $refinery,
        array $custom_field_types
    ): Transformation {
        $cts = array_map(
            static fn(CustomType $v): string => $v::class,
            $custom_field_types
        );
        return $refinery->custom()->transformation(
            function (array $vs) use ($cts, $custom_field_types): self {
                $access = $vs['configuration']['field']['access'];
                $settings = $vs['configuration']['field']['settings'];
                $clone = clone $this;
                $clone->visible_in_registration = $this->definition->requiredForcedTo() === true
                    ? true
                    : $access['visible_in_registration'];
                $clone->visible_to_user = $this->definition->visibleToUserForcedTo()
                    ?? $access['visible_in_personal_data'];
                $clone->visible_in_local_user_administration = $this->definition->visibleInLocalUserAdministrationForcedTo()
                    ?? $access['visible_in_local_user_administration'];
                $clone->visible_in_courses = $this->definition->visibleInCoursesForcedTo()
                    ?? $access['visible_in_courses'];
                $clone->visible_in_groups = $this->definition->visibleInGroupsForcedTo()
                    ?? $access['visible_in_groups'];
                $clone->visible_in_study_programmes = $this->definition->visibleInStudyProgrammesForcedTo()
                    ?? $access['visible_in_study_programmes'];
                $clone->changeable_by_user = $this->definition->changeableByUserForcedTo()
                    ?? $access['changeable_by_user'];
                $clone->changeable_in_local_user_administration = $this->definition->changeableInLocalUserAdministrationForcedTo()
                    ?? $access['changeable_in_local_user_administration'];
                $clone->required = $this->definition->requiredForcedTo()
                    ?? $settings['required'];
                $clone->export = $this->definition->exportForcedTo()
                    ?? $settings['export'];
                $clone->searchable = $this->definition->searchableForcedTo()
                    ?? $settings['searchable'];

                if (!$clone->isCustom()) {
                    return $clone;
                }

                $definition = clone $this->definition
                    ->withLabel($vs['edit_field']['label'])
                    ->withSection(AvailableSections::tryFrom($vs['edit_field']['section']))
                    ->withAdditionalEditFormData(
                        $vs['edit_field']['data']
                            ?? $vs['edit_field']['type'][0]['data']
                            ?? null
                    );
                if ($definition->isUnspecific()
                    && ($field_type = array_search($vs['edit_field']['type'][0], $cts)) !== false) {
                    $definition = $definition->withType($custom_field_types[$field_type]);
                }
                $clone->definition = $definition;
                return $clone;
            }
        );
    }
}
