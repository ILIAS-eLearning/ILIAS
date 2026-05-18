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

use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\FeedbackProvider;
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\AnswerForm\Response as AnswerFormResponse;
use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\Legacy\LocalDIC;
use ILIAS\Questions\Presentation\Definitions\ViewMode;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Legacy\Content as LegacyContent;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

class ilPCAnswerForm extends ilPageContent
{
    private const string ANSWER_FORM_ELEMENT_TAG = 'AnswerForm';
    private const string ANSWER_FORM_ID_ATTRIBUTE = 'Uuid';
    private const string ANSWER_FORM_PLACEHOLDER = '\[\[\[ANSWER_FORM_([0-9a-f\-]+)\]\]\]';

    private const string TEMPLATE_VARIABLE_MAIN = 'OUTPUT';


    public function init(): void
    {
        $this->setType('answf');
    }

    #[\Override]
    public static function getLangVars(): array
    {
        return ['ed_insert_pcqst', 'empty_question', 'pc_qst'];
    }

    #[\Override]
    public function modifyPageContentPostXsl(
        string $output,
        string $mode,
        bool $abstract_only = false
    ): string {
        if ($this->pg_obj::class !== QstsQuestionPage::class) {
            return $output;
        }

        global $DIC;
        $lng = $DIC['lng'];
        $ui_factory = $DIC['ui.factory'];
        $ui_renderer = $DIC['ui.renderer'];
        $refinery = $DIC['refinery'];

        return mb_ereg_replace_callback(
            self::ANSWER_FORM_PLACEHOLDER,
            fn(array $matches): string => $this->renderAnswerForm(
                $lng,
                $ui_factory,
                $ui_renderer,
                $refinery,
                $this->pg_obj->getQuestion()->getAnswerFormPropertiesByIdString($matches[1]),
                $this->pg_obj->getAttemptData()
            ),
            $output
        );
    }

    #[\Override]
    public static function afterPageUpdate(
        ilPageObject $page,
        DOMDocument $domdoc,
        string $xml,
        bool $creation
    ): void {
        if ($page::class !== QstsQuestionPage::class || $creation) {
            return;
        }

        global $DIC;
        $dom_util = $DIC->copage()->internal()->domain()->domUtil();
        $question_repository = LocalDIC::dic()[Repository::class];

        /** @var \ILIAS\Questions\Question\Question $question */
        $question = $page->getQuestion();

        $answer_forms = [];
        foreach ($dom_util->path($domdoc, '//AnswerForm') as $node) {
            $answer_forms[] = $node->getAttribute(self::ANSWER_FORM_ID_ATTRIBUTE);
        }

        $question_repository->update(
            [$question->withoutDeletedAnswerForms($answer_forms)]
        );
    }

    #[\Override]
    public static function handleCopiedContent(
        DOMDocument $a_domdoc,
        bool $a_self_ass = true,
        bool $a_clone_mobs = false,
        int $new_parent_id = 0,
        int $obj_copy_id = 0
    ): void {
        global $DIC;

        $dom_util = $DIC->copage()->internal()->domain()->domUtil();

        // handle question elements
        if ($a_self_ass) {
            // copy questions
            $path = "//Question";
            $nodes = $dom_util->path($a_domdoc, $path);
            foreach ($nodes as $node) {
                $qref = $node->getAttribute("QRef");

                $inst_id = ilInternalLink::_extractInstOfTarget($qref);
                $q_id = ilInternalLink::_extractObjIdOfTarget($qref);

                if (!($inst_id > 0)) {
                    if ($q_id > 0) {
                        $question = null;
                        try {
                            $question = assQuestion::instantiateQuestion($q_id);
                        } catch (Exception $e) {
                        }
                        // check due to #16557
                        if (is_object($question) && $question->isComplete()) {
                            // check if page for question exists
                            // due to a bug in early 4.2.x version this is possible
                            if (!ilPageObject::_exists("qpl", $q_id)) {
                                $question->createPageObject();
                            }

                            // now copy this question and change reference to
                            // new question id
                            $duplicate_id = $question->duplicate(false);
                            $node->setAttribute("QRef", "il__qst_" . $duplicate_id);
                        }
                    }
                }
            }
        } else {
            // remove question
            $path = "//Question";
            $nodes = $dom_util->path($a_domdoc, $path);
            foreach ($nodes as $node) {
                $parent = $node->parentNode;
                $parent->parentNode->removeChild($parent);
            }
        }
    }

