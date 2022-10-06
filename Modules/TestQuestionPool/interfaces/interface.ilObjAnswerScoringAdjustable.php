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
