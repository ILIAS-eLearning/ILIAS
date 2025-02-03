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

/**
 * Interface ilAsqQuestionAuthoring
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components/ILIAS/AssessmentQuestion
 */
interface ilAsqQuestionAuthoring
{
    /**
     * Execute Command
     */
    public function executeCommand();

    /**
     * @param ilAsqQuestion $question
     */
    public function setQuestion(ilAsqQuestion $question);

    /**
     * @param ilQuestionChangeListener $listener
     */
    public function addQuestionChangeListener(ilQuestionChangeListener $listener);

    /**
     * @param object $a_object
     * @param string $a_method
     * @param mixed $a_parameters
     */
    public function addNewIdListener($a_object, $a_method, $a_parameters = "");

    /**
     * @param int $a_new_question_id
     */
    public function callNewIdListeners($a_new_question_id);

    /**
     * @param array $taxonomies - an array of taxonomy ids
     */
    public function setTaxonomies($taxonomies);

    /**
     * @param string $backLinkTarget - an http link
     */
    public function setBackLink($backLinkTarget);

    /**
     * @return \ILIAS\UI\Component\Link\Link
     */
    public function getPreviewLink(): \ILIAS\UI\Component\Link\Link;

    /**
     * @return \ILIAS\UI\Component\Link\Link
     */
    public function getEditQuestionConfigLink(): \ILIAS\UI\Component\Link\Link;

    /**
     * @return \ILIAS\UI\Component\Link\Link
     */
    public function getEditQuestionPageLink(): \ILIAS\UI\Component\Link\Link;

    /**
     * @return \ILIAS\UI\Component\Link\Link
     */
    public function getEditFeedbacksLink(): \ILIAS\UI\Component\Link\Link;

    /**
     * @return \ILIAS\UI\Component\Link\Link
     */
    public function getEditHintsLink(): \ILIAS\UI\Component\Link\Link;

    /**
     * @return \ILIAS\UI\Component\Link\Link
     */
    public function getStatisticLink(): \ILIAS\UI\Component\Link\Link;
}
