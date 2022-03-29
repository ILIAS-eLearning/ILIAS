<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Class for error text answers
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @ingroup ModulesTestQuestionPool
 */
class assAnswerErrorText
{
    public string $text_wrong;
    public string $text_correct;
    public float  $points;

    /**
     * assAnswerErrorText constructor
     * @param string $text_wrong   Wrong text
     * @param string $text_correct Correct text
     * @param double $points       Points
     */
    public function __construct(string $text_wrong = "", string $text_correct = "", float $points = 0.0)
    {
        $this->text_wrong = $text_wrong;
        $this->text_correct = $text_correct;
        $this->points = $points;
    }
}
