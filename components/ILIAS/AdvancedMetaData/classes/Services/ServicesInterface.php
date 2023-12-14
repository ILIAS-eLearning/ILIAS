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

namespace ILIAS\AdvancedMetaData\Services;

use ILIAS\AdvancedMetaData\Services\ObjectModes\ObjectModesInterface;
use ILIAS\AdvancedMetaData\Services\SubObjectModes\SubObjectModesInterface;

interface ServicesInterface
{
    public function forObject(
        string $type,
        int $ref_id,
        string $sub_type = '',
        int $sub_id = 0
    ): ObjectModesInterface;

    public function forSubObjects(
        string $type,
        int $ref_id,
        SubObjectIDInterface ...$sub_object_ids
    ): SubObjectModesInterface;

    public function getSubObjectID(
        int $obj_id,
        int $sub_id,
        string $sub_type
    ): SubObjectIDInterface;
}
