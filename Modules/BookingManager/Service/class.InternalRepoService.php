<?php

declare(strict_types=1);

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

use ILIAS\BookingManager\Reservation\ReservationTableSessionRepository;
use ILIAS\BookingManager\Objects\ObjectsDBRepository;
use ILIAS\BookingManager\Reservations\ReservationDBRepository;
use ILIAS\BookingManager\BookingProcess\SelectedObjectsDBRepository;
use ILIAS\BookingManager\Schedule\SchedulesDBRepository;

/**
 * Repository internal repo service
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalRepoService
{
    protected InternalDataService $data;
    protected \ilDBInterface $db;

    public function __construct(InternalDataService $data, \ilDBInterface $db)
    {
        $this->data = $data;
        $this->db = $db;
    }

    /*
    public function ...() : ...\RepoService
    {
        return new ...\RepoService(
            $this->data,
            $this->db
        );
    }*/

    public function preferences(): \ilBookingPreferencesDBRepository
    {
        return new \ilBookingPreferencesDBRepository(
            $this->data,
            $this->db
        );
    }

    public function preferenceBasedBooking(): \ilBookingPrefBasedBookGatewayRepository
    {
        return new \ilBookingPrefBasedBookGatewayRepository(
            $this->db
        );
    }

    public function reservationTable(): ReservationTableSessionRepository
    {
        return new ReservationTableSessionRepository();
    }

    public function objects() : ObjectsDBRepository
    {
        return new ObjectsDBRepository(
            $this->db
        );
    }

    public function schedules() : SchedulesDBRepository
    {
        return new SchedulesDBRepository(
            $this->db
        );
    }

    public function reservation() : ReservationDBRepository
    {
        return new ReservationDBRepository($this->db);
    }

    /**
     * Get repo with reservation information preloaded for context obj ids
     * @param int[] $context_obj_ids
     */
    public function reservationWithContextObjCache(
        array $context_obj_ids
    ) : ReservationDBRepository {
        return new ReservationDBRepository($this->db, $context_obj_ids);
    }

    public function objectSelection() : SelectedObjectsDBRepository
    {
        return new SelectedObjectsDBRepository($this->db);
    }

}
