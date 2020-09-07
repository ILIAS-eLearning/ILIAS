<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Class for error text answers
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 *
 * @TODO Rework class to use members instead of $arrData + magic methods. (This needs some changes in neighbours.)
 */
class assAnswerErrorText
{
    /**
     * Array consisting of one errortext-answer
     * E.g. array('text_wrong' => 'Guenther', 'text_correct' => 'Günther', 'points' => 20)
     *
     * @var array Array consisting of one errortext-answer
     */
    protected $arrData;

    /**
     * assAnswerErrorText constructor
     *
     * @param string $text_wrong Wrong text
     * @param string $text_correct Correct text
     * @param double $points Points
     *
     * @return assAnswerErrorText
     */
    public function __construct($text_wrong = "", $text_correct = "", $points = 0.0)
    {
        $this->arrData = array(
            'text_wrong' => $text_wrong,
            'text_correct' => $text_correct,
            'points' => $points
        );
    }

    /**
     * Object getter
     */
    public function __get($value)
    {
        switch ($value) {
            case "text_wrong":
            case "text_correct":
            case "points":
                return $this->arrData[$value];
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * Object setter
     */
    public function __set($key, $value)
    {
        switch ($key) {
            case "text_wrong":
            case "text_correct":
            case "points":
                $this->arrData[$key] = $value;
                break;
            default:
                break;
        }
    }
}
