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

namespace ILIAS\Notifications;

use ilDBInterface;
use ilDBStatement;
use Iterator;
use ilDBConstants;

/**
 * @author Jan Posselt <jposselt@databay.de>
 * @implements Iterator<int, array<string, mixed>>
 */
class ilNotificationUserIterator implements Iterator
{
    private ilDBStatement $rset;
    private readonly ilDBInterface $db;
    /** @var array<string, mixed>|null */
    private ?array $data = null;

    /**
     * @param list<int> $userids
     */
    public function __construct(private readonly string $module, private readonly array $userids = [])
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->rewind();
    }

    public function __destruct()
    {
        $this->db->free($this->rset);
    }

    /**
     * @return array<string, mixed>
     */
    public function current(): array
    {
        return $this->data;
    }

    public function key(): int
    {
        return (int) $this->data['usr_id'];
    }

    public function next(): void
    {
    }

    public function rewind(): void
    {
        $query = 'SELECT usr_id, module, channel FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE module = %s AND ' . $this->db->in(
            'usr_id',
            $this->userids,
            false,
            ilDBConstants::T_INTEGER
        );
        $types = [ilDBConstants::T_TEXT];
        $values = [$this->module];
        $this->rset = $this->db->queryF($query, $types, $values);
    }

    public function valid(): bool
    {
        $this->data = $this->db->fetchAssoc($this->rset);

        return is_array($this->data);
    }
}
