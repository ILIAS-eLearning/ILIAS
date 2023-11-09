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

namespace ILIAS\Object\Properties\ObjectTypeSpecificProperties;

use ILIAS\DI\Container;

interface ilObjectTypeSpecificProperties
{
    /**
     * @description This function MUST return the object type string as defined in ObjectDefinitions.
     */
    public function getObjectTypeString(): string;
    public function getModifications(): ?ilObjectTypeSpecificPropertyModifications;
    public function getProviders(): ?ilObjectTypeSpecificPropertyProviders;

    /**
     * @description To avoid too many roundtrips to the persistence layer on lists
     * of objects, please implement an efficient query to preload the data we will
     * need.
     *
     * @param array<int> $object_ids
     */
    public function preload(array $object_ids): void;
}
