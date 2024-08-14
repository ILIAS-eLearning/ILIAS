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

namespace ILIAS\User\Setup;

use ILIAS\User\Profile\ChangeMailTokenDBRepository;

class DBUpdateSteps10 implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists(ChangeMailTokenDBRepository::TABLE_NAME, 'status')) {
            $this->db->addTableColumn(
                ChangeMailTokenDBRepository::TABLE_NAME,
                'status',
                [
                    'type' => \ilDBConstants::T_INTEGER,
                    'notnull' => false,
                    'length' => 1
                ]
            );
        }
        if ($this->db->tableColumnExists(ChangeMailTokenDBRepository::TABLE_NAME, 'valid_until')) {
            $this->db->renameTableColumn(
                ChangeMailTokenDBRepository::TABLE_NAME,
                'valid_until',
                'created_ts'
            );
        }
        if (!$this->db->indexExistsByFields(ChangeMailTokenDBRepository::TABLE_NAME, ['token'])) {
            $this->db->manipulate('DELETE token1 FROM ' . ChangeMailTokenDBRepository::TABLE_NAME . ' token1 '
                . 'INNER JOIN ' . ChangeMailTokenDBRepository::TABLE_NAME . ' token2 '
                . 'WHERE token1.token = token2.token AND token1.created_ts < token2.created_ts');
            $this->db->addPrimaryKey(ChangeMailTokenDBRepository::TABLE_NAME, ['token']);
        }
    }
}
