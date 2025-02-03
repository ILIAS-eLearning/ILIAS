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
 * Interface ilAsqResultCalculator
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components/ILIAS/AssessmentQuestion
 */
interface ilAsqResultCalculator
{
    /**
     * @param ilAsqQuestion $question
     * @return void
     */
    public function setQuestion(ilAsqQuestion $question);

    /**
     * @param ilAsqQuestionSolution $question
     * @return void
     */
    public function setSolution(ilAsqQuestionSolution $question);

    /**
     * @return ilAsqQuestionResult
     */
    public function calculate(): ilAsqQuestionResult;
}
