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
