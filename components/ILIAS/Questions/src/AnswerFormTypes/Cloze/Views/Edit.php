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
use ILIAS\Questions\Question\Persistence\ManipulateQuery;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Language\Language;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\Refinery\Factory as Refinery;

class Edit implements EditViewInterface
{
    private const string STEP_SET_GAP_TYPES = 'sgt';
    private const string STEP_SET_ANSWER_OPTIONS = 'sao';
    private const string STEP_SET_POINTS = 'sap';
    private const string STEP_SAVE = 's';

    private const string MAIN_SECTION_NAME = 'form';
    private const string PROPERTIES_SECTION_NAME = 'properties';

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
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        string $step,
    ): array|ManipulateQuery {
        return match($step) {
            self::STEP_SET_GAP_TYPES => $this->processBasicEditingForm($url_builder, $step_token),
            self::STEP_SET_ANSWER_OPTIONS => $this->processGapTypesForm($url_builder, $step_token),
            self::STEP_SET_POINTS => $this->processAnswerOptionsForm($url_builder, $step_token),
            self::STEP_SAVE => $this->processAssignPointsForm($url_builder, $step_token),
            default => [$this->buildBasicEditingForm($url_builder, $step_token)]
        };
    }

    public function edit(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        string $step
    ): array|ManipulateQuery {

    }

    public function other(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        string $step
    ): array|ManipulateQuery {

    }

    private function buildBasicEditingForm(
        URLBuilder $url_builder,
        URLBuilderToken $step_token
    ): StandardForm {
        return $this->ui_factory->input()->container()->form()->standard(
            $url_builder->withParameter($step_token, self::STEP_SET_GAP_TYPES)->buildURI()->__toString(),
            [
                self::MAIN_SECTION_NAME => $this->type->getProperties()->buildBasicEditingInputs(
                    $this->lng,
                    $this->ui_factory->input()->field(),
                    $this->refinery,
                    $this->properties_factory,
                    $this->cloze_text_factory
                )
            ]
        )->withSubmitLabel($this->lng->txt('next'));
    }

    private function processBasicEditingForm(
        URLBuilder $url_builder,
        URLBuilderToken $step_token
    ): array {
        $form = $this->buildBasicEditingForm($url_builder, $step_token)->withRequest($this->http->request());
        $data = $form->getData();
        if ($data === null) {
            return [$form];
        }

        return $this->buildOutputWithPanel(
            $this->buildGapTypesForm(
                $url_builder,
                $step_token,
                $data[self::MAIN_SECTION_NAME]
            ),
            $data[self::MAIN_SECTION_NAME]
        );
    }

    private function buildGapTypesForm(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        Properties $properties
    ): StandardForm {
        $ff = $this->ui_factory->input()->field();
        return $this->ui_factory->input()->container()->form()->standard(
            $url_builder->withParameter($step_token, self::STEP_SET_ANSWER_OPTIONS)->buildURI()->__toString(),
            [
                self::MAIN_SECTION_NAME => $properties->getGaps()->buildGapsTypeInputs(
                    $this->lng,
                    $ff,
                    $this->refinery,
                    $this->gap_factory->getAvailableGapTypesOptionsArray($this->lng)
                ),
                self::PROPERTIES_SECTION_NAME => $properties
                    ->withClozeText($properties->getClozeText())
                    ->buildBasicEditingInputsHidden($ff)
                    ->withDedicatedName(self::PROPERTIES_SECTION_NAME)

            ]
        )->withSubmitLabel($this->lng->txt('next'));
    }

    private function processGapTypesForm(
        URLBuilder $url_builder,
        URLBuilderToken $step_token
    ): array {
        $properties = $this->retrievePropertiesFromPost();
        $form = $this->buildGapTypesForm(
            $url_builder,
            $step_token,
            $properties
        )->withRequest($this->http->request());

        $data = $form->getData();
        return $this->buildOutputWithPanel(
            $data === null
                ? $form
                : $this->buildAnswerOptionsForm(
                    $url_builder,
                    $step_token,
                    $properties->withGaps($data[self::MAIN_SECTION_NAME])
                ),
            $properties
        );
    }

    private function buildAnswerOptionsForm(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        Properties $properties
    ): StandardForm {
        $ff = $this->ui_factory->input()->field();
        return $this->ui_factory->input()->container()->form()->standard(
            $url_builder->withParameter($step_token, self::STEP_SET_POINTS)->buildURI()->__toString(),
            [
                self::MAIN_SECTION_NAME => $properties->getGaps()->buildAnswerOptionsInputs($this->lng, $ff, $this->refinery),
                self::PROPERTIES_SECTION_NAME => $properties->buildBasicEditingInputsHidden($ff)
                    ->withDedicatedName(self::PROPERTIES_SECTION_NAME)
            ]
        )->withSubmitLabel($this->lng->txt('next'));
    }

    private function processAnswerOptionsForm(
        URLBuilder $url_builder,
        URLBuilderToken $step_token
    ): array {
        $properties = $this->retrievePropertiesFromPost();
        $form = $this->buildAnswerOptionsForm(
            $url_builder,
            $step_token,
            $properties
        )->withRequest($this->http->request());

        $data = $form->getData();
        return $this->buildOutputWithPanel(
            $data === null
                ? $form
                : $this->buildAssignPointsForm(
                    $url_builder,
                    $step_token,
                    $properties->withGaps($data[self::MAIN_SECTION_NAME])
                ),
            $properties
        );
    }

    private function buildAssignPointsForm(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        Properties $properties
    ): StandardForm {
        $ff = $this->ui_factory->input()->field();
        return $this->ui_factory->input()->container()->form()->standard(
            $url_builder->withParameter($step_token, self::STEP_SAVE)->buildURI()->__toString(),
            [
                self::MAIN_SECTION_NAME => $properties->getGaps()->buildPointInputs($this->lng, $ff, $this->refinery),
                self::PROPERTIES_SECTION_NAME => $properties->buildBasicEditingInputsHidden($ff)
                    ->withDedicatedName(self::PROPERTIES_SECTION_NAME)
            ]
        )->withSubmitLabel($this->lng->txt('save'));
    }

    private function processAssignPointsForm(
        URLBuilder $url_builder,
        URLBuilderToken $step_token
    ): array|ManipulateQuery {
        $properties = $this->retrievePropertiesFromPost();
        $form = $this->buildAssignPointsForm(
            $url_builder,
            $step_token,
            $properties
        )->withRequest($this->http->request());

        $data = $form->getData();
        if ($data === null) {
            return $this->buildOutputWithPanel($form, $properties);
        }

        return $this->save($properties->withGaps($data[self::MAIN_SECTION_NAME]));
    }

    private function buildOutputWithPanel(
        StandardForm $form,
        Properties $properties
    ): array {
        return [
            $this->ui_factory->panel()->standard(
                $this->lng->txt('cloze_text'),
                $this->ui_factory->legacy()->content(
                    $properties->getClozeText()->getRenderedMarkdown(
                        $properties->getGaps()
                    )
                )
            ),
            $form
        ];
    }

    private function save(Properties $properties): ManipulateQuery
    {
        return $properties->toPersistence();
    }

    private function retrievePropertiesFromPost(): Properties
    {

        return $this->type->getProperties()->withValuesFromPost(
            $this->refinery,
            $this->http->wrapper()->post(),
            'form/' . self::PROPERTIES_SECTION_NAME
        );
    }
}
