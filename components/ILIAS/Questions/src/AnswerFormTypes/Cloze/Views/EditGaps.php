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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Views;

use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Factory as PropertiesFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Factory as GapFactory;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\InputsBuilderSession;
use ILIAS\Refinery\Custom\Transformation as CustomTransformation;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\URLBuilder;

class EditGaps
{
    private const string STEP_BACK_TO_EDIT_BASIC_PROPERTIES = 'bebp';
    private const string STEP_SET_GAP_TYPES = 'sgt';
    private const string STEP_BACK_TO_SET_GAP_TYPES = 'bsgt';
    public const string STEP_JUMP_TO_SET_GAP_TYPES = 'jsgt';
    private const string STEP_SET_ANSWER_OPTIONS = 'sao';
    private const string STEP_BACK_TO_SET_ANSWER_OPTIONS = 'bsao';
    public const string STEP_JUMP_TO_SET_ANSWER_OPTIONS = 'jsao';
    private const string STEP_ASSIGN_POINTS = 'ap';
    public const string STEP_JUMP_TO_ASSIGN_POINTS = 'jap';
    private const string STEP_SAVE = 's';

    private string $step;
    private ?string $start_step;

    public function __construct(
        private readonly PropertiesFactory $properties_factory,
        private readonly GapFactory $gap_factory
    ) {
    }

    public function call(
        Environment $environment,
        string $step = self::STEP_SET_GAP_TYPES
    ): EditForm|Properties|string {
        $step_array = explode('_', $step);
        $this->step = $step_array[0];
        $this->start_step = $this->determineStartStepFromStep(
            $step_array[1] ?? null
        );

        return match ($this->step) {
            self::STEP_SET_GAP_TYPES,
            self::STEP_JUMP_TO_SET_GAP_TYPES
                => $this->buildGapTypesFormWithCarry(
                    $environment,
                    $environment->getAnswerFormProperties()
                ),
            self::STEP_BACK_TO_EDIT_BASIC_PROPERTIES
                => $this->backToEditBasicProperties(
                    $environment
                ),
            self::STEP_BACK_TO_SET_GAP_TYPES
                => $this->backToGapTypesForm(
                    $environment
                ),
            self::STEP_SET_ANSWER_OPTIONS
                => $this->forwardToAnswerOptionsForm(
                    $environment
                ),
            self::STEP_JUMP_TO_SET_ANSWER_OPTIONS
                => $this->buildAnswerOptionsFormWithCarry(
                    $environment,
                    $environment->getAnswerFormProperties()
                ),
            self::STEP_BACK_TO_SET_ANSWER_OPTIONS
                => $this->backToSetAnswerOptionsForm(
                    $environment
                ),
            self::STEP_ASSIGN_POINTS
                => $this->forwardToAssignPointsForm(
                    $environment
                ),
            self::STEP_JUMP_TO_ASSIGN_POINTS
                => $this->buildAssignPointsFormWithCarry(
                    $environment,
                    $environment->getAnswerFormProperties()
                ),
            self::STEP_SAVE
                => $this->processAssignPointsForm(
                    $environment
                )
        };
    }

    private function backToEditBasicProperties(
        Environment $environment
    ): EditForm|string {
        $processed_form = $this->processGapTypesForm($environment);
        if ($processed_form instanceof EditForm) {
            return $processed_form;
        }

        return $processed_form->toCarry();
    }

    private function backToGapTypesForm(
        Environment $environment
    ): EditForm {
        $processed_form = $this->processAnswerOptionsForm($environment);
        if ($processed_form instanceof EditForm) {
            return $processed_form;
        }

        return $this->buildGapTypesFormWithCarry(
            $environment,
            $processed_form
        );
    }

    private function buildGapTypesFormWithCarry(
        Environment $environment,
        Properties $properties
    ): EditForm {
        $inputs_builder = $this->buildInputsBuilderForTypesForm(
            $environment
        )->withCarry(
            $properties->toCarry()
        );

        $inputs_builder->persistCarry();

        return $this->buildGapTypesForm(
            $environment,
            $inputs_builder
        );
    }

    private function buildGapTypesForm(
        Environment $environment,
        InputsBuilderSession $inputs_builder
    ): EditForm {
        /** @var \ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties $properties */
        $properties = $environment->getAnswerFormProperties();

        $inputs_builder->persistCarry();

        return $environment->getPresentationFactory()->getEditForm(
            $inputs_builder,
            $this->buildPostTarget(
                $environment,
                self::STEP_SET_ANSWER_OPTIONS
            ),
            $this->step === self::STEP_JUMP_TO_SET_GAP_TYPES
            || $this->step === $this->start_step
                ? null
                : $this->buildPostTarget(
                    $environment,
                    self::STEP_BACK_TO_EDIT_BASIC_PROPERTIES
                ),
            false
        )->withContentBeforeForm(
            $properties->getClozeText()->buildPanelForEditing(
                $environment->getUIFactory(),
                $environment->getLanguage(),
                $properties->getGaps(),
                $properties->getLegacyClozeText()
            )
        );
    }

