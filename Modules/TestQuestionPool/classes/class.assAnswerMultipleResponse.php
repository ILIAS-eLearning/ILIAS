<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/class.assAnswerSimple.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for true/false or yes/no answers
*
* ASS_AnswerMultipleResponse is a class for answers with a binary state indicator (checked/unchecked, set/unset)
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @see ASS_AnswerSimple
*/
class ASS_AnswerMultipleResponse extends ASS_AnswerSimple
{
    /**
    * The points given to the answer when the answer is not checked
    *
    * The points given to the answer when the answer is not checked
    *
    * @var double
    */
    public $points_unchecked;

    /**
    * ASS_AnswerMultipleResponse constructor
    *
    * The constructor takes possible arguments an creates an instance of the ASS_AnswerMultipleResponse object.
    *
    * @param string $answertext A string defining the answer text
    * @param double $points The number of points given for the selected answer
    * @param double $points_unchecked The points when the answer is not checked
    * @param integer $order A nonnegative value representing a possible display or sort order
    * @access public
    */
    public function __construct(
      $answertext = "",
      $points_checked = 0.0,
      $order = 0,
      $points_unchecked = 0,
      $id = -1
  ) {
        parent::__construct($answertext, $points_checked, $order, $id);
        $this->setPointsUnchecked($points_unchecked);
    }


    /**
    * Returns the points for an unchecked answer
    *
    * Returns the points for an unchecked answer
    
    * @return double The points for an unchecked answer
    * @access public
    * @see $points_unchecked
    */
    public function getPointsUnchecked()
    {
        return $this->points_unchecked;
    }

    /**
    * Sets the points for an unchecked answer
    *
    * Sets the points for an unchecked answer
    *
    * @param double $points_unchecked The points for an unchecked answer
    * @access public
    * @see $state
    */
    public function setPointsUnchecked($points_unchecked = 0.0)
    {
        $new_points = str_replace(",", ".", $points_unchecked);
        
        if ($this->checkPoints($new_points)) {
            $this->points_unchecked = $new_points;
        } else {
            $this->points_unchecked = 0.0;
        }
    }

    public function setPointsChecked($points_checked)
    {
        $this->setPoints($points_checked);
    }
    
    public function getPointsChecked()
    {
        return $this->getPoints();
    }
}
