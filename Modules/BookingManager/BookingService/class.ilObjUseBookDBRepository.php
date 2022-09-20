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

/**
 * This repo stores infos on repository objects that are using booking managers as a service
 * (resource management).
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjUseBookDBRepository
{
    protected const TABLE_NAME = 'book_obj_use_book';

    protected ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param int[] $book_obj_ids
     */
    public function updateUsedBookingPools(
        int $obj_id,
        array $book_obj_ids
    ): void {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM " . self::TABLE_NAME . " WHERE " .
            " obj_id = %s",
            array("integer"),
            array($obj_id)
        );

        foreach ($book_obj_ids as $id) {
            $db->insert(self::TABLE_NAME, array(
                "obj_id" => array("integer", $obj_id),
                "book_ref_id" => array("integer", $id)
            ));
        }
    }

    /**
     * @return int[] ref ids
     */
    public function getUsedBookingPools(int $obj_id): array
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM " . self::TABLE_NAME . " " .
            " WHERE obj_id = %s ",
            array("integer"),
            array($obj_id)
        );
        $book_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $book_ids[] = $rec["book_ref_id"];
        }
        return $book_ids;
    }
}
