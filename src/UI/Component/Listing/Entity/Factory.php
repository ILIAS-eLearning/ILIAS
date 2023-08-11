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

namespace ILIAS\UI\Component\Listing\Entity;

/**
 * This is what a factory for EntityListings looks like
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     The Entity Listing yields uniform Entities according to a consumer
     *     defined concept and lists them one after the other.
     *
     * ---
     * @return \ILIAS\UI\Component\Listing\Entity\Standard
     */
    public function standard(RecordToEntity $entity_mapping): Standard;
}
