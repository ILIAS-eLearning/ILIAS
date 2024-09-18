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

namespace ILIAS\Survey\Metadata;

use ILIAS\MetaData\Services\ServicesInterface as LOMServices;

class MetadataManager
{
    protected LOMServices $lom_services;

    public function __construct(LOMServices $lom_services)
    {
        $this->lom_services = $lom_services;
    }

    public function getAuthorsFromLOM(
        int $obj_id,
        int $sub_id,
        string $type
    ): string {
        $path_to_authors = $this->lom_services->paths()->authors();
        $author_data = $this->lom_services->read($obj_id, $sub_id, $type, $path_to_authors)
                                          ->allData($path_to_authors);

        return $this->lom_services->dataHelper()->makePresentableAsList(',', ...$author_data);
    }

    public function saveAuthorsInLOMIfNoLifecycleSet(
        int $obj_id,
        int $sub_id,
        string $type,
        string $author
    ): void {
        $path_to_lifecycle = $this->lom_services->paths()->custom()->withNextStep('lifeCycle')->get();
        $path_to_authors = $this->lom_services->paths()->authors();

        $reader = $this->lom_services->read($obj_id, $sub_id, $type, $path_to_lifecycle);
        if (!is_null($reader->allData($path_to_lifecycle)->current())) {
            return;
        }

        $this->lom_services->manipulate($obj_id, $sub_id, $type)
                           ->prepareCreateOrUpdate($path_to_authors, $author)
                           ->execute();
    }
}
