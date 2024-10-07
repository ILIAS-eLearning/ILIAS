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

namespace ILIAS\MetaData\Repository;

use ILIAS\MetaData\Elements\NullSet;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Repository\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Repository\Search\Filters\FilterInterface;

class NullRepository implements RepositoryInterface
{
    public function getMD(int $obj_id, int $sub_id, string $type): SetInterface
    {
        return new NullSet();
    }

    public function getMDOnPath(PathInterface $path, int $obj_id, int $sub_id, string $type): SetInterface
    {
        return new NullSet();
    }

    /**
     * @return RessourceIDInterface[]
     */
    public function searchMD(
        ClauseInterface $clause,
        ?int $limit,
        ?int $offset,
        FilterInterface ...$filters
    ): \Generator {
        yield from [];
    }

    public function manipulateMD(SetInterface $set): void
    {
    }

    public function transferMD(
        SetInterface $from_set,
        int $to_obj_id,
        int $to_sub_id,
        string $to_type,
        bool $throw_error_if_invalid
    ): void {
    }

    public function deleteAllMD(int $obj_id, int $sub_id, string $type): void
    {
    }
}
