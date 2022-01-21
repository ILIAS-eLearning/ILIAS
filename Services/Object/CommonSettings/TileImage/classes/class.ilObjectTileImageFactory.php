<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilObjectTileImageFactory implements ilObjectTileImageFactoryInterface
{
    protected ilObjectService $service;

    public function __construct(ilObjectService $service)
    {
        $this->service = $service;
    }

    public function getSupportedFileExtensions() : array
    {
        return ['png', 'jpg', 'jpeg'];
    }

    public function getByObjId(int $obj_id) : ilObjectTileImage
    {
        return new ilObjectTileImage($this->service, $obj_id);
    }
}
