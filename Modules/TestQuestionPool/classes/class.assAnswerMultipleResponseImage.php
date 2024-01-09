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

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

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
    public ?string $image = null;

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
    * @param integer $id The database id of the answer
    * @access public
    */
    public function __construct(
        string $answertext = '',
        float $points = 0.0,
        int $order = 0,
        int $id = -1,
        int $state = 0
    ) {
        parent::__construct($answertext, $points, $order, $id, $state);
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image = null): void
    {
        if ($image === '') {
            throw new \Exception('imagename must not be empty');
        }
        $this->image = $image;
    }

    public function hasImage(): bool
    {
        return $this->image !== null;
    }
}
