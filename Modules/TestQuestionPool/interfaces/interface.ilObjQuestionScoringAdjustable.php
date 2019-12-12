<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilObjQuestionScoringAdjustable
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
interface ilObjQuestionScoringAdjustable
{
    /**
     * Saves a record to the question types additional data table.
     *
     * @return mixed
     */
    public function saveAdditionalQuestionDataToDb();
}
