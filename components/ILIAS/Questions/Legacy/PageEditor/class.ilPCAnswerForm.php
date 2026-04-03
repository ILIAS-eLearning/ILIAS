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

use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\Legacy\LocalDIC;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

class ilPCAnswerForm extends ilPageContent
{
    private const string ANSWER_FORM_ELEMENT_TAG = 'AnswerForm';
    private const string ANSWER_FORM_ID_ATTRIBUTE = 'Uuid';
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
        $ui_factory = $DIC['ui.factory'];
        $ui_renderer = $DIC['ui.renderer'];
        $lng = $DIC['lng'];
        $question = $this->pg_obj->getQuestion();

        return mb_ereg_replace_callback(
            self::ANSWER_FORM_PLACEHOLDER,
            fn(array $matches): string => $this->renderAnswerForm(
                $ui_factory,
                $ui_renderer,
                $lng,
                $question->getAnswerFormPropertiesByIdString($matches[1])
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
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        Language $lng,
        ?AnswerFormProperties $answer_form_properties,
    ): string {
        if ($answer_form_properties === null) {
            return $lng->txt('broken_answer_form');
        }

        return $ui_renderer->render(
            $ui_factory->legacy()->latexContent(
                $answer_form_properties->getDefinition()->getParticipantView()
                    ->get(
                        $answer_form_properties,
                        null
                    )
            )
        );
    }
}
