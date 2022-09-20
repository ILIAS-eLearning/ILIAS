<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAsqResultCalculator
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Services/AssessmentQuestion
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
