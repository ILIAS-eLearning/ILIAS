<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilObjectTileImageFactoryInterface
{
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array;

    public function getByObjId(int $obj_id) : ilObjectTileImage;
}
