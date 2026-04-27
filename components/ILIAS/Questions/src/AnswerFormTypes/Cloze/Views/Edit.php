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

use ILIAS\Questions\AnswerForm\Capabilities\Marking\Marking;
use ILIAS\Questions\AnswerForm\Views\Edit as EditViewInterface;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Factory as PropertiesFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Factory as ClozeTextFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Edit as EditGaps;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Definitions\ForImmediateStorage;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\EditOverview;
use ILIAS\Questions\Presentation\Layout\Tools\InputsBuilderSession;
use ILIAS\Questions\Presentation\Layout\Viewable;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Modal\InterruptiveItem\Standard as InterruptiveItem;

class Edit implements EditViewInterface
{
    private const string SUB_ACTION_EDIT_BASIC_PROPERTIES = 'ebp';
    private const string SUB_ACTION_PROCESS_BASIC_PROPERTIES = 'pbp';
    private const string SUB_ACTION_ADD_LEGACY_TEXT_BASIC_PROPERTIES = 'altbp';
    private const string SUB_ACTION_CONFIRMED_GAP_REMOVAL = 'cgr';

    public function __construct(
        private readonly PropertiesFactory $properties_factory,
        private readonly ClozeTextFactory $cloze_text_factory,
        private readonly EditGaps $edit_gaps
    ) {
    }

    #[\Override]
    public function create(
        Environment $environment
    ): EditForm|Async|Properties {
        $sub_action = $environment->getSubAction();

        return match($sub_action) {
            '' => $this->startEditing($environment),
            self::SUB_ACTION_PROCESS_BASIC_PROPERTIES => $this->processBasicEditingForm(
                $environment
            ),
            default => $this->forwardCmdToEditGaps(
                $environment
            )
        };
    }

    #[\Override]
    public function edit(
        Environment $environment
    ): EditOverview|EditForm|Async|ForImmediateStorage|Properties {
        $sub_action = $environment->getSubAction();

        $combinations = $environment->getAnswerFormProperties()->getCombinations();
        if ($environment->isCapabilityRequired(Marking::class)
            && $combinations->areCombinationsEnabled()) {
            $combinations->getEditView()->addCombinationsSubTab($environment);
        }

        if ($sub_action === '') {
            return $environment->getPresentationFactory()->getEditOverview(
                $environment,
                $environment->withSubActionParameter(self::SUB_ACTION_EDIT_BASIC_PROPERTIES)
                    ->getUrlBuilder()
            );
        }

        $environment->setEditAnswerFormBackTarget();

        return match ($sub_action) {
            self::SUB_ACTION_EDIT_BASIC_PROPERTIES => $this->startEditing($environment),
            self::SUB_ACTION_ADD_LEGACY_TEXT_BASIC_PROPERTIES =>
                $this->addLegacyTextToBasicProperties($environment),
            self::SUB_ACTION_CONFIRMED_GAP_REMOVAL,
            self::SUB_ACTION_PROCESS_BASIC_PROPERTIES => $this->processBasicEditingForm(
                $environment->withPreservedTableRowIdsParameter()
            ),
            default => $this->forwardCmdToEditGaps(
                $environment
                    ->withPreservedTableRowIdsParameter()
                    ->withPreservedFormStartSubActionParameter()
            )
        };
    }

    #[\Override]
    public function other(
        Environment $environment
    ): Async|Viewable|Properties {
        $from_other = $environment
            ->getAnswerFormProperties()
            ->getCombinations()->getEditView()->show($environment);

        if ($from_other === null) {
            return $this->edit(
                $environment->withDefaultSubAction()
            );
        }

        return $from_other;
    }

    #[\Override]
    public function backToLastEditCommand(
        Environment $environment
    ): EditForm {
        return $this->edit_gaps->do(
            $environment,
            EditGaps::SUB_ACTION_BACK_TO_SET_ANSWER_OPTIONS
        );
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
        Environment $environment
    ): EditForm|Async|Properties {
        $processed_form = $this->edit_gaps->do(
            $environment,
            $environment->getSubAction()
        );
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
                $environment->withSubActionParameter(
                    self::SUB_ACTION_ADD_LEGACY_TEXT_BASIC_PROPERTIES
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
    ): EditForm|Async|ForImmediateStorage|Properties {
        $inputs_builder = $this->buildInputsBuilderForBasicInputs(
            $environment,
            false,
        );

        $form = $this->buildBasicEditingForm(
            $environment,
            $inputs_builder,
            false
        )->withRequest($environment->getHttpServices()->request());

        /** @var \ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties $data */
        $data = $form->getData();
        if ($data === null) {
            $inputs_builder->persistCarry();
            return $form;
        }

        $new_gaps = $data->getGaps();
        $old_gaps = $environment->getAnswerFormProperties()->getGaps();

        if ($environment->getSubAction() !== self::SUB_ACTION_CONFIRMED_GAP_REMOVAL) {
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
            return new ForImmediateStorage($data);
        }

        return $this->edit_gaps->do(
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
                ->withSubActionParameter(self::SUB_ACTION_PROCESS_BASIC_PROPERTIES)
                ->getUrlBuilder(),
            null
        );
    }

    private function buildInputsBuilderForBasicInputs(
        Environment $environment,
        bool $add_legacy_cloze_text_to_input,
        ?string $carry = null
    ): InputsBuilderSession {
        $inputs_builder = $environment->getPresentationFactory()
            ->getSessionBasedInputsBuilder(
                $environment->getRefinery()->custom()->transformation(
                    fn(?string $carry): Section => $this->properties_factory
                        ->fromCarry(
                            $environment->getAnswerFormProperties(),
                            $carry
                        )->buildBasicEditingInputs(
                            $environment,
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
        return $environment->getUIFactory()->modal()->interruptive(
            $environment->getLanguage()->txt('confirm'),
            $environment->getLanguage()->txt('confirm_remove_gaps'),
            $environment->withSubActionParameter(
                self::SUB_ACTION_CONFIRMED_GAP_REMOVAL
            )->getUrlBuilder()->buildURI()->__toString()
        )->withAffectedItems(
            array_map(
                fn(Gap $v): InterruptiveItem => $environment->getUIFactory()
                    ->modal()->interruptiveItem()->standard(
                        $v->getAnswerInputId()->toString(),
                        $v->buildShortenedGapName()
                    ),
                $removed_gaps
            )
        );
    }
}
