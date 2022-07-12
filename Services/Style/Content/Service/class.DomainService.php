<?php declare(strict_types = 1);

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

namespace ILIAS\Style\Content;

use ILIAS\Style\Content\Object\ObjectFacade;

/**
 * Facade for consumer domain interface
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class DomainService
{
    private InternalService $internal;

    public function __construct(
        InternalService $internal_service
    ) {
        $this->internal = $internal_service;
    }

    public function styleForObjId(int $obj_id) : ObjectFacade
    {
        return new ObjectFacade(
            $this->internal->data(),
            $this->internal->domain(),
            0,
            $obj_id
        );
    }

    public function styleForRefId(int $ref_id) : ObjectFacade
    {
        return new ObjectFacade(
            $this->internal->data(),
            $this->internal->domain(),
            $ref_id
        );
    }
}
