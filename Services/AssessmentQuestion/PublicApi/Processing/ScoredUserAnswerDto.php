<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;

/**
 * Class UserAnswerScoring
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
interface ScoredUserAnswerDto
{

    public function getQuestionRevisionId() : AssessmentEntityId;


    public function getUserAnswerId() : AssessmentEntityId;


    public function getUserId() : int;


    public function getSubmittedOn();


    public function isCorrect() : bool;


    public function getPoints() : int;
}
