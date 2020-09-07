<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilObjAnswerScoringAdjustable
 *
 * This is the extended interface for questions, which support the relevant object-class methods for post-test-scoring
 * adjustments. This is the object-part.
 *
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup 	ModulesTestQuestionPool
 */
interface ilObjAnswerScoringAdjustable
{
    /**
     * Saves the answer specific records into a question types answer table.
     *
     * @return mixed
     */
    public function saveAnswerSpecificDataToDb();
}