    private function processGapTypesForm(
        Environment $environment
    ): EditForm|Properties {
        $inputs_builder_for_types = $this->buildInputsBuilderForTypesForm(
            $environment
        );

        $properties = $inputs_builder_for_types->retrieveCarry(
            $this->buildRetrievePropertiesTransformation($environment)
        );

        $form = $this->buildGapTypesForm(
            $environment->withAnswerFormProperties($properties),
            $inputs_builder_for_types
        )->withRequest($environment->getHttpServices()->request());

        $data = $form->getData();
        if ($data === null) {
            $inputs_builder_for_types->persistCarry();
            return $form;
        }

        return $data;
    }

    private function buildInputsBuilderForTypesForm(
        Environment $environment
    ): InputsBuilderSession {
        return $environment->getPresentationFactory()->getSessionBasedInputsBuilder(
            $environment->getAnswerFormProperties()->getAnswerFormId()->toString(),
            $environment->getRefinery()->custom()->transformation(
                function (?string $carry) use ($environment): Section {
                    $properties_from_carry = $this->properties_factory
                    ->fromCarry(
                        $environment->getAnswerFormProperties(),
                        $carry
                    );
                    return $properties_from_carry->getGaps()->buildGapsTypeInputs(
                        $environment->getLanguage(),
                        $environment->getUIFactory()->input()->field(),
                        $this->gap_factory->getAvailableGapTypesOptionsArray(
                            $environment->getLanguage()
                        ),
                        $properties_from_carry,
                        $environment->isInCreationContext(),
                        $environment->getTableRowIds()
                    );
                }
            )
        );
    }

    private function forwardToAnswerOptionsForm(
        Environment $environment
    ): EditForm {
        $processed_form = $this->processGapTypesForm($environment);
        if ($processed_form instanceof EditForm) {
            return $processed_form;
        }

        return $this->buildAnswerOptionsFormWithCarry(
            $environment,
            $processed_form
        );
    }

    private function backToSetAnswerOptionsForm(
        Environment $environment
    ): EditForm {
        $processed_form = $this->processAssignPointsForm($environment);
        if ($processed_form instanceof EditForm) {
            return $processed_form;
        }

        return $this->buildAnswerOptionsFormWithCarry(
            $environment,
            $processed_form
        );
    }

    private function buildAnswerOptionsFormWithCarry(
        Environment $environment,
        Properties $properties
    ): EditForm {
        $inputs_builder = $this->buildInputsBuilderForAnswerOptionsForm(
            $environment,
            $properties,
        )->withCarry(
            $properties->toCarry()
        );

        $inputs_builder->persistCarry();

        return $this->buildAnswerOptionsForm(
            $environment->withAnswerFormProperties($properties),
            $inputs_builder
        );
    }

    private function buildAnswerOptionsForm(
        Environment $environment,
        InputsBuilderSession $inputs_builder
    ): EditForm {
        $properties = $environment->getAnswerFormProperties();
        return $environment->getPresentationFactory()->getEditForm(
            $inputs_builder,
            $this->buildPostTarget(
                $environment,
                self::STEP_ASSIGN_POINTS
            ),
            $this->step === self::STEP_JUMP_TO_SET_ANSWER_OPTIONS
            || $this->step === $this->start_step
                ? null
                : $this->buildPostTarget(
                    $environment,
                    self::STEP_BACK_TO_SET_GAP_TYPES
                ),
            false
        )->withContentBeforeForm(
            $properties->getClozeText()->buildPanelForEditing(
                $environment->getUIFactory(),
                $environment->getLanguage(),
                $properties->getGaps(),
                $properties->getLegacyClozeText()
            )
        );
    }

    private function processAnswerOptionsForm(
        Environment $environment
    ): EditForm|Properties {
        $inputs_builder_for_options = $this->buildInputsBuilderForAnswerOptionsForm(
            $environment,
            $environment->getAnswerFormProperties()
        );

        $form = $this->buildAnswerOptionsForm(
            $environment,
            $inputs_builder_for_options
        )->withRequest($environment->getHttpServices()->request());

        $data = $form->getData();
        if ($data === null) {
            $inputs_builder_for_options->persistCarry();
            return $form;
        }

        return $data;
    }

