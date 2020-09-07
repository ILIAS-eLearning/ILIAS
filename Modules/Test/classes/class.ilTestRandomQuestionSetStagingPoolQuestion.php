<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    public function getTestId()
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
    public function getPoolId()
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
    public function getQuestionId()
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
