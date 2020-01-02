<?php
/**
 * Class SolutionExpressionInterface
 *
 * Date: 10.01.14
 * Time: 12:17
 * @author Thomas Joußen <tjoussen@databay.de>
 */

interface ilAssLacSolutionExpressionInterface
{
    /**
     * @param ilUserQuestionResult $result
     * @param string $comperator
     * @param null|int $index
     *
     * @return bool
     */
    public function checkResult($result, $comperator, $index = null);
}
