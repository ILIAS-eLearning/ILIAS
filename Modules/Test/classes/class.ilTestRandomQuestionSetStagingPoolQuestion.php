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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestRandomQuestionSetStagingPoolQuestion
{
    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var integer
     */
    protected $testId;

    /**
     * @var integer
     */
    protected $poolId;

    /**
     * @var integer
     */
    protected $questionId;

    /**
     * @param ilDBInterface $db
     */
    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return int
     */
    public function getTestId(): int
    {
        return $this->testId;
    }

    /**
     * @param int $testId
     */
    public function setTestId($testId)
    {
        $this->testId = $testId;
    }

    /**
     * @return int
     */
    public function getPoolId(): int
    {
        return $this->poolId;
    }

    /**
     * @param int $poolId
     */
    public function setPoolId($poolId)
    {
        $this->poolId = $poolId;
    }

    /**
     * @return int
     */
    public function getQuestionId(): int
    {
        return $this->questionId;
    }

    /**
     * @param int $questionId
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;
    }

    public function saveQuestionStaging()
    {
        $nextId = $this->db->nextId('tst_rnd_cpy');

        $this->db->insert('tst_rnd_cpy', array(
            'copy_id' => array('integer', $nextId),
            'tst_fi' => array('integer', $this->getTestId()),
            'qst_fi' => array('integer', $this->getQuestionId()),
            'qpl_fi' => array('integer', $this->getPoolId())
        ));
    }
}
