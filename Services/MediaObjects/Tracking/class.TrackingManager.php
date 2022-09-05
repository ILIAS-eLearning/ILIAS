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

namespace ILIAS\MediaObjects\Tracking;

use ILIAS\MediaObjects\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class TrackingManager
{
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain
    )
    {
        $this->domain = $domain;
    }

    public function saveCompletion(
        int $mob_id,
        int $ref_id,
        int $user_id = 0
    ) : void
    {
        if ($user_id === 0) {
            $user_id = $this->domain->user()->getId();
        }

        \ilChangeEvent::_recordReadEvent(
            "mob",
            $ref_id,
            $mob_id,
            $user_id
        );

        // trigger LP update
        \ilLPStatusWrapper::_updateStatus(
            \ilObject::_lookupObjId($ref_id),
            $user_id
        );
    }
}