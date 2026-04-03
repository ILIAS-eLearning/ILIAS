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
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\Tools\InputsBuilderSession;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Refinery\Custom\Transformation as CustomTransformation;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\URLBuilder;

class EditGaps
{
    private const string SUB_ACTION_BACK_TO_EDIT_BASIC_PROPERTIES = 'bebp';
    private const string SUB_ACTION_SET_GAP_TYPES = 'sgt';
    private const string SUB_ACTION_BACK_TO_SET_GAP_TYPES = 'bsgt';
    public const string SUB_ACTION_JUMP_TO_SET_GAP_TYPES = 'jsgt';
    private const string SUB_ACTION_SET_ANSWER_OPTIONS = 'sao';
    public const string SUB_ACTION_BACK_TO_SET_ANSWER_OPTIONS = 'bsao';
    public const string SUB_ACTION_JUMP_TO_SET_ANSWER_OPTIONS = 'jsao';
    public const string SUB_ACTION_JUMP_TO_ASSIGN_POINTS = 'jap';
    private const string SUB_ACTION_PROCESS_SET_ANSWER_OPTIONS = 'psao';

    public function __construct(
        private readonly FileUpload $file_upload,
        private readonly PropertiesFactory $properties_factory,
        private readonly GapFactory $gap_factory
    ) {
    }

    public function do(
        Environment $environment,
        string $sub_action = self::SUB_ACTION_SET_GAP_TYPES
    ): EditForm|Async|Properties|string {
        $upload_handler = new UploadAnswerOptions(
            $this->file_upload,
            $environment
        );
        if ($upload_handler->can($sub_action)) {
            return $upload_handler->do($sub_action);
        }

        return match ($sub_action) {
            self::SUB_ACTION_SET_GAP_TYPES,
            self::SUB_ACTION_JUMP_TO_SET_GAP_TYPES
                => $this->buildGapTypesFormWithCarry(
                    $environment,
                    $environment->getAnswerFormProperties(),
                    $sub_action
                ),
            self::SUB_ACTION_BACK_TO_EDIT_BASIC_PROPERTIES
                => $this->backToEditBasicProperties(
                    $environment,
                    $sub_action
                ),
            self::SUB_ACTION_BACK_TO_SET_GAP_TYPES
                => $this->backToGapTypesForm(
                    $environment,
                    $sub_action
                ),
            self::SUB_ACTION_SET_ANSWER_OPTIONS
                => $this->forwardToAnswerOptionsForm(
                    $environment,
                    $sub_action
                ),
            self::SUB_ACTION_BACK_TO_SET_ANSWER_OPTIONS,
            self::SUB_ACTION_JUMP_TO_SET_ANSWER_OPTIONS
                => $this->buildAnswerOptionsFormWithCarry(
                    $environment,
                    $environment->getAnswerFormProperties(),
                    $sub_action
                ),
            self::SUB_ACTION_PROCESS_SET_ANSWER_OPTIONS
                => $this->processAnswerOptionsForm(
                    $environment,
                    $sub_action
                )
        };
    }

    private function backToEditBasicProperties(
        Environment $environment,
        string $sub_action
    ): EditForm|string {
        $processed_form = $this->processGapTypesForm(
            $environment,
            $sub_action
        );
        if ($processed_form instanceof EditForm) {
            return $processed_form;
        }

        return $processed_form->toCarry();
    }

    private function backToGapTypesForm(
        Environment $environment,
        string $sub_action
    ): EditForm {
        $processed_form = $this->processAnswerOptionsForm(
            $environment,
            $sub_action
        );
        if ($processed_form instanceof EditForm) {
            return $processed_form;
        }

        return $this->buildGapTypesFormWithCarry(
            $environment,
            $processed_form,
            $sub_action
        );
    }

