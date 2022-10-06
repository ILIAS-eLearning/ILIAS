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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestRandomQuestionSetQuestion
{
    /**
     * @var integer
     */
    private $questionId = null;

    /**
     * @var integer
     */
    private $sequencePosition = null;

    /**
     * @var integer
     */
    private $sourcePoolDefinitionId = null;

    /**
     * @param int $questionId
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;
    }

    /**
     * @return int
     */
    public function getQuestionId(): ?int
    {
        return $this->questionId;
    }

    /**
     * @param int $sequencePosition
     */
    public function setSequencePosition($sequencePosition)
    {
        $this->sequencePosition = $sequencePosition;
    }

    /**
     * @return int
     */
    public function getSequencePosition(): ?int
    {
        return $this->sequencePosition;
    }

    /**
     * @param int $sourcePoolDefinitionId
     */
    public function setSourcePoolDefinitionId($sourcePoolDefinitionId)
    {
        $this->sourcePoolDefinitionId = $sourcePoolDefinitionId;
    }

    /**
     * @return int
     */
    public function getSourcePoolDefinitionId(): ?int
    {
        return $this->sourcePoolDefinitionId;
    }
}
