<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tile image factory
 *
 * @author killing@leifos.de
 * @ingroup ServicesObject
 */
interface ilObjectTileImageFactoryInterface
{
    /**
     * Get supported file extensions
     *
     * @return string[]
     */
    public function getSupportedFileExtensions() : array;

    /**
     * Get tile image by object id
     *
     * @param int $objId
     * @return ilObjectTileImage
     */
    public function getByObjId(int $obj_id) : ilObjectTileImage;
}
