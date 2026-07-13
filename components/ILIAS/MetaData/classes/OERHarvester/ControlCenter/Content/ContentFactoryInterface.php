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

namespace ILIAS\MetaData\OERHarvester\ControlCenter\Content;

use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\StateInfoInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\Action;

interface ContentFactoryInterface
{
    public function getInfoContent(
        int $ref_id,
        int $obj_id,
        string $type,
        StateInfoInterface $state_info
    ): RoundTripModal;

    public function getConfirmationContent(
        int $ref_id,
        int $obj_id,
        string $type,
        Action $action,
        bool $is_last_reference
    ): RoundTripModal;

    public function getSuccessMessage(Action $action): string;
}
