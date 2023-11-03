<?php

/**
 * For that questions that are actually shown in the rows limit of the table corresponding question links
 * needs to be rendered. The ilAsqFactory can be used within the fillRow method to get an ilAsqQuestionAuthoring
 * instance for each question to get all neccessary links as an UI Link Component.
 *
 * The following links are provided:
 * - Question Preview Link
 * - Edit Question Config Link
 * - Edit Question Page Link
 * - Edit Feedbacks Link
 * - Edit Hints Link
 * - Question Statistic Link
 */
class exQuestionsTableGUI extends ilTable2GUI
{
    /**
     * @param array $a_set
     */
    public function fillRow(array $a_set): void
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        /**
         * use the associative array containing the question data
         * for filling any table column with title, comment, points, etc.
         */

        $this->tpl->setVariable('QUESTION_TITLE', $a_set['title']);

        /**
         * use the questionId and the ilAsqFactory to get an ilAsqQuestionAuthoring instance
         * that provides interface methods to get neccessary links related to the question
         */

        $questionInstance = $DIC->question()->getQuestionInstance($a_set['questionId']);
        $questionAuthoringGUI = $DIC->question()->getAuthoringCommandInstance($questionInstance);

        $previewLinkComponent = $questionAuthoringGUI->getPreviewLink();

        $this->tpl->setVariable('QUESTION_HREF', $previewLinkComponent->getAction());
    }
}
