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

namespace ILIAS\MetaData\Repository\Utilities;

use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Repository\Dictionary\TagInterface;

interface QueryExecutorInterface
{
    /**
     * Keys are the ids
     * @return string[]
     */
    public function read(
        TagInterface $tag,
        RessourceIDInterface $ressource_id,
        int $super_id,
        int ...$parent_ids
    ): \Generator;

    /**
     * Returns ID of created element.
     */
    public function create(
        TagInterface $tag,
        RessourceIDInterface $ressource_id,
        ?int $id,
        string $data_value,
        int $super_id,
        int ...$parent_ids
    ): int;

    public function update(
        TagInterface $tag,
        RessourceIDInterface $ressource_id,
        int $id,
        string $data_value,
        int $super_id,
        int ...$parent_ids
    ): void;

    public function delete(
        TagInterface $tag,
        RessourceIDInterface $ressource_id,
        int $id,
        int $super_id,
        int ...$parent_ids
    ): void;

    public function deleteAll(RessourceIDInterface $ressource_id): void;
}
