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

declare(strict_types=1);

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
 * @ingroup components\ILIASTestQuestionPool
 *
 * @see ASS_AnswerSimple
 */
class ASS_AnswerBinaryStateImage extends ASS_AnswerBinaryState
{
    protected ?string $image = null;

    /**
     * ASS_AnswerBinaryStateImage constructor
     * The constructor takes possible arguments an creates an instance of the ASS_AnswerBinaryStateImage object.
     * @param string  $answertext A string defining the answer text
     * @param double  $points     The number of points given for the selected answer
     * @param integer $order      A nonnegative value representing a possible display or sort order
     * @param integer $state      A integer value indicating the state of the answer
     * @param string  $a_image    The image filename
     * @param integer $id         The database id of the answer
     */
    public function __construct($answertext = "", $points = 0.0, $order = 0, $state = false, string $a_image = null, int $id = -1)
    {
        parent::__construct($answertext, (float) $points, $order, $state, $id);
        $this->setImage($a_image);
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image = null): void
    {
        if($image === '') {
            throw new \Exception('imagename must not be empty');
        }
        $this->image = $image;
    }

    public function hasImage(): bool
    {
        return $this->image !== null;
    }
}
