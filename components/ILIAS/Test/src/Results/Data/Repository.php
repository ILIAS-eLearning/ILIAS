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

namespace ILIAS\Test\Results\Data;

class Repository
{
    public function __construct(
        private readonly \ilDBInterface $db
    ) {
    }

    public function finalizeTestPassResult(int $active_id, int $pass, StatusOfAttempt $status_of_attempt): void
    {
        if (!$status_of_attempt->isFinished()) {
            throw new \RuntimeException('Status of attempt must be finished to finalize test pass result');
        }

        $this->db->manipulateF(
            'UPDATE tst_pass_result SET tstamp = %s, finalized_by = %s WHERE active_fi = %s AND pass = %s',
            ['integer', 'text', 'integer', 'integer'],
            [time(), $status_of_attempt->value, $active_id, $pass]
        );
    }
}
