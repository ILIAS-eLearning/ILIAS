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

namespace ILIAS\ResourceStorage\Resource\Repository;

use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Flavour;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Preloader\PreloadableRepository;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface FlavourRepository extends PreloadableRepository
{
    public function has(ResourceIdentification $rid, int $revision, FlavourDefinition $definition): bool;

    public function store(Flavour $flavour): void;

    public function get(ResourceIdentification $rid, int $revision, FlavourDefinition $definition): Flavour;

    public function delete(Flavour $flavour): void;
}
