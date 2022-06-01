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

namespace ILIAS\Style\Content\Object;

use ilDBInterface;

/**
 * This repo stores infos on repository objects that are using booking managers as a service
 * (resource management).
 * @author Alexander Killing <killing@leifos.de>
 */
class ObjectDBRepository
{
    const DATA_TABLE_NAME = 'style_data';

    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * For an array of object IDs (objects using styles) get back
     * their owned styles (if any), object IDs without ownerships are removed
     * @param int[] $owner_obj_ids
     * @return array<int, int>
     */
    public function getOwnedStyles(array $owner_obj_ids) : array
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM style_usage su JOIN style_data sd " .
            " ON (su.style_id = sd.id AND su.obj_id = sd.owner_obj) " .
            " WHERE " . $db->in("su.obj_id", $owner_obj_ids, false, "integer"),
            [],
            []
        );

        $owned = [];
        while ($rec = $db->fetchAssoc($set)) {
            $owned[(int) $rec["owner_obj"]] = (int) $rec["style_id"];
        }
        return $owned;
    }

    // is a style owned by an object?
    public function isOwned(int $obj_id, int $style_id) : bool
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM style_data " .
            " WHERE id = %s AND owner_obj = %s",
            ["integer", "integer"],
            [$style_id, $obj_id]
        );

        if ($db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }
}
