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

namespace ILIAS\BookingManager;

use ILIAS\Repository\BaseGUIRequest;

class StandardGUIRequest
{
    use BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getPoolRefId(): int
    {
        return $this->int("pool_ref_id");
    }

    public function getObjectId(): int
    {
        return $this->int("object_id");
    }

    public function getUserId(): int
    {
        return $this->int("user_id");
    }

    public function getBookedUser(): int
    {
        return $this->int("bkusr");
    }

    public function getScheduleId(): int
    {
        return $this->int("schedule_id");
    }

    public function getStatus(): int
    {
        return $this->int("tstatus");
    }

    public function getReservationIdsFromString(): array
    {
        return explode(";", $this->str("rsv_ids"));
    }

    public function getReturnTo(): string
    {
        return $this->str("return_to");
    }

    public function getReservationId(): string
    {
        return $this->str("reservation_id");
    }

    public function getReservationIds(): array
    {
        $ids = $this->strArray("mrsv");
        if (count($ids) === 0) {
            $ids = $this->strArray("reservation_id");
        }
        if (count($ids) === 0) {
            $ids = $this->strArray("rsv_id");
        }
        return $ids;
    }

    public function getSeed(): string
    {
        return $this->str("seed");
    }

    public function getSSeed(): string
    {
        return $this->str("sseed");
    }

    public function getNotification(): int
    {
        return $this->int("ntf");
    }

    public function getParticipants(): array
    {
        $p = $this->intArray("mass");
        if (count($p) === 0) {
            $p = $this->intArray("participants");
        }
        return $p;
    }

    public function getDates(): array
    {
        return $this->strArray("date");
    }

    public function getRece(): string
    {
        return $this->str("rece");
    }

    public function getRecm(): string
    {
        return $this->str("recm");
    }

    public function getUserLogin(): string
    {
        return $this->str("user_login");
    }

    public function getGroupId(): int
    {
        return $this->int("grp_id");
    }

    public function getCancelNr($id): int
    {
        return $this->int("rsv_id_" . $id);
    }
}
