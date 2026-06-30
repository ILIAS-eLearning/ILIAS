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
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\Legacy\LocalDIC;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UICore\GlobalTemplate;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Data\UUID\Factory as UuidFactory;

class ilPCAnswerForm extends ilPageContent
{
    public const string ANSWER_FORM_ELEMENT_TAG = 'AnswerForm';
    public const string ANSWER_FORM_ID_ATTRIBUTE = 'Uuid';
    private const string ANSWER_FORM_PLACEHOLDER = '\[\[\[ANSWER_FORM_([0-9a-f\-]+)\]\]\]';

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
        $global_tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ui_factory = $DIC['ui.factory'];
        $ui_renderer = $DIC['ui.renderer'];
        $refinery = $DIC['refinery'];

        return mb_ereg_replace_callback(
            self::ANSWER_FORM_PLACEHOLDER,
            fn(array $matches): string => $this->renderAnswerForm(
                $global_tpl,
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
        /** @var Repository $question_repository */
        $question_repository = LocalDIC::dic()[Repository::class];

        $uuid_factory = new UuidFactory();

        /** @var \ILIAS\Questions\Question\Question $question */
        $question = $page->getQuestion();

        $answer_form_mapping = \QstsQuestionPage::getAnswerFormMapping();
        \QstsQuestionPage::setAnswerFormMapping([]);

        $answer_forms = [];
        foreach ($dom_util->path($domdoc, '//AnswerForm') as $node) {
            $answer_form_id = $node->getAttribute(self::ANSWER_FORM_ID_ATTRIBUTE);
            $answer_form = $question->getAnswerFormPropertiesByIdString($answer_form_id);

            if ($answer_form === null && in_array($answer_form_id, $answer_form_mapping)) {
                $old_answer_form_id = array_search($answer_form_id, $answer_form_mapping);
                $old_answer_form = $question->getAnswerFormPropertiesByIdString(
                    $old_answer_form_id
                );

                if ($old_answer_form === null) {
                    $old_answer_form_uuid = $uuid_factory->fromString($answer_form_id);
                    $old_answer_form = $question_repository->getQuestionForAnswerFormId(
                        $old_answer_form_uuid
                    )->getAnswerFormPropertiesByIdString(
                        $old_answer_form_uuid
                    );
                }

                $question = $question->withAnswerFormProperties(
                    $old_answer_form->clone(
                        $uuid_factory,
                        [
                            'new_question_id' => $question->getId(),
                            'answer_form_id' => $uuid_factory->fromString(
                                $answer_form_id
                            )
                        ]
                    )
                );
            }

            $answer_forms[] = $answer_form_id;
        }

        $page->setQuestion($question->withoutDeletedAnswerForms($answer_forms));

        $question_repository->update(
            [$page->getQuestion()]
        );
    }

    #[\Override]
    public static function handleCopiedContent(
        DOMDocument $domdoc,
        bool $self_ass = true,
        bool $clone_mobs = false,
        int $new_parent_id = 0,
        int $obj_copy_id = 0
    ): void {
        global $DIC;
        $dom_util = $DIC->copage()->internal()->domain()->domUtil();

        $answer_form_mapping = \QstsQuestionPage::getAnswerFormMapping();
        if ($answer_form_mapping === []) {
            return;
        }

        foreach ($dom_util->path($domdoc, '//AnswerForm') as $node) {
            $old_answer_form_id = $node->getAttribute(self::ANSWER_FORM_ID_ATTRIBUTE);
            if (isset($answer_form_mapping[$old_answer_form_id])) {
                $node->setAttribute(
                    self::ANSWER_FORM_ID_ATTRIBUTE,
                    $answer_form_mapping[$old_answer_form_id]
                );
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
        GlobalTemplate $global_tpl,
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

        /** @var RequiredCapabilities $required_capabilities */
        $required_capabilities = $this->pg_obj->getRequiredCapabilities();

        $answer_form_response = $attempt_data?->getResponseForQuestion(
            $answer_form_properties->getQuestionId()
        )?->getAnswerFormResponse(
            $answer_form_properties->getAnswerFormId()
        );

        return $ui_renderer->render(
            $required_capabilities
                ->getParticipantViewProvider()
                ->getParticipantView($answer_form_properties)
                ->show(
                    $global_tpl,
                    $lng,
                    $refinery,
                    $ui_factory,
                    $required_capabilities,
                    $this->pg_obj->getViewConfiguration(),
                    $answer_form_properties,
                    $attempt_data,
                    $answer_form_response
                )
        );
    }
}
