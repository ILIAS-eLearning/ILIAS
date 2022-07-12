<?php declare(strict_types=1);

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

namespace ILIAS\Style\Content\Container;

use ilDBInterface;

/**
 * This repo stores infos on repository objects that are using booking managers as a service
 * (resource management).
 * @author Alexander Killing <killing@leifos.de>
 */
class ContainerDBRepository
{
    const TABLE_NAME = 'sty_rep_container';

    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function updateReuse(int $ref_id, bool $reuse) : void
    {
        $db = $this->db;

        $db->replace(
            self::TABLE_NAME,
            [
                "ref_id" => ["integer", $ref_id]
            ],
            [
                "reuse" => ["integer", (int) $reuse]
            ]
        );
    }

    public function readReuse(int $ref_id) : bool
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM " . self::TABLE_NAME .
            " WHERE ref_id = %s ",
            ["integer"],
            [$ref_id]
        );
        $rec = $db->fetchAssoc($set);

        return (bool) ($rec["reuse"] ?? false);
    }

    /**
     * For an array of ref ids, return only the ref ids
     * that have the reuse flag set.
     */
    public function filterByReuse(array $ref_ids) : array
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM " . self::TABLE_NAME .
            " WHERE reuse = %s AND " . $db->in("ref_id", $ref_ids, false, "integer"),
            ["integer"],
            [1]
        );
        $ref_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $ref_ids[] = (int) $rec["ref_id"];
        }
        return $ref_ids;
    }
}
