<?php

namespace ILIAS\MetaData\Repository;

use ILIAS\MetaData\Elements\NullSet;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Repository\Utilities\NullScaffoldProvider;
use ILIAS\MetaData\Repository\Utilities\ScaffoldProviderInterface;

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

    public function scaffolds(): ScaffoldProviderInterface
    {
        return new NullScaffoldProvider();
    }

    public function manipulateMD(SetInterface $set): void
    {
    }

    public function deleteAllMD(int $obj_id, int $sub_id, string $type): void
    {
    }
}
