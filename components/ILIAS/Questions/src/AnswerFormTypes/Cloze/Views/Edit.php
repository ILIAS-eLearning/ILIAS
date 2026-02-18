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

use ILIAS\Questions\AnswerForm\Views\Edit as EditViewInterface;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Factory as PropertiesFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Factory as ClozeTextFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\EditOverview;
use ILIAS\Questions\Presentation\Layout\InputsBuilderSession;
use ILIAS\Questions\Presentation\Layout\Renderable;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Modal\InterruptiveItem\Standard as InterruptiveItem;
use ILIAS\UI\URLBuilder;

class Edit implements EditViewInterface
{
    private const string STEP_EDIT_BASIC_PROPERTIES = 'ebp';
    private const string STEP_PROCESS_BASIC_PROPERTIES = 'pbp';
    private const string STEP_ADD_LEGACY_TEXT_BASIC_PROPERTIES = 'altbp';
    private const string STEP_CONFIRMED_GAP_REMOVAL = 'cgr';

    public function __construct(
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly \ilToolbarGUI $toolbar,
        private readonly Refinery $refinery,
        private readonly HTTPServices $http,
        private readonly PropertiesFactory $properties_factory,
        private readonly ClozeTextFactory $cloze_text_factory,
        private readonly EditGaps $edit_gaps
    ) {
    }

    #[\Override]
    public function create(
        Environment $environment
    ): EditForm|Properties {
        $step = $environment->getStep();

        return match($step) {
            '' => $this->startEditing($environment),
            self::STEP_PROCESS_BASIC_PROPERTIES => $this->processBasicEditingForm(
                $environment
            ),
            default => $this->forwardCmdToEditGaps(
                $environment,
                $step
            )
        };
    }

    #[\Override]
    public function edit(
        Environment $environment
    ): EditOverview|EditForm|Properties {
        $step = $environment->getStep();

        $combinations = $environment->getAnswerFormProperties()->getCombinations();
        if ($combinations->areCombinationsEnabled()) {
            $combinations->getEditView(
                $this->ui_factory,
                $this->toolbar,
                $this->refinery,
                $this->lng,
                $this->http
            )->addCombinationsSubTab($environment);
        }

        if ($step === '') {
            return $environment->getPresentationFactory()->getEditOverview(
                $environment,
                $environment->withStepParameter(self::STEP_EDIT_BASIC_PROPERTIES)
                    ->getUrlBuilder()
            );
        }

        $environment->setEditAnswerFormBackTarget();

        return match ($step) {
            self::STEP_EDIT_BASIC_PROPERTIES => $this->startEditing($environment),
            self::STEP_ADD_LEGACY_TEXT_BASIC_PROPERTIES =>
                $this->addLegacyTextToBasicProperties($environment),
            self::STEP_CONFIRMED_GAP_REMOVAL,
            self::STEP_PROCESS_BASIC_PROPERTIES => $this->processBasicEditingForm(
                $environment->withPreservedTableRowIdsParameter()
            ),
            default => $this->forwardCmdToEditGaps(
                $environment->withPreservedTableRowIdsParameter(),
                $step
            )
        };
    }

    #[\Override]
    public function other(
        Environment $environment
    ): Async|Renderable|Properties {
        return $environment
            ->getAnswerFormProperties()
            ->getCombinations()->getEditView(
                $this->ui_factory,
                $this->toolbar,
                $this->refinery,
                $this->lng,
                $this->http
            )->show($environment);
    }

    #[\Override]
    public function getFinishEditingUrl(
        Environment $environment
    ): URLBuilder {
        return $environment->getUrlBuilder();
    }

    private function startEditing(
        Environment $environment
    ): EditForm {
        $input_builder = $this->buildInputsBuilderForBasicInputs(
            $environment,
            false
        );
        $input_builder->resetCarry();

        return $this->buildBasicEditingForm(
            $environment,
            $input_builder,
            false
        );
    }

    private function forwardCmdToEditGaps(
        Environment $environment,
        string $step
    ): EditForm|Properties {
        $processed_form = $this->edit_gaps->call($environment, $step);
        if (is_string($processed_form)) {
            $inputs_builder = $this->buildInputsBuilderForBasicInputs(
                $environment,
                false,
                $processed_form
            );

            $inputs_builder->persistCarry();

            return $this->buildBasicEditingForm(
                $environment,
                $inputs_builder,
                false
            );
        }

        return $processed_form;
    }

