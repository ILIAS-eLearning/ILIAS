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

namespace ILIAS\Object\Properties\ObjectReferenceProperties;

interface ObjectReferencePropertiesRepository
{
    /**
     * @param array<int> $ids
     */
    public function preload(array $ref_ids): void;
    public function resetPreloadedData(): void;
    public function getFor(?int $object_reference_id): ObjectReferenceProperties;
    public function storePropertyAvailabilityPeriod(ObjectAvailabilityPeriodProperty $time_based_activation_property);
}
