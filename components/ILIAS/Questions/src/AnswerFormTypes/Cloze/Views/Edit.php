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
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\AnswerForm\Factory as PropertiesFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\AnswerForm\Properties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText\Factory as ClozeTextFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Factory as GapFactory;
use ILIAS\Questions\Presentation\Layout\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Definitions\EditForm;
use ILIAS\Questions\Presentation\Layout\Definitions\EditOverview;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\Refinery\Factory as Refinery;

class Edit implements EditViewInterface
{
    private const string STEP_EDIT_BASIC_PROPERTIES = 'ebp';
    private const string STEP_SET_GAP_TYPES = 'sgt';
    private const string STEP_SET_ANSWER_OPTIONS = 'sao';
    private const string STEP_SET_POINTS = 'sap';
    private const string STEP_SAVE = 's';

    public function __construct(
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly Refinery $refinery,
        private readonly HTTPServices $http,
        private readonly PropertiesFactory $properties_factory,
        private readonly ClozeTextFactory $cloze_text_factory,
        private readonly GapFactory $gap_factory
    ) {
    }

    public function create(
        Environment $environment
    ): EditForm|Properties {
        return match($environment->getStep()) {
            self::STEP_SET_GAP_TYPES => $this->processBasicEditingForm(
                $environment->withProperties(
                    $environment->getProperties()->withValuesFromCarry(
                        $this->refinery,
                        $this->cloze_text_factory,
                        $this->gap_factory,
                        $environment->getDefinitionsFactory()->getCarrySectionData(
                            $this->http->wrapper()->post(),
                            $this->refinery
                        )
                    )
                )
            ),
            self::STEP_SET_ANSWER_OPTIONS => $this->processGapTypesForm(
                $environment->withProperties(
                    $environment->getProperties()->withValuesFromCarry(
                        $this->refinery,
                        $this->cloze_text_factory,
                        $this->gap_factory,
                        $environment->getDefinitionsFactory()->getCarrySectionData(
                            $this->http->wrapper()->post(),
                            $this->refinery
                        )
                    )
                )
            ),
            self::STEP_SET_POINTS => $this->processAnswerOptionsForm(
                $environment->withProperties(
                    $environment->getProperties()->withValuesFromCarry(
                        $this->refinery,
                        $this->cloze_text_factory,
                        $this->gap_factory,
                        $environment->getDefinitionsFactory()->getCarrySectionData(
                            $this->http->wrapper()->post(),
                            $this->refinery
                        )
                    )
                )
            ),
            self::STEP_SAVE => $this->processAssignPointsForm(
                $environment->withProperties(
                    $environment->getProperties()->withValuesFromCarry(
                        $this->refinery,
                        $this->cloze_text_factory,
                        $this->gap_factory,
                        $environment->getDefinitionsFactory()->getCarrySectionData(
                            $this->http->wrapper()->post(),
                            $this->refinery
                        )
                    )
                )
            ),
            default => $this->buildBasicEditingForm($environment)
        };
    }

    public function edit(
        Environment $environment
    ): EditOverview|EditForm|Properties {
        return match ($step) {
            default => $this->buildEditingOverview($environment)
        };
    }

    public function other(
        Environment $environment
    ): EditForm|Properties {

    }

    private function buildEditingOverview(
        Environment $environment
    ): EditOverview {
        return $environment->getDefinitionsFactory()->getEditOverview(
            $environment->getEditability(),
            $environment->getUrlBuilderWithStepParameter(self::STEP_EDIT_BASIC_PROPERTIES),
            $environment->getProperties()
        );
    }

    private function buildBasicEditingForm(
        Environment $environment
    ): EditForm {
        return $environment->getDefinitionsFactory()->getEditForm(
            $environment->getUrlBuilderWithStepParameter(self::STEP_SET_GAP_TYPES),
            $environment->getProperties()->buildBasicEditingInputs(
                $this->lng,
                $this->ui_factory->input()->field(),
                $this->refinery,
                $this->properties_factory,
                $this->cloze_text_factory
            ),
            false
        );
    }

    private function processBasicEditingForm(
        Environment $environment
    ): EditForm {
        $form = $this->buildBasicEditingForm(
            $environment
        )->withRequest($this->http->request());

        $data = $form->getData();
        return $data === null
            ? $form
            : $this->buildGapTypesForm(
                $environment->withProperties($data)
            );
    }

    private function buildGapTypesForm(
        Environment $environment
    ): EditForm {
        $properties = $environment->getProperties();
        $ff = $this->ui_factory->input()->field();
        return $environment->getDefinitionsFactory()->getEditForm(
            $environment->getUrlBuilderWithStepParameter(self::STEP_SET_ANSWER_OPTIONS),
            $properties->getGaps()->buildGapsTypeInputs(
                $this->lng,
                $ff,
                $this->refinery,
                $this->gap_factory->getAvailableGapTypesOptionsArray($this->lng)
            ),
            false,
            $properties->withClozeText($properties->getClozeText())
                ->buildCarryInputs($ff)
        )->withContentBeforeForm(
            $this->buildClozeTextPanel($properties)
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
                $environment->withProperties(
                    $environment->getProperties()->withGaps($data)
                )
            );
    }

    private function buildAnswerOptionsForm(
        Environment $environment
    ): EditForm {
        $properties = $environment->getProperties();
        $ff = $this->ui_factory->input()->field();
        return $environment->getDefinitionsFactory()->getEditForm(
            $environment->getUrlBuilderWithStepParameter(self::STEP_SET_POINTS),
            $properties->getGaps()->buildAnswerOptionsInputs($this->lng, $ff, $this->refinery),
            false,
            $properties->buildCarryInputs($ff)
        )->withContentBeforeForm(
            $this->buildClozeTextPanel($properties)
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
                $environment->withProperties(
                    $environment->getProperties()->withGaps($data)
                )
            );
    }

    private function buildAssignPointsForm(
        Environment $environment
    ): EditForm {
        $properties = $environment->getProperties();
        $ff = $this->ui_factory->input()->field();
        return $environment->getDefinitionsFactory()->getEditForm(
            $environment->getUrlBuilderWithStepParameter(self::STEP_SAVE),
            $properties->getGaps()->buildPointInputs($this->lng, $ff, $this->refinery),
            true,
            $properties->buildCarryInputs($ff)
        )->withContentBeforeForm(
            $this->buildClozeTextPanel($properties)
        );
    }

    private function processAssignPointsForm(
        Environment $environment
    ): EditForm|Properties {
        $form = $this->buildAssignPointsForm(
            $environment
        )->withRequest($this->http->request());

        $properties = $environment->getProperties();
        $data = $form->getData();
        return $data === null
            ? $form->withContentBeforeForm(
                $this->buildClozeTextPanel($properties)
            ) : $properties->withGaps($data);
    }

    private function buildClozeTextPanel(
        Properties $properties
    ): StandardPanel {
        return $this->ui_factory->panel()->standard(
            $this->lng->txt('cloze_text'),
            $this->ui_factory->legacy()->content(
                $properties->getClozeText()->getRenderedMarkdownForEditingPresentation(
                    $properties->getGaps()
                )
            )
        );
    }
}
