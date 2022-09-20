<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */



/**
 * Class ilAssQuestionHintRequestStatisticRegister
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class ilAssQuestionHintRequestStatisticRegister
{
    /**
     * @var array
     */
    protected $requestsByTestPassIndexAndQuestionId = array();

    /**
     * @param integer $passIndex
     * @param integer $qId
     * @param ilAssQuestionHintRequestStatisticData $request
     */
    public function addRequestByTestPassIndexAndQuestionId($passIndex, $qId, ilAssQuestionHintRequestStatisticData $request): void
    {
        if (!isset($this->requestsByTestPassIndexAndQuestionId[$passIndex])) {
            $this->requestsByTestPassIndexAndQuestionId[$passIndex] = array();
        }

        $this->requestsByTestPassIndexAndQuestionId[$passIndex][$qId] = $request;
    }

    /**
     * @param integer $passIndex
     * @param integer $qId
     */
    public function getRequestByTestPassIndexAndQuestionId($passIndex, $qId)
    {
        if (!isset($this->requestsByTestPassIndexAndQuestionId[$passIndex]) && !isset($this->requestsByTestPassIndexAndQuestionId[$passIndex][$qId])) {
            return null;
        }
        return $this->requestsByTestPassIndexAndQuestionId[$passIndex][$qId];
    }
}
