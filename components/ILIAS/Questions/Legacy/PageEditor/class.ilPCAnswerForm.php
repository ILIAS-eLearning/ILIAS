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

use ILIAS\Data\UUID\Uuid;

class ilPCAnswerForm extends ilPageContent
{
    private const string ANSWER_FORM_ELEMENT_TAG = 'AnswerForm';
    private const string ANSWER_FORM_ID_ATTRIBUTE = 'Uuid';
    private const string ANSWER_FORM_PLACEHOLDER = '\[\[\[ANSWER_FORM_([0-9a-f\-]+)\]\]\]';

    public function init(): void
    {
        $this->setType('answf');
    }

    public static function getLangVars(): array
    {
        return ['ed_insert_pcqst', 'empty_question', 'pc_qst'];
    }

    public function modifyPageContentPostXsl(
        string $output,
        string $mode,
        bool $abstract_only = false
    ): string {
        if ($this->pg_obj::class !== QstsQuestionPage::class) {
            return $output;
        }

        /** @var \ILIAS\Questions\Question\QuestionImplementation $question */
        $question = $this->pg_obj->getQuestion();

        return mb_ereg_replace_callback(
            self::ANSWER_FORM_PLACEHOLDER,
            fn(array $matches): string => $question
                ->getAnswerFormByIdString($matches[1])?->getTypeGenericProperties()
                ->getAdditionalText() ?? '',
            $output
        );
    }

    public function getCssFiles(string $a_mode): array
    {
        if ($this->getPage()->getPageConfig()->getEnableSelfAssessment()) {
            return array("./components/ILIAS/TestQuestionPool/resources/js/dist/question_handling.css",
                "components/ILIAS/TestQuestionPool/templates/default/test_javascript.css");
        }
        return array();
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

    public static function afterPageUpdate(
        ilPageObject $page,
        DOMDocument $domdoc,
        string $xml,
        bool $creation
    ): void {
        if ($page::class !== QstsQuestionPage::class) {
            return;
        }

        global $DIC;
        $dom_util = $DIC->copage()->internal()->domain()->domUtil();

        /** @var \ILIAS\Questions\Question\QuestionImplementation $question */
        $question = $page->getQuestion();

        $answer_forms = [];
        foreach ($dom_util->path($domdoc, '//AnswerForm') as $node) {
            $answer_forms[] = $node->getAttribute(self::ANSWER_FORM_ID_ATTRIBUTE);
        }
    }

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
}
