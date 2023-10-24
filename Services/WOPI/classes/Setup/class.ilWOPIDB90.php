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
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilWOPIDB90 implements \ilDatabaseUpdateSteps
{
    private ?ilDBInterface $db = null;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        // create app table
        $this->db->createTable('wopi_app', [
            'id' => [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
            ],
            'name' => [
                'type' => 'text',
                'length' => 256,
                'notnull' => true,
            ],
            'favicon' => [
                'type' => 'text',
                'length' => 256,
                'notnull' => false,
            ],
        ]);
        $this->db->addPrimaryKey('wopi_app', ['id']);
        $this->db->createSequence('wopi_app');
    }

    public function step_2(): void
    {
        // create action table
        $this->db->createTable('wopi_action', [
            'id' => [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
            ],
            'app_id' => [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
            ],
            'name' => [
                'type' => 'text',
                'length' => 256,
                'notnull' => true,
            ],
            'ext' => [
                'type' => 'text',
                'length' => 256,
                'notnull' => true,
            ],
            'urlsrc' => [
                'type' => 'text',
                'length' => 2048,
                'notnull' => true,
            ]
        ]);
        $this->db->addPrimaryKey('wopi_action', ['id']);
        $this->db->createSequence('wopi_action');
        $this->db->addIndex('wopi_action', ['app_id'], 'i1');
    }

}
