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
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Factory as GapFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gap;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\EditOverview;
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
    private const string STEP_ADD_LEGACY_TEXT_BASIC_PROPERTIES = 'altbp';
    private const string STEP_CONFIRMED_GAP_REMOVAL = 'cgr';
    private const string STEP_SET_GAP_TYPES = 'sgt';
    public const string STEP_JUMP_TO_SET_GAP_TYPES = 'jsgt';
    private const string STEP_SET_ANSWER_OPTIONS = 'sao';
    public const string STEP_JUMP_TO_SET_ANSWER_OPTIONS = 'jsao';
    private const string STEP_SET_POINTS = 'sp';
    public const string STEP_JUMP_TO_SET_POINTS = 'jsp';
    private const string STEP_SAVE = 's';

    public function __construct(
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly \ilToolbarGUI $toolbar,
        private readonly Refinery $refinery,
        private readonly HTTPServices $http,
        private readonly PropertiesFactory $properties_factory,
        private readonly ClozeTextFactory $cloze_text_factory,
        private readonly GapFactory $gap_factory
    ) {
    }

    #[\Override]
    public function create(
        Environment $environment
    ): EditForm|Properties {
        $step = $environment->getStep();

        return match($step) {
            '' => $this->buildBasicEditingForm($environment, false),
            default => $this->callIntermediateStep($environment, $step)
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
                    ->buildURI()
            );
        }

        $environment->setEditAnswerFormBackTarget();

        return match ($step) {
            self::STEP_EDIT_BASIC_PROPERTIES =>
                $this->buildBasicEditingForm($environment, false),
            self::STEP_ADD_LEGACY_TEXT_BASIC_PROPERTIES =>
                $this->addLegacyTextToBasicProperties($environment),
            default => $this->callIntermediateStep($environment, $step)
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

    private function callIntermediateStep(
        Environment $environment,
        string $step
    ): EditForm|Properties {
        $initialized_environment = $environment->withPreservedTableRowIdsParameter();

        return match ($step) {
            self::STEP_SET_GAP_TYPES,
            self::STEP_CONFIRMED_GAP_REMOVAL => $this->processBasicEditingForm(
                $initialized_environment
            ),
            self::STEP_JUMP_TO_SET_GAP_TYPES => $this->buildGapTypesForm(
                $initialized_environment
            ),
            self::STEP_SET_ANSWER_OPTIONS => $this->processGapTypesForm(
                $initialized_environment
            ),
            self::STEP_JUMP_TO_SET_ANSWER_OPTIONS => $this->buildAnswerOptionsForm(
                $initialized_environment
            ),
            self::STEP_SET_POINTS => $this->processAnswerOptionsForm(
                $initialized_environment
            ),
            self::STEP_JUMP_TO_SET_POINTS => $this->buildAssignPointsForm(
                $initialized_environment
            ),
            self::STEP_SAVE => $this->processAssignPointsForm(
                $initialized_environment
            )
        };
    }

    private function buildBasicEditingForm(
        Environment $environment,
        bool $add_legacy_cloze_text_to_input
    ): EditForm {

        /** @var \ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties $answer_form_properties */
        $answer_form_properties = $environment->getAnswerFormProperties();

        $editing_form = $environment->getPresentationFactory()->getEditForm(
            $environment->withStepParameter(self::STEP_SET_GAP_TYPES),
            $environment->getPresentationFactory()->getInputsBuilder(
                $this->refinery->custom()->transformation(
                    fn(null $carry) => $answer_form_properties->buildBasicEditingInputs(
                        $this->lng,
                        $this->ui_factory->input()->field(),
                        $this->refinery,
                        $this->properties_factory,
                        $this->cloze_text_factory,
                        $add_legacy_cloze_text_to_input
                    )
                )
            ),
            false
        );

        if (!$add_legacy_cloze_text_to_input
            && $answer_form_properties->getLegacyClozeText() !== ''
            && $answer_form_properties->getClozeText()->getRawRepresentation() === '') {
            return $editing_form->withInsertLegacyTextsButton(
                $environment->withStepParameter(
                    self::STEP_ADD_LEGACY_TEXT_BASIC_PROPERTIES
                )->getUrlBuilder()
                ->buildURI()
            );
        }

        return $editing_form;
    }

    private function addLegacyTextToBasicProperties(
        Environment $environment
    ): EditForm {
        return $this->buildBasicEditingForm(
            $environment,
            true
        );
    }

    private function processBasicEditingForm(
        Environment $environment
    ): EditForm|Properties {
        $form = $this->buildBasicEditingForm(
            $environment,
            false
        )->withRequest($this->http->request());

        $data = $form->getData();
        if ($data === null) {
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

        return $this->buildGapTypesForm(
            $environment->withAnswerFormProperties($data)
        );
    }

    private function buildGapTypesForm(
        Environment $environment
    ): EditForm {
        /** @var \ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties $properties */
        $properties = $environment->getAnswerFormProperties();
        $ff = $this->ui_factory->input()->field();
        return $environment->getPresentationFactory()->getEditForm(
            $environment->withStepParameter(self::STEP_SET_ANSWER_OPTIONS),
            $environment->getPresentationFactory()->getInputsBuilder(
                $this->refinery->custom()->transformation(
                    fn(?string $carry) => $properties
                        ->withValuesFromQueryCarry($carry)
                        ->getGaps()
                        ->buildGapsTypeInputs(
                            $this->lng,
                            $ff,
                            $this->refinery,
                            $this->gap_factory->getAvailableGapTypesOptionsArray($this->lng),
                            $environment->getTableRowIds()
                        )
                )
            )->withCarry(
                $properties->buildQueryCarry()
            ),
            false
        )->withContentBeforeForm(
            $properties->getClozeText()->buildPanelForEditing(
                $this->ui_factory,
                $this->lng,
                $properties->getGaps(),
                $properties->getLegacyClozeText()
            )
        );
    }

    private function processGapTypesForm(
        Environment $environment
    ): EditForm {
        $form = $this->buildGapTypesForm(
            $environment
        )->withRequest($this->http->request());

        $data = $form->getData();
        return $data === null
            ? $form
            : $this->buildAnswerOptionsForm(
                $environment->withAnswerFormProperties(
                    $environment->getAnswerFormProperties()->withGaps($data)
                )
            );
    }

    private function buildAnswerOptionsForm(
        Environment $environment
    ): EditForm {
        $properties = $environment->getAnswerFormProperties();
        $ff = $this->ui_factory->input()->field();
        return $environment->getPresentationFactory()->getEditForm(
            $environment->withStepParameter(self::STEP_SET_POINTS),
            $this->refinery->custom()->transformation(
                fn(?string $carry): Section => $properties->getGaps()
                    ->buildAnswerOptionsInputs(
                        $this->lng,
                        $ff,
                        $this->refinery,
                        $carry,
                        $environment->getTableRowIds()
                    )
            ),
            false,
            $properties->buildCarryInputs($ff)
        )->withContentBeforeForm(
            $properties->getClozeText()->buildPanelForEditing(
                $this->ui_factory,
                $this->lng,
                $properties->getGaps(),
                $properties->getLegacyClozeText()
            )
        );
    }

    private function processAnswerOptionsForm(
        Environment $environment
    ): EditForm {
        $form = $this->buildAnswerOptionsForm(
            $environment
        )->withRequest($this->http->request());

        $data = $form->getData();
        return $data === null
            ? $form
            : $this->buildAssignPointsForm(
                $environment->withAnswerFormProperties(
                    $environment->getAnswerFormProperties()->withGaps($data)
                )
            );
    }

    private function buildAssignPointsForm(
        Environment $environment
    ): EditForm {
        $properties = $environment->getAnswerFormProperties();
        $ff = $this->ui_factory->input()->field();
        return $environment->getPresentationFactory()->getEditForm(
            $environment->withStepParameter(self::STEP_SAVE),
            $properties->getGaps()->buildPointInputs(
                $this->lng,
                $ff,
                $this->refinery,
                $environment->getTableRowIds()
            ),
            true,
            $properties->buildCarryInputs($ff)
        )->withContentBeforeForm(
            $properties->getClozeText()->buildPanelForEditing(
                $this->ui_factory,
                $this->lng,
                $properties->getGaps(),
                $properties->getLegacyClozeText()
            )
        );
    }

    private function processAssignPointsForm(
        Environment $environment
    ): EditForm|Properties {
        $form = $this->buildAssignPointsForm(
            $environment
        )->withRequest($this->http->request());

        $properties = $environment->getAnswerFormProperties();
        $data = $form->getData();
        return $data === null
            ? $form->withContentBeforeForm(
                $properties->getClozeText()->buildPanelForEditing(
                    $this->ui_factory,
                    $this->lng,
                    $properties->getGaps(),
                    $properties->getLegacyClozeText()
                )
            ) : $properties->withGaps($data);
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
