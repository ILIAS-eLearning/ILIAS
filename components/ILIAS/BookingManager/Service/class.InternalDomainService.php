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

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\BookingManager\BookingProcess\BookingProcessManager;
use ILIAS\BookingManager\Objects\ObjectsManager;
use ILIAS\BookingManager\Schedule\ScheduleManager;
use ILIAS\User\UserEvent;
use ILIAS\BookingManager\Settings\SettingsManager;

/**
 * Author: Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    protected static array $instances = [];
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

    public function log(): \ilLogger
    {
        return self::$instances["book_log"] ??= $this->logger()->book();
    }

    public function preferences(
        \ilObjBookingPool $pool
    ): \ilBookingPreferencesManager {
        return self::$instances["preferences"][$pool->getId()] ??= new \ilBookingPreferencesManager(
            $pool,
            $this->repo_service->preferenceBasedBooking()
        );
    }

    public function process(): BookingProcessManager
    {
        return self::$instances["process"] ??= new BookingProcessManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function objects(int $pool_id): ObjectsManager
    {
        return self::$instances["objects"][$pool_id] ??= new ObjectsManager(
            $this->data_service,
            $this->repo_service,
            $this,
            new \ilBookObjectInfoStakeholder(),
            new \ilBookBookingInfoStakeholder(),
            $pool_id
        );
    }

    public function schedules(int $pool_id): ScheduleManager
    {
        return self::$instances["schedules"][$pool_id] ??= new ScheduleManager(
            $this->data_service,
            $this->repo_service,
            $this,
            $pool_id
        );
    }

    public function reservations(): Reservations\ReservationManager
    {
        return self::$instances["reservations"] ??= new Reservations\ReservationManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function participants(): Participants\ParticipantsManager
    {
        return self::$instances["participants"] ??= new Participants\ParticipantsManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function objectSelection(int $pool_id): BookingProcess\ObjectSelectionManager
    {
        return self::$instances["object_sel"][$pool_id] ??= new BookingProcess\ObjectSelectionManager(
            $this->data_service,
            $this->repo_service,
            $this,
            $pool_id
        );
    }

    public function userEvent(): UserEvent
    {
        return self::$instances["user_event"] ??= new UserEvent($this);
    }

    public function bookingSettings(): SettingsManager
    {
        return self::$instances["settings"] ??= new SettingsManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function access(): Access\AccessManager
    {
        return new Access\AccessManager(
            $this,
            $this->DIC->access()
        );
    }

}
