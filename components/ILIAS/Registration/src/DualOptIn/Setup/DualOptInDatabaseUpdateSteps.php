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

namespace ILIAS\Registration\DualOptIn\Setup;

use ilDatabaseUpdateSteps;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Data\UUID\Factory as UUIDFactory;

class DualOptInDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableExists('reg_dual_opt_in')) {
            return;
        }

        $fields = [
            'id' => [
                'type' => ilDBConstants::T_TEXT,
                'length' => 36,
                'fixed' => true,
                'notnull' => true,
            ],
            'usr_id' => [
                'type' => ilDBConstants::T_INTEGER,
                'length' => 8,
                'notnull' => true
            ],
            'reg_hash' => [
                'type' => ilDBConstants::T_TEXT,
                'length' => 16,
                'fixed' => true,
                'notnull' => true
            ],
            'creation_date' => [
                'type' => ilDBConstants::T_INTEGER,
                'length' => 8,
                'notnull' => true
            ]
        ];

        $this->db->createTable('reg_dual_opt_in', $fields);
        $this->db->addPrimaryKey('reg_dual_opt_in', ['id']);
    }

    public function step_2(): void
    {
        if (
            !$this->db->tableExists('usr_data') ||
            !$this->db->tableColumnExists('usr_data', 'reg_hash')
        ) {
            return;
        }

        $res = $this->db->query(
            "SELECT usr_id, reg_hash, create_date FROM usr_data WHERE reg_hash IS NOT NULL AND reg_hash <> ''"
        );

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->db->manipulateF(
                'INSERT INTO reg_dual_opt_in (id, usr_id, reg_hash, creation_date) VALUES (%s, %s, %s)',
                [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER],
                [(new UUIDFactory())->uuid4(), $row->usr_id, $row->reg_hash, $row->create_date]
            );
        }
    }

    public function step_3(): void
    {
        if (
            !$this->db->tableExists('usr_data') ||
            !$this->db->tableColumnExists('usr_data', 'reg_hash')
        ) {
            return;
        }

        $this->db->dropTableColumn('usr_data', 'reg_hash');
    }

    public function step_4(): void
    {
        if (
            $this->db->tableExists('reg_dual_opt_in') &&
            !$this->db->indexExistsByFields('reg_dual_opt_in', ['reg_hash'])
        ) {
            $this->db->addIndex('reg_dual_opt_in', ['reg_hash'], 'i1');
        }

        if (
            $this->db->tableExists('reg_dual_opt_in') &&
            !$this->db->indexExistsByFields('reg_dual_opt_in', ['usr_id'])
        ) {
            $this->db->addIndex('reg_dual_opt_in', ['usr_id'], 'i2');
        }
    }
}
