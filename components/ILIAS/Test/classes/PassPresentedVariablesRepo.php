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
 * Stores random-generated parts of questions
 * in order to present the user with a fixed question during a test attempt
 */
class PassPresentedVariablesRepo
{
    public const TABLE_NAME = 'tst_qst_var_presented';

    public function __construct(
        protected ilDBInterface $db
    ) {
    }

    public function getFor(
        int $question_id,
        int $active_id,
        int $pass
    ): ?array {
        $query = 'SELECT variable, value FROM ' . self::TABLE_NAME . ' WHERE '
            . 'question_id = %s AND '
            . 'active_id = %s AND '
            . 'pass = %s';

        $result = $this->db->queryF(
            $query,
            ['integer','integer','integer'],
            [$question_id, $active_id, $pass]
        );

        if($result->numRows() === 0) {
            return null;
        }

        $values = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $values[$row['variable']] = $row['value'];
        }
        return $values;
    }

    public function store(
        int $question_id,
        int $active_id,
        int $pass,
        array $values
    ): void {
        $query = 'INSERT INTO ' . self::TABLE_NAME
            . ' (question_id, active_id, pass, variable, value) '
            . ' VALUES (%s,%s,%s,%s,%s)';

        foreach ($values as $k => $v) {
            $this->db->manipulateF(
                $query,
                ['integer','integer','integer', 'text', 'text'],
                [$question_id, $active_id, $pass, $k, $v]
            );
        }
    }
}
