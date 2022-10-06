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
class ilTestEvaluation
{
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
    public function getAllActivesPasses(): array
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
