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

namespace ILIAS\MetaData\Repository\Utilities\Queries;

use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Repository\Dictionary\TagInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Results\RowInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Assignments\AssignmentRowInterface;

interface DatabaseQuerierInterface
{
    public function manipulate(
        RessourceIDInterface $ressource_id,
        AssignmentRowInterface $row
    ): void;

    /**
     * @return RowInterface[]
     */
    public function read(
        RessourceIDInterface $ressource_id,
        int $id_from_parent_table,
        TagInterface ...$tags
    ): \Generator;

    public function deleteAll(RessourceIDInterface $ressource_id): void;

    public function nextID(string $table): int;
}
