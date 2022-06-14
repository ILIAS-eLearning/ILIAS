<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/class.assAnswerMultipleResponse.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* ASS_AnswerBinaryStateImage is a class for answers with a binary state
* indicator (checked/unchecked, set/unset) and an image file
*
* ASS_AnswerBinaryStateImage is a class for answers with a binary state
* indicator (checked/unchecked, set/unset) and an image file
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @see ASS_AnswerSimple
*/
class ASS_AnswerMultipleResponseImage extends ASS_AnswerMultipleResponse
{
    /**
    * Image filename
    *
    * Image filename
    *
    * @var string
    */
    public $image;

    /**
    * ASS_AnswerMultipleResponse constructor
    *
    * The constructor takes possible arguments an creates an instance of the ASS_AnswerMultipleResponse object.
    *
    * @param string $answertext A string defining the answer text
    * @param double $points The number of points given for the selected answer
    * @param integer $order A nonnegative value representing a possible display or sort order
    * @param double $points_unchecked The points when the answer is not checked
    * @param string $a_image The image filename
    * @access public
    */
    public function __construct(string $answertext = "", float $points = 0.0, int $order = 0, int $id = -1, int $state = 0)
    {
        parent::__construct($answertext, $points, $order, $id, $state);
    }


    /**
    * Gets the image filename
    *
    * Returns the image filename

    * @return string The image filename
    * @access public
    * @see $image
    */
    public function getImage() : ?string
    {
        return $this->image;
    }

    /**
    * Sets the image filename
    *
    * Sets the image filename
    *
    * @param string $a_image The image filename
    * @access public
    * @see $image
    */
    public function setImage($a_image = 0) : void
    {
        $this->image = $a_image;
    }
}
