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
 * Factory for reservation repo
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingReservationDBRepositoryFactory
{
    protected ilDBInterface $db;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * Get repo without any preloaded data
     */
    public function getRepo(): ilBookingReservationDBRepository
    {
        return new ilBookingReservationDBRepository($this->db);
    }

    /**
     * Get repo with reservation information preloaded for context obj ids
     * @param int[] $context_obj_ids
     */
    public function getRepoWithContextObjCache(
        array $context_obj_ids
    ): ilBookingReservationDBRepository {
        return new ilBookingReservationDBRepository($this->db, $context_obj_ids);
    }
}
