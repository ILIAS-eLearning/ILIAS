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

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\BookingManager\BookingProcess\BookingProcessManager;
use ILIAS\BookingManager\Objects\ObjectsManager;
use ILIAS\BookingManager\Schedule\ScheduleManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    /**
     * @var ObjectsManager[]
     */
    protected static array $object_manager = [];
    /**
     * @var ScheduleManager[]
     */
    protected static array $schedule_manager = [];
    protected ?\ilLogger $book_log = null;
    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;

    public function __construct(
        Container $DIC,
        InternalRepoService $repo_service,
        InternalDataService $data_service
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->initDomainServices($DIC);
    }

    /*
    public function access(int $ref_id, int $user_id) : Access\AccessManager
    {
        return new Access\AccessManager(
            $this,
            $this->access,
            $ref_id,
            $user_id
        );
    }*/

    public function log(): \ilLogger
    {
        if (is_null($this->book_log)) {
            $this->book_log = $this->logger()->book();
        }
        return $this->book_log;
    }

    public function preferences(
        \ilObjBookingPool $pool
    ): \ilBookingPreferencesManager {
        return new \ilBookingPreferencesManager(
            $pool,
            $this->repo_service->preferenceBasedBooking()
        );
    }

    public function process(): BookingProcessManager
    {
        return new BookingProcessManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function objects(int $pool_id): ObjectsManager
    {
        if (!isset(self::$object_manager[$pool_id])) {
            self::$object_manager[$pool_id] = new ObjectsManager(
                $this->data_service,
                $this->repo_service,
                $this,
                $pool_id
            );
        }
        return self::$object_manager[$pool_id];
    }

    public function schedules(int $pool_id): ScheduleManager
    {
        if (!isset(self::$schedule_manager[$pool_id])) {
            self::$schedule_manager[$pool_id] = new ScheduleManager(
                $this->data_service,
                $this->repo_service,
                $this,
                $pool_id
            );
        }
        return self::$schedule_manager[$pool_id];
    }

    public function reservations(): Reservations\ReservationManager
    {
        return new Reservations\ReservationManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function participants(): Participants\ParticipantsManager
    {
        return new Participants\ParticipantsManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function objectSelection(int $pool_id): BookingProcess\ObjectSelectionManager
    {
        return new BookingProcess\ObjectSelectionManager(
            $this->data_service,
            $this->repo_service,
            $this,
            $pool_id
        );
    }

}
