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
 * Value object for booking service settings of a repository object
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjBookingServiceSettings
{
    // repository object id (e.g. a course)
    protected int $obj_id;
    /** @var int[] */
    protected array $book_obj_ids;

    /**
     * @param int $obj_id
     * @param int[] $book_obj_ids
     */
    public function __construct(
        int $obj_id,
        array $book_obj_ids
    ) {
        $this->obj_id = $obj_id;
        $this->book_obj_ids = $book_obj_ids;
    }

    // Get object id of repo object
    public function getObjectId(): int
    {
        return $this->obj_id;
    }

    /**
     * Get used booking object ids
     * @return int[]
     */
    public function getUsedBookingObjectIds(): array
    {
        return $this->book_obj_ids;
    }
}