    private function buildInputsBuilderForAnswerOptionsForm(
        Environment $environment,
        Properties $properties
    ): InputsBuilderSession {
        return $environment->getPresentationFactory()->getSessionBasedInputsBuilder(
            $properties->getAnswerFormId()->toString(),
            $environment->getRefinery()->custom()->transformation(
                function (?string $carry) use (
                    $environment,
                    $properties
                ): Section {
                    $properties_from_carry = $this->properties_factory
                        ->fromCarry(
                            $properties,
                            $carry
                        );
                    return $properties_from_carry->getGaps()
                        ->buildAnswerOptionsInputs(
                            $environment->getLanguage(),
                            $environment->getUIFactory()->input()->field(),
                            $properties_from_carry,
                            $environment->isInCreationContext(),
                            $environment->getTableRowIds()
                        );
                }
            )
        );
    }

    private function forwardToAssignPointsForm(
        Environment $environment
    ): EditForm {
        $processed_form = $this->processAnswerOptionsForm($environment);
        if ($processed_form instanceof EditForm) {
            return $processed_form;
        }

        return $this->buildAssignPointsFormWithCarry(
            $environment,
            $processed_form
        );
    }

    private function buildAssignPointsFormWithCarry(
        Environment $environment,
        Properties $properties
    ): EditForm {
        $inputs_builder_for_points = $this->buildInputsBuilderForPointsForm(
            $environment,
            $properties
        )->withCarry(
            $properties->toCarry()
        );

        $inputs_builder_for_points->persistCarry();

        return $this->buildAssignPointsForm(
            $environment->withAnswerFormProperties($properties),
            $inputs_builder_for_points
        );
    }

    private function buildAssignPointsForm(
        Environment $environment,
        InputsBuilderSession $inputs_builder
    ): EditForm {
        $properties = $environment->getAnswerFormProperties();
        return $environment->getPresentationFactory()->getEditForm(
            $inputs_builder,
            $this->buildPostTarget(
                $environment,
                self::STEP_SAVE
            ),
            $this->step === self::STEP_JUMP_TO_ASSIGN_POINTS
                ? null
                : $this->buildPostTarget(
                    $environment,
                    self::STEP_BACK_TO_SET_ANSWER_OPTIONS
                ),
            true
        )->withContentBeforeForm(
            $properties->getClozeText()->buildPanelForEditing(
                $environment->getUIFactory(),
                $environment->getLanguage(),
                $properties->getGaps(),
                $properties->getLegacyClozeText()
            )
        );
    }

    private function processAssignPointsForm(
        Environment $environment
    ): EditForm|Properties {
        $inputs_builder_for_points = $this->buildInputsBuilderForPointsForm(
            $environment,
            $environment->getAnswerFormProperties()
        );

        $form = $this->buildAssignPointsForm(
            $environment,
            $inputs_builder_for_points
        )->withRequest($environment->getHttpServices()->request());

        $data = $form->getData();
        if ($data === null) {
            $inputs_builder_for_points->persistCarry();
            return $form;
        }

        return $data;
    }

    private function buildInputsBuilderForPointsForm(
        Environment $environment,
        Properties $properties
    ): InputsBuilderSession {
        return $environment->getPresentationFactory()->getSessionBasedInputsBuilder(
            $properties->getAnswerFormId()->toString(),
            $environment->getRefinery()->custom()->transformation(
                function (?string $carry) use (
                    $environment,
                    $properties
                ): Section {
                    $properties_from_carry = $this->properties_factory
                        ->fromCarry(
                            $properties,
                            $carry
                        );
                    return $properties_from_carry->getGaps()
                        ->buildPointInputs(
                            $environment->getLanguage(),
                            $environment->getUIFactory()->input()->field(),
                            $properties_from_carry,
                            $environment->isInCreationContext(),
                            $environment->getTableRowIds()
                        );
                }
            )
        );
    }

    private function buildPostTarget(
        Environment $environment,
        string $next_step
    ): URLBuilder {
        if ($this->start_step !== null) {
            $next_step = "{$next_step}_{$this->start_step}";
        }

        return $environment->withStepParameter($next_step)->getUrlBuilder();
    }

    private function determineStartStepFromStep(
        ?string $start_step_from_get
    ): ?string {
        if ($start_step_from_get !== null) {
            return $start_step_from_get;
        }

        if ($this->step === self::STEP_JUMP_TO_SET_GAP_TYPES) {
            return self::STEP_BACK_TO_SET_GAP_TYPES;
        }

        if ($this->step === self::STEP_JUMP_TO_SET_ANSWER_OPTIONS) {
            return self::STEP_BACK_TO_SET_ANSWER_OPTIONS;
        }

        return null;
    }

    private function buildRetrievePropertiesTransformation(
        Environment $environment
    ): CustomTransformation {
        return $environment->getRefinery()->custom()->transformation(
            fn(?string $carry): Properties => $this->properties_factory
                ->fromCarry(
                    $environment->getAnswerFormProperties(),
                    $carry
                )
        );
    }
}
