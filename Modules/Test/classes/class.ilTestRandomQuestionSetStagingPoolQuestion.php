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
    protected $test_id;

    /**
     * @var integer
     */
    protected $pool_id;

    /**
     * @var integer
     */
    protected $question_id;

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
        return $this->test_id;
    }

    public function setTestId(int $test_id)
    {
        $this->test_id = $test_id;
    }

    public function getPoolId(): int
    {
        return $this->pool_id;
    }

    public function setPoolId(int $pool_id)
    {
        $this->pool_id = $pool_id;
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function setQuestionId(int $question_id)
    {
        $this->question_id = $question_id;
    }

    public function saveQuestionStaging()
    {
        $next_id = $this->db->nextId('tst_rnd_cpy');

        $this->db->insert('tst_rnd_cpy', [
            'copy_id' => ['integer', $next_id],
            'tst_fi' => ['integer', $this->getTestId()],
            'qst_fi' => ['integer', $this->getQuestionId()],
            'qpl_fi' => ['integer', $this->getPoolId()]
        ]);
    }
}