    private function buildGapTypesFormWithCarry(
        Environment $environment,
        Properties $properties,
        string $sub_action
    ): EditForm {
        $inputs_builder = $this->buildInputsBuilderForTypesForm(
            $environment
        )->withCarry(
            $properties->toCarry()
        );

        $inputs_builder->persistCarry();

        return $this->buildGapTypesForm(
            $environment,
            $inputs_builder,
            $sub_action
        );
    }

    private function buildGapTypesForm(
        Environment $environment,
        InputsBuilderSession $inputs_builder,
        string $sub_action
    ): EditForm {
        /** @var \ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties $properties */
        $properties = $environment->getAnswerFormProperties();

        $inputs_builder->persistCarry();

        return $environment->getPresentationFactory()->getEditForm(
            $inputs_builder,
            $this->buildPostTarget(
                $environment,
                self::SUB_ACTION_SET_ANSWER_OPTIONS
            ),
            $sub_action === self::SUB_ACTION_JUMP_TO_SET_GAP_TYPES
            || $environment->getFormStartSubAction() === self::SUB_ACTION_JUMP_TO_SET_GAP_TYPES
                ? null
                : $this->buildPostTarget(
                    $environment,
                    self::SUB_ACTION_BACK_TO_EDIT_BASIC_PROPERTIES
                )
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
        Environment $environment,
        string $sub_action
    ): EditForm|Properties {
        $inputs_builder_for_types = $this->buildInputsBuilderForTypesForm(
            $environment
        );

        $properties = $inputs_builder_for_types->retrieveCarry(
            $this->buildRetrievePropertiesTransformation($environment)
        );

        $form = $this->buildGapTypesForm(
            $environment->withAnswerFormProperties($properties),
            $inputs_builder_for_types,
            $sub_action
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
        Environment $environment,
        string $sub_action
    ): EditForm {
        $processed_form = $this->processGapTypesForm(
            $environment,
            $sub_action
        );
        if ($processed_form instanceof EditForm) {
            return $processed_form;
        }

        return $this->buildAnswerOptionsFormWithCarry(
            $environment,
            $processed_form,
            $sub_action
        );
    }

    private function buildAnswerOptionsFormWithCarry(
        Environment $environment,
        Properties $properties,
        string $sub_action
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
            $inputs_builder,
            $sub_action
        );
    }

    private function buildAnswerOptionsForm(
        Environment $environment,
        InputsBuilderSession $inputs_builder,
        string $sub_action
    ): EditForm {
        $properties = $environment->getAnswerFormProperties();
        return $environment->getPresentationFactory()->getEditForm(
            $inputs_builder,
            $this->buildPostTarget(
                $environment,
                self::SUB_ACTION_PROCESS_SET_ANSWER_OPTIONS
            ),
            $sub_action === self::SUB_ACTION_JUMP_TO_SET_ANSWER_OPTIONS
            || $environment->getFormStartSubAction() === self::SUB_ACTION_JUMP_TO_SET_ANSWER_OPTIONS
                ? null
                : $this->buildPostTarget(
                    $environment,
                    self::SUB_ACTION_BACK_TO_SET_GAP_TYPES
                )
        )->withIsFinalStep(true)
        ->withContentBeforeForm(
            $properties->getClozeText()->buildPanelForEditing(
                $environment->getUIFactory(),
                $environment->getLanguage(),
                $properties->getGaps(),
                $properties->getLegacyClozeText()
            )
        );
    }

    private function processAnswerOptionsForm(
        Environment $environment,
        string $sub_action
    ): EditForm|Properties {
        $inputs_builder_for_options = $this->buildInputsBuilderForAnswerOptionsForm(
            $environment,
            $environment->getAnswerFormProperties()
        );

        $form = $this->buildAnswerOptionsForm(
            $environment,
            $inputs_builder_for_options,
            $sub_action
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
                            $this->file_upload,
                            $environment,
                            $properties_from_carry
                        );
                }
            )
        );
    }

    private function buildPostTarget(
        Environment $environment,
        string $next_step
    ): URLBuilder {
        return $environment
            ->withSubActionParameter($next_step)
            ->getUrlBuilder();
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
