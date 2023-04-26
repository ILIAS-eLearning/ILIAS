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

use ILIAS\Cache\Nodes\NodeRepository;
use ILIAS\Cache\Nodes\Node;

class ilMemcacheNodesRepository implements NodeRepository
{
    public const TABLE_NAME = "il_gc_memcache_server";

    public function __construct(private ?ilDBInterface $db = null)
    {
    }

    public function deleteAll(): void
    {
        if ($this->db === null) {
            return;
        }
        $this->db->manipulate("TRUNCATE TABLE " . self::TABLE_NAME);
    }

    public function store(Node $node): Node
    {
        if ($this->db === null) {
            return $node;
        }
        $this->create(
            $node->getHost(),
            $node->getPort(),
            $node->getWeight()
        );
        return $node;
    }

    public function create(
        string $host,
        int $port,
        int $weight
    ): Node {
        $node = new Node($host, $port, $weight);
        if ($this->db != null) {
            $next_id = $this->db->nextId(self::TABLE_NAME);
            $this->db->insert(self::TABLE_NAME, [
                "id" => ["integer", $next_id],
                "status" => ["integer", true],
                "host" => ["text", $node->getHost()],
                "port" => ["integer", $node->getPort()],
                "weight" => ["integer", $node->getWeight()],
                "flush_needed" => ["integer", false]
            ]);
        }
        return $node;
    }

    public function getNodes(): array
    {
        if ($this->db === null) {
            return [];
        }
        $set = $this->db->query("SELECT * FROM " . self::TABLE_NAME);
        $nodes = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $nodes[] = new Node(
                $rec["host"],
                (int) $rec["port"],
                (int) $rec["weight"]
            );
        }
        return $nodes;
    }
}
