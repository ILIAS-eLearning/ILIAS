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

use ILIAS\Notifications\ilNotificationSetupHelper;

readonly class ilNotificationUpdateSteps11 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableExists('push_subscriptions')) {
            $this->db->createTable(
                'push_subscriptions',
                [
                    "endpoint" => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                    ],
                    "user_id" => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                    ],
                    "p256dh" => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 87
                    ],
                    "auth" => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 22
                    ],
                ]
            );
            $this->db->addIndex('push_subscriptions', ['user_id'], 'i1');
            $this->db->addPrimaryKey('push_subscriptions', ['auth']);
        }
    }
}
