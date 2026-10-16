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

use ILIAS\Questions\Question\Question;
use ILIAS\Data\UUID\Uuid;

class ilAssQuestionPage extends ilPageObject
{
    private readonly Question $question;

    /**
     * Get parent type
     * @return string parent type
     */
    public function getParentType(): string
    {
        return "qpl";
    }

    public function setQuestion(
        Question $question
    ): void {
        $this->question = $question;
    }

    public function copyToAnswerForm(
        int $new_id,
        Question $question
    ): void {
        $this->buildDom();
        $this->migrateQuestionElementToAnswerForm();

        $new_page_object = new QstsQuestionPage();
        $new_page_object->setParentId($this->getParentId());
        $new_page_object->setId($new_id);
        $new_page_object->setXMLContent($this->copyXMLContent(false, $this->getParentId()));
        $new_page_object->setActive($this->getActive());
        $new_page_object->setActivationStart($this->getActivationStart());
        $new_page_object->setActivationEnd($this->getActivationEnd());
        $new_page_object->setQuestion($question);
        $new_page_object->create(false);
    }

    private function migrateQuestionElementToAnswerForm(): void
    {
        global $DIC;
        $DIC->copage()
            ->internal()
            ->domain()
            ->domUtil()
            ->path($this->getDomDoc(), '//Question')
            ->item(0)->parentNode->replaceWith(
                $this->buildLegacyAnswerFormTextNode(),
                $this->buildAnswerFormNode(
                    $this->question->getFirstAnswerFormIdForMigration()
                )
            );
        $this->xml = $this->getXMLFromDom();
    }

    private function buildLegacyAnswerFormTextNode(): DOMNode
    {
        $legacy_answer_form_text_node = new ilPCLegacyAnswerFormText($this);
        $legacy_answer_form_text_node->createPageContentNode();
        $legacy_answer_form_text_node->writePCId($this->generatePCId());
        $legacy_answer_form_text_node->create(
            $this->retrieveLegacyPageElementContent()
        );

        return $legacy_answer_form_text_node->getDomNode();
    }

    private function buildAnswerFormNode(
        Uuid $answer_form_id
    ): DOMNode {
        $answer_form_node = new ilPCAnswerForm($this);
        $answer_form_node->createPageContentNode();
        $answer_form_node->writePCId($this->generatePCId());
        $answer_form_node->create($answer_form_id);

        return $answer_form_node->getDomNode();
    }

    private function retrieveLegacyPageElementContent(): string
    {
        $question_info = $this->db->fetchObject(
            $this->db->query(
                "SELECT add_cont_edit_mode, question_text FROM qpl_questions WHERE question_id = {$this->id}"
            )
        );

        $purified_content = ilHtmlPurifierFactory::getInstanceByType('qpl_usersolution')
            ->purify($question_info->question_text);

        if ($question_info->add_cont_edit_mode === assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE
            || !(new ilSetting('advanced_editing'))->get('advanced_editing_javascript_editor') === 'tinymce') {
            $purified_content = nl2br($purified_content);
        }
        return base64_encode(
            ilLegacyFormElementsUtil::prepareTextareaOutput(
                $purified_content,
                true,
                true
            )
        );
    }
}
