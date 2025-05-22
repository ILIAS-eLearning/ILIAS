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

namespace ILIAS\MetaData\Vocabularies\Standard;

use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

class DatabaseGateway implements GatewayInterface
{
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function createDeactivationEntry(SlotIdentifier $slot): void
    {
        $this->db->insert(
            'il_md_vocab_inactive',
            ['slot' => [\ilDBConstants::T_TEXT, $slot->value]]
        );
    }

    public function deleteDeactivationEntry(SlotIdentifier $slot): void
    {
        $this->db->manipulate(
            'DELETE FROM il_md_vocab_inactive WHERE slot = ' .
            $this->db->quote($slot->value, \ilDBConstants::T_TEXT)
        );
    }

    public function doesDeactivationEntryExistForSlot(SlotIdentifier $slot): bool
    {
        $res = $this->db->query(
            'SELECT COUNT(*) AS count FROM il_md_vocab_inactive WHERE slot = ' .
            $this->db->quote($slot->value, \ilDBConstants::T_TEXT)
        );
        if ($row = $res->fetchAssoc()) {
            return $row['count'] > 0;
        }
        return false;
    }
}
