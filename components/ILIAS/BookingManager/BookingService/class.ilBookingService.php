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
 * Low level api for booking service
 * @todo: integration into Service structure
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingService
{
    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = $db ?? $DIC->database();
    }

    public function cloneSettings(
        int $source_obj_id,
        int $target_obj_id
    ): void {
        $use_book_repo = new ilObjUseBookDBRepository($this->db);
        $book_ref_ids = $use_book_repo->getUsedBookingPools($source_obj_id);
        $use_book_repo->updateUsedBookingPools($target_obj_id, $book_ref_ids);
    }
}
