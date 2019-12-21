<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';

/**
 * Class for answers with a binary state indicator
 *
 * ASS_AnswerBinaryStateImage is a class for answers with a binary state
 * indicator (checked/unchecked, set/unset) and an image file
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 *
 * @see ASS_AnswerSimple
 */
class ASS_AnswerBinaryStateImage extends ASS_AnswerBinaryState
{

    /**
     * Image filename
     *
     * @var string
     */
    protected $image;

    /**
     * ASS_AnswerBinaryStateImage constructor
     *
     * The constructor takes possible arguments an creates an instance of the ASS_AnswerBinaryStateImage object.
     *
     * @param string  $answertext A string defining the answer text
     * @param double  $points     The number of points given for the selected answer
     * @param integer $order      A nonnegative value representing a possible display or sort order
     * @param integer $state      A integer value indicating the state of the answer
     * @param string  $a_image    The image filename
     * @param integer $id         The database id of the answer
     *
     * @return ASS_AnswerBinaryStateImage
     */
    public function __construct($answertext = "", $points = 0.0, $order = 0, $state = 0, $a_image = "", $id = -1)
    {
        parent::__construct($answertext, $points, $order, $id);
        $this->image = $a_image;
    }

    /**
     * Gets the image filename
     *
     * Returns the image filename
     *
     * @return string The image filename
     * @see $image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Sets the image filename
     *
     * Sets the image filename
     *
     * @param int|string $a_image The image filename
     *
     * @see $image
     */
    public function setImage($a_image = 0)
    {
        $this->image = $a_image;
    }
}
