<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * tile image factory
 *
 * @author killing@leifos.de
 * @ingroup ServicesObject
 */
class ilObjectTileImageFactory implements ilObjectTileImageFactoryInterface
{
    /**
     * @var ilObjectService
     */
    protected $service;

    /**
     * Constructor
     * @param ilObjectService $service
     */
    public function __construct(ilObjectService $service)
    {
        $this->service = $service;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedFileExtensions() : array
    {
        return ["png", "jpg", "jpeg"];
    }

    /**
     * @inheritdoc
     */
    public function getByObjId(int $obj_id) : ilObjectTileImage
    {
        return new \ilObjectTileImage($this->service, $obj_id);
    }
}