    private function buildBasicEditingForm(
        Environment $environment,
        InputsBuilderSession $inputs_builder,
        bool $add_legacy_cloze_text_to_input
    ): EditForm {
        $editing_form = $this->buildEditFormForBasicInputs(
            $environment,
            $inputs_builder
        );

        /** @var \ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties $properties */
        $properties = $environment->getAnswerFormProperties();
        if (!$add_legacy_cloze_text_to_input
            && $properties->getLegacyClozeText() !== ''
            && $properties->getClozeText()->getRawRepresentation() === '') {
            return $editing_form->withInsertLegacyTextsButton(
                $environment->withStepParameter(
                    self::STEP_ADD_LEGACY_TEXT_BASIC_PROPERTIES
                )->getUrlBuilder()
            );
        }

        return $editing_form;
    }

    private function addLegacyTextToBasicProperties(
        Environment $environment
    ): EditForm {
        $inputs_builder = $this->buildInputsBuilderForBasicInputs(
            $environment,
            true
        );

        $inputs_builder->persistCarry();

        return $this->buildBasicEditingForm(
            $environment,
            $inputs_builder,
            true
        );
    }

    private function processBasicEditingForm(
        Environment $environment
    ): EditForm|Properties {
        $inputs_builder = $this->buildInputsBuilderForBasicInputs(
            $environment,
            false,
        );

        $form = $this->buildBasicEditingForm(
            $environment,
            $inputs_builder,
            false
        )->withRequest($this->http->request());

        /** @var \ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties $data */
        $data = $form->getData();
        if ($data === null) {
            $inputs_builder->persistCarry();
            return $form;
        }

        $new_gaps = $data->getGaps();
        $old_gaps = $environment->getAnswerFormProperties()->getGaps();

        if ($environment->getStep() !== self::STEP_CONFIRMED_GAP_REMOVAL) {
            $removed_gaps = $new_gaps->getRemovedGaps($old_gaps);
            if ($removed_gaps !== []) {
                return $form->withConfirmation(
                    $this->buildRemovedGapsConfirmation(
                        $environment,
                        $removed_gaps
                    )
                );
            }
        }

        if ($new_gaps->getAddedGaps($old_gaps) === []) {
            return $data;
        }

        return $this->edit_gaps->call(
            $environment->withAnswerFormProperties(
                $data->withGaps(
                    $data->getGaps()->withMarkedIncompleteGaps()
                )
            )
        );
    }

    private function buildEditFormForBasicInputs(
        Environment $environment,
        InputsBuilderSession $inputs_builder
    ): EditForm {
        return $environment->getPresentationFactory()->getEditForm(
            $inputs_builder,
            $environment
                ->withStepParameter(self::STEP_PROCESS_BASIC_PROPERTIES)
                ->getUrlBuilder(),
            null,
            false
        );
    }

    private function buildInputsBuilderForBasicInputs(
        Environment $environment,
        bool $add_legacy_cloze_text_to_input,
        ?string $carry = null
    ): InputsBuilderSession {
        $inputs_builder = $environment->getPresentationFactory()
            ->getSessionBasedInputsBuilder(
                $environment->getAnswerFormId()->toString(),
                $this->refinery->custom()->transformation(
                    fn(?string $carry): Section => $this->properties_factory
                        ->fromCarry(
                            $environment->getAnswerFormProperties(),
                            $carry
                        )->buildBasicEditingInputs(
                            $this->lng,
                            $this->ui_factory->input()->field(),
                            $this->refinery,
                            $this->properties_factory,
                            $this->cloze_text_factory,
                            $add_legacy_cloze_text_to_input
                        )
                )
            );

        if ($carry === null) {
            return $inputs_builder;
        }

        return $inputs_builder->withCarry($carry);
    }

    /**
     * @param array<\ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap> $removed_gaps
     */
    private function buildRemovedGapsConfirmation(
        Environment $environment,
        array $removed_gaps
    ): InterruptiveModal {
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt('confirm_remove_gaps'),
            $environment->withStepParameter(
                self::STEP_CONFIRMED_GAP_REMOVAL
            )->getUrlBuilder()->buildURI()->__toString()
        )->withAffectedItems(
            array_map(
                fn(Gap $v): InterruptiveItem => $this->ui_factory->modal()
                    ->interruptiveItem()->standard(
                        $v->getAnswerInputId()->toString(),
                        $v->buildShortenedGapName()
                    ),
                $removed_gaps
            )
        );
    }
}