    public function create(
        Uuid $answer_form_id
    ): void {
        $this->createInitialChildNode(
            $this->hier_id,
            '',
            self::ANSWER_FORM_ELEMENT_TAG,
            [self::ANSWER_FORM_ID_ATTRIBUTE => $answer_form_id->toString()]
        );
    }

    public function getAnswerFormIdStringFromAttribute(): string
    {
        return $this->getChildNode()->attributes
                ->getNamedItem(self::ANSWER_FORM_ID_ATTRIBUTE)->nodeValue;
    }

    private function renderAnswerForm(
        Language $lng,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        Refinery $refinery,
        ?AnswerFormProperties $answer_form_properties,
        ?Attempt $attempt_data
    ): string {
        if ($answer_form_properties === null) {
            return $lng->txt('broken_answer_form');
        }

        $template = new \ilTemplate(
            'tpl.qsts_question_presentation.html',
            true,
            true,
            'components/ILIAS/Questions'
        );

        $question_response = $attempt_data?->getResponseForQuestion(
            $answer_form_properties->getQuestionId()
        );

        $answer_form_response = $question_response?->getAnswerFormResponse(
            $answer_form_properties->getAnswerFormId()
        );

        $main_content = $this->buildMainContent(
            $lng,
            $ui_factory,
            $answer_form_properties,
            $attempt_data,
            $answer_form_response
        );

        if (!$this->pg_obj->getShowFeedback()) {
            return $this->renderTemplate(
                $template,
                $ui_renderer->render($main_content)
            );
        }

        return $this->renderTemplate(
            $template,
            $ui_renderer->render(
                $this->addFeedbackSubPanelsToContent(
                    $lng,
                    $refinery,
                    $ui_factory,
                    $answer_form_properties,
                    $answer_form_response,
                    [
                        $ui_factory->panel()->standard(
                            $this->buildMainContentLabel($lng),
                            $main_content
                        )
                    ]
                )
            )
        );


    }

    private function renderTemplate(
        \ilTemplate $template,
        string $content
    ): string {
        $template->setVariable(
            self::TEMPLATE_VARIABLE_MAIN,
            $content
        );

        return $template->get();
    }

    private function buildMainContentLabel(
        Language $lng
    ): string {
        if ($this->pg_obj->getShowBestResponse()) {
            return $lng->txt('best_response');
        }

        return $lng->txt('question');
    }

    private function buildMainContent(
        Language $lng,
        UIFactory $ui_factory,
        ?AnswerFormProperties $answer_form_properties,
        ?Attempt $attempt_data,
        ?AnswerFormResponse $answer_form_response
    ): LegacyContent {
        /** @var RequiredCapabilities $required_capabilities */
        $required_capabilities = $this->pg_obj->getRequiredCapabilities();
        $participant_view = $required_capabilities
            ->getParticipantViewProvider()
            ->getParticipantView($answer_form_properties);

        if ($this->pg_obj->getShowBestResponse()) {
            $best_response = $this->pg_obj->getRequiredCapabilities()->getMarking(
                $answer_form_properties
            )?->getBestResponse(
                $answer_form_properties
            );

            return $ui_factory->legacy()->content(
                $participant_view->show(
                    $lng,
                    $answer_form_properties,
                    $attempt_data,
                    $best_response,
                    ViewMode::ViewBestResponse
                )
            );
        }

        return $ui_factory->legacy()->content(
            $participant_view->show(
                $lng,
                $answer_form_properties,
                $attempt_data,
                $answer_form_response,
                $this->pg_obj->getInteractive()
                    ? ViewMode::Respond
                    : ViewMode::ViewResponse
            )
        );
    }

    private function addFeedbackSubPanelsToContent(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        AnswerFormProperties $answer_form_properties,
        AnswerFormResponse $answer_form_response,
        array $content
    ): array {
        $required_capabilities = $this->pg_obj->getRequiredCapabilities();
        return array_reduce(
            $required_capabilities->getRequiredFeedbackProviders(),
            function (
                array $c,
                FeedbackProvider $v
            ) use (
                $lng,
                $refinery,
                $ui_factory,
                $answer_form_properties,
                $answer_form_response,
                $required_capabilities
            ): array {
                $output = $v->getFeedback(
                    $answer_form_properties
                )->getParticipantOutput(
                    $lng,
                    $refinery,
                    $ui_factory,
                    $answer_form_properties,
                    $answer_form_response,
                    $required_capabilities
                );

                if ($output === null) {
                    return $c;
                }

                $c[] = $output->getUI();
                return $c;
            },
            $content
        );
    }
}
