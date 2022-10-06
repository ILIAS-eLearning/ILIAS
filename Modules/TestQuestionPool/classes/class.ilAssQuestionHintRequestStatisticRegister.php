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
