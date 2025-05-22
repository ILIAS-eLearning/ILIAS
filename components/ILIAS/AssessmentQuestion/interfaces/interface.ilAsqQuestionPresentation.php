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
 * Interface ilAsqQuestionPresentation
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components/ILIAS/AssessmentQuestion
 */
interface ilAsqQuestionPresentation
{
    /**
     * @param ilAsqQuestion $question
     */
    public function setQuestion(ilAsqQuestion $question);

    /**
     * @param ilAsqQuestionSolution $solution
     * @return \ILIAS\UI\Component\Component
     */
    public function getQuestionPresentation(ilAsqQuestionSolution $solution): \ILIAS\UI\Component\Component;

    /**
     * @param ilAsqQuestionSolution $solution
     * @return \ILIAS\UI\Component\Component
     */
    public function getSolutionPresentation(ilAsqQuestionSolution $solution): \ILIAS\UI\Component\Component;

    /**
     * @param ilAsqQuestionSolution $solution
     * @return \ILIAS\UI\Component\Component
     */
    public function getGenericFeedbackOutput(ilAsqQuestionSolution $solution): \ILIAS\UI\Component\Component;

    /**
     * @param ilAsqQuestionSolution $solution
     * @return \ILIAS\UI\Component\Component
     */
    public function getSpecificFeedbackOutput(ilAsqQuestionSolution $solution): \ILIAS\UI\Component\Component;

    /**
     * @return bool
     */
    public function hasInlineFeedback(): bool;

    /**
     * @return bool
     */
    public function isAutosaveable(): bool;

    /**
     * @param ilAsqQuestionNavigationAware
     */
    public function setQuestionNavigation(ilAsqQuestionNavigationAware $questionNavigationAware);
}
