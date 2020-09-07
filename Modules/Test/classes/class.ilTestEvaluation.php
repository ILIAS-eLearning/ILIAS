<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestEvaluation
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var integer
     */
    protected $testId;

    /**
     * ilTestEvaluation constructor.
     * @param ilDBInterface $db
     * @param $testId
     */
    public function __construct(ilDBInterface $db, $testId)
    {
        $this->db = $db;
        $this->testId = $testId;
    }

    /**
     * @param $testId
     * @return array
     */
    public function getAllActivesPasses()
    {
        $query = "
			SELECT active_fi, pass
			FROM tst_active actives
			INNER JOIN tst_pass_result passes
			ON active_fi = active_id
			WHERE test_fi = %s
		";
        
        $res = $this->db->queryF($query, array('integer'), array($this->testId));
        
        $passes = array();
        
        while ($row = $this->db->fetchAssoc($res)) {
            if (!isset($passes[$row['active_fi']])) {
                $passes[$row['active_fi']] = array();
            }

            $passes[$row['active_fi']][] = $row['pass'];
        }
        
        return $passes;
    }
}
