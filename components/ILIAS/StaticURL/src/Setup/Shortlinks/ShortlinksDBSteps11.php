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

namespace ILIAS\StaticURL\Setup\Shortlinks;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ShortlinksDBSteps11 implements \ilDatabaseUpdateSteps
{
    private \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $fields = [
            'id' => [
                'type' => 'text',
                'length' => 32,
                'notnull' => true,
            ],
            'alias' => [
                'type' => 'text',
                'length' => 255,
                'notnull' => true
            ],
            'target_type' => [
                'type' => 'text',
                'length' => 8,
                'notnull' => true
            ],
            'target_type_data' => [
                'type' => 'clob',
                'notnull' => false,
            ],
            'position' => [
                'type' => 'integer',
                'notnull' => true,
            ],
            'active' => [
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 1,
            ],
            'used' => [
                'type' => 'integer',
                'notnull' => true,
                'default' => 0,
            ],
        ];

        $this->db->createTable('il_shortlinks', $fields);

        $this->db->addPrimaryKey(
            'il_shortlinks',
            ['id']
        );

        $this->db->addIndex(
            'il_shortlinks',
            ['alias'],
            'i1'
        );
    }

}
