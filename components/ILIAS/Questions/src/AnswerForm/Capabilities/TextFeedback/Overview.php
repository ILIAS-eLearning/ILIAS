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

namespace ILIAS\Questions\AnswerForm\Capabilities\TextFeedback;

use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\Viewable;
use ILIAS\Data\Text\Factory as TextFactory;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\URLBuilder;

class Overview implements Viewable
{
    private bool $set_legacy_texts_as_values = false;

    private ?StandardForm $form = null;
    private ?RoundTripModal $modal = null;

    private ?OverviewTable $specific_feedback_table;

    public function __construct(
        private readonly Environment $environment,
        private readonly TextFactory $text_factory,
        private TextFeedback $feedback,
        private readonly URLBuilder $save_uri,
        private readonly URLBuilder $insert_legacy_texts_uri
    ) {
        $this->specific_feedback_table = $feedback->getSpecificFeedbackTable(
            $environment
        );
    }

    #[\Override]
    public function getUI(): array
    {
        $ui_factory = $this->environment->getUIFactory();
        $lng = $this->environment->getLanguage();

        $content = [];

        if ($this->feedback->hasLegacyTexts()) {
            $content[] = $ui_factory->messageBox()->info(
                $lng->txt('insert_legacy_texts_info')
            )->withButtons([
                $ui_factory->button()->standard(
                    $lng->txt('insert_legacy_texts'),
                    $this->insert_legacy_texts_uri->buildURI()->__toString()
                )
            ]);
        }

        $form = $this->form ?? $this->buildForm();
        if ($form !== null) {
            $content[] = $form;
        }

        if ($this->specific_feedback_table !== null) {
            $modal = $this->specific_feedback_table->getCreateModal(
                $this->environment
            );
            $content[] = $ui_factory->button()->standard(
                $lng->txt('create_feedback'),
                $modal->getShowSignal()
            );
            $content[] = $modal;
            $content[] = $this->specific_feedback_table->getTable(
                $this->environment,
                $this->feedback
            )->withTitle(
                $lng->txt('specific_feedback')
            );
        }

        if ($this->modal !== null) {
            $content[] = $this->modal;
        }
        return $content;
    }

    public function withLegacyTextsAsValues(
        bool $replace_with_legacy_texts
    ): self {
        $clone = clone $this;
        $clone->set_legacy_texts_as_values = $replace_with_legacy_texts;
        return $clone;
    }

    public function processForm(): TextFeedback|StandardForm
    {
        $this->form = $this->buildForm()?->withRequest(
            $this->environment->getHttpServices()->request()
        );
        $data = $this->form?->getData();

        return $data === null
            ? $this
            : $data['feedback'];
    }

    public function doAction(
        Repository $repository,
        string $action
    ): Async|self {
        $result = $this->specific_feedback_table->doAction(
            $this->environment,
            $this->feedback,
            $action
        );

        if ($result instanceof Async) {
            return $result;
        }

        $clone = clone $this;
        if ($result instanceof RoundTripModal) {
            $clone->modal = $result->withOnLoad($result->getShowSignal());
            return $clone;
        }

        $repository->store($this->environment->getAnswerFormId(), $result);
        $clone->feedback = $result;
        $clone->specific_feedback_table = $result->getSpecificFeedbackTable(
            $this->environment
        );
        return $clone;
    }

    private function buildForm(): ?StandardForm
    {
        $if = $this->environment->getUIFactory()->input();
        $lng = $this->environment->getLanguage();

        $inputs = $this->buildGenericFormInputs();

        $additional_inputs = $this->feedback->getAdditionalInputs(
            $lng,
            $this->environment->getUIFactory(),
            $this->set_legacy_texts_as_values
        );

        if ($additional_inputs !== null) {
            $inputs['specific_feedback'] = $if->field()->section(
                $additional_inputs,
                $lng->txt('edit_specific_feedback')
            );
        }

        if ($inputs === []) {
            return null;
        }

        return $if->container()->form()->standard(
            $this->save_uri->buildURI()->__toString(),
            [
                'feedback' => $if->field()->group(
                    $inputs
                )->withAdditionalTransformation(
                    $this->environment->getRefinery()->custom()->transformation(
                        fn(array $vs): TextFeedback => ($vs['specific_feedback'] ?? $this->feedback)
                            ->withFeedbackBestResponse(
                                $this->text_factory->markdown($vs['generic_feedback']['max_points'])
                            )->withFeedbackOtherResponse(
                                $this->text_factory->markdown($vs['generic_feedback']['not_max_points'])
                            )
                    )
                )
            ]
        );
    }

    private function buildGenericFormInputs(): array
    {
        if (!$this->environment->isMarkingRequired()) {
            return [];
        }

        $if = $this->environment->getUIFactory()->input();
        $lng = $this->environment->getLanguage();

        return [
            'generic_feedback' => $if->field()->section(
                [
                    'max_points' => $if->field()->markdown(
                        new \ilUIMarkdownPreviewGUI(),
                        Types::BestResponse->getTranslatedOptionName($lng)
                    )->withValue(
                        $this->set_legacy_texts_as_values
                            ? $this->feedback->getFeedbackBestResponseLegacy()
                            : $this->feedback->getFeedbackBestResponse()
                                ?->getRawRepresentation() ?? ''
                    ),
                    'not_max_points' => $if->field()->markdown(
                        new \ilUIMarkdownPreviewGUI(),
                        Types::OtherResponse->getTranslatedOptionName($lng)
                    )->withValue(
                        $this->set_legacy_texts_as_values
                            ? $this->feedback->getFeedbackOtherResponseLegacy()
                            : $this->feedback->getFeedbackOtherResponse()
                                ?->getRawRepresentation() ?? ''
                    ),
                ],
                $lng->txt('edit_generic_feedback')
            )
        ];
    }
}
