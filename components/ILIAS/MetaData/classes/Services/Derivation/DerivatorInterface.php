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

namespace ILIAS\MetaData\Services\Derivation;

interface DerivatorInterface
{
    /**
     * Writes the LOM set derived from the source to the given object. Always deletes whatever
     * metadata already exist at the target. See {@see \ILIAS\MetaData\Services\ServicesInterface::read()}
     * for a description of the parameters.
     *
     * @throws \ilMDServicesException if the set contains invalid manipulations
     */
    public function forObject(int $obj_id, int $sub_id, string $type): void;
}
