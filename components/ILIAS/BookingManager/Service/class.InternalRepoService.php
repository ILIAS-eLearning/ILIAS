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

namespace ILIAS\BookingManager;

use ILIAS\BookingManager\Reservation\ReservationTableSessionRepository;
use ILIAS\BookingManager\Objects\ObjectsDBRepository;
use ILIAS\BookingManager\Reservations\ReservationDBRepository;
use ILIAS\BookingManager\BookingProcess\SelectedObjectsDBRepository;
use ILIAS\BookingManager\Schedule\SchedulesDBRepository;
use ILIAS\BookginManager\Participants\ParticipantsRepository;
use ILIAS\BookingManager\Settings\SettingsDBRepository;
use ILIAS\Exercise\IRSS\IRSSWrapper;
use ILIAS\Exercise;

class InternalRepoService
{
    protected static array $instances = [];
    protected IRSSWrapper $irss_wrapper;

    public function __construct(
        protected InternalDataService $data,
        protected \ilDBInterface $db
    ) {
        $this->irss_wrapper = new IRSSWrapper(new Exercise\InternalDataService());
    }

    public function preferences(): \ilBookingPreferencesDBRepository
    {
        return self::$instances["preferences"] ??= new \ilBookingPreferencesDBRepository(
            $this->data,
            $this->db
        );
    }

    public function preferenceBasedBooking(): \ilBookingPrefBasedBookGatewayRepository
    {
        return self::$instances["preferenceBasedBooking"] ??= new \ilBookingPrefBasedBookGatewayRepository(
            $this->db
        );
    }

    public function reservationTable(): ReservationTableSessionRepository
    {
        return self::$instances["reservationTable"] ??= new ReservationTableSessionRepository();
    }

    public function objects(): ObjectsDBRepository
    {
        return self::$instances["objects"] ??= new ObjectsDBRepository(
            $this->irss_wrapper,
            $this->db
        );
    }

    public function schedules(): SchedulesDBRepository
    {
        return self::$instances["schedules"] ??= new SchedulesDBRepository(
            $this->db
        );
    }

    public function reservation(): ReservationDBRepository
    {
        return self::$instances["reservation"] ??= new ReservationDBRepository(
            $this->db
        );
    }

    /**
     * Get repo with reservation information preloaded for context obj ids
     * @param int[] $context_obj_ids
     */
    public function reservationWithContextObjCache(
        array $context_obj_ids
    ): ReservationDBRepository {
        return new ReservationDBRepository(
            $this->db,
            $context_obj_ids
        );
    }

    public function objectSelection(): SelectedObjectsDBRepository
    {
        return self::$instances["objectSelection"] ??= new SelectedObjectsDBRepository(
            $this->db
        );
    }

    public function participants(): ParticipantsRepository
    {
        return self::$instances["participants"] ??= new ParticipantsRepository(
            $this->db
        );
    }

    public function settings(): SettingsDBRepository
    {
        return self::$instances["settings"] ??= new SettingsDBRepository(
            $this->db,
            $this->data
        );
    }

}
