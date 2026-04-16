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

namespace ILIAS\ResourceStorage;

use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Manager\ContainerManager;
use ILIAS\ResourceStorage\Consumer\Consumers;
use ILIAS\ResourceStorage\Collection\Collections;
use ILIAS\ResourceStorage\Flavour\Flavours;
use ILIAS\ResourceStorage\Events\Subject;

/**
 * Public API interface for the ILIAS Resource Storage Service (IRSS).
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface IRSSServices
{
    public function manage(): Manager;

    public function manageContainer(): ContainerManager;

    public function consume(): Consumers;

    public function collection(): Collections;

    public function flavours(): Flavours;

    public function preload(array $identification_strings): void;

    /**
     * @param string[] $collection_identification_strings
     */
    public function preloadCollections(array $collection_identification_strings): void;

    public function events(): Subject;
}
