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

namespace ILIAS\Mail\Setup;

use ilDBConstants;
use ilDBInterface;
use ilDatabaseUpdateSteps;

class MailDBUpdateSteps11 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->update(
            'mail_obj_data',
            ['title' => [ilDBConstants::T_TEXT, 'f_sent']],
            ['m_type' => [ilDBConstants::T_TEXT, 'sent'], 'title' => [ilDBConstants::T_TEXT, 'e_sent']]
        );
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists('mail', 'schedule_datetime')) {
            $this->db->addTableColumn(
                'mail',
                'schedule_datetime',
                [
                    'type' => ilDBConstants::T_TIMESTAMP,
                    'notnull' => false,
                    'default' => null,
                ]
            );
        }
        if (!$this->db->tableColumnExists('mail', 'schedule_timezone')) {
            $this->db->addTableColumn(
                'mail',
                'schedule_timezone',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 32,
                    'notnull' => false,
                    'default' => null
                ]
            );
        }
    }
}
