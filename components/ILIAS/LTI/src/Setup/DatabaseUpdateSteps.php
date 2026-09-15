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

namespace ILIAS\LTI\Setup;

class DatabaseUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableExists('lti2_tool')) {
            return;
        }

        $this->db->createTable('lti2_tool', [
            'tool_pk' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
            'name' => ['type' => 'text', 'length' => 255, 'notnull' => true],
            'consumer_key' => ['type' => 'text', 'length' => 255, 'notnull' => false],
            'secret' => ['type' => 'text', 'length' => 255, 'notnull' => false],
            'message_url' => ['type' => 'text', 'length' => 255, 'notnull' => false],
            'initiate_login_url' => ['type' => 'text', 'length' => 255, 'notnull' => false],
            'redirection_uris' => ['type' => 'clob', 'notnull' => false],
            'public_key' => ['type' => 'clob', 'notnull' => false],
            'lti_version' => ['type' => 'text', 'length' => 10, 'notnull' => false],
            'signature_method' => ['type' => 'text', 'length' => 15, 'notnull' => false],
            'settings' => ['type' => 'clob', 'notnull' => false],
            'enabled' => ['type' => 'integer', 'length' => 1, 'notnull' => true, 'default' => 0],
            'enable_from' => ['type' => 'timestamp', 'notnull' => false],
            'enable_until' => ['type' => 'timestamp', 'notnull' => false],
            'last_access' => ['type' => 'date', 'notnull' => false],
            'created' => ['type' => 'timestamp', 'notnull' => true],
            'updated' => ['type' => 'timestamp', 'notnull' => true],
        ]);
        $this->db->addPrimaryKey('lti2_tool', ['tool_pk']);
        $this->db->createSequence('lti2_tool');
    }
}
