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

namespace ILIAS\BookingManager\Participants;

use ILIAS\BookingManager\InternalDataService;
use ILIAS\BookingManager\InternalRepoService;
use ILIAS\BookingManager\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ParticipantsManager
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

    public function createIfNotExisting(
        int $user_id,
        int $pool_id) : void
    {
        if (!\ilObjUser::_exists($user_id)) {
            throw new \ilException("User $user_id does not exist.");
        }
        if (!\ilObjBookingPool::_exists($pool_id)) {
            throw new \ilException("Booking Pool $pool_id does not exist.");
        }

        $participant = new \ilBookingParticipant($user_id, $pool_id);
    }

}