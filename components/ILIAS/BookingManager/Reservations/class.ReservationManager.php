<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\BookingManager\Reservations;

use ILIAS\BookingManager\InternalDataService;
use ILIAS\BookingManager\InternalRepoService;
use ILIAS\BookingManager\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ReservationManager
{
    protected InternalDataService $data;
    protected InternalRepoService $repo;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDataService $data,
        InternalRepoService $repo,
        InternalDomainService $domain
    ) {
        $this->data = $data;
        $this->repo = $repo;
        $this->domain = $domain;
    }

    public function getAvailableNr(
        int $object_id,
        int $from,
        int $to
    ) : int {
        $counter = \ilBookingReservation::getAvailableObject(array($object_id), $from, $to, false, true);
        return (int) $counter[$object_id];
    }
}