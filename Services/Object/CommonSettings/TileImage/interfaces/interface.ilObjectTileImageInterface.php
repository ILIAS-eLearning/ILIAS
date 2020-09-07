<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tile image object
 *
 * @author killing@leifos.de
 * @ingroup ServicesObject
 */
interface ilObjectTileImageInterface
{
    /**
     * Get extenstion
     *
     * @return string
     */
    public function getExtension() : string;

    /**
     * Copy tile image to repository object
     * @param int $target_obj_id
     */
    public function copy(int $target_obj_id);

    /**
     * Delete tile image
     */
    public function delete();

    /**
     * Save image from request
     *
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function saveFromHttpRequest(string $tmpname);

    /**
     * Does tile image file exist?
     *
     * @return bool
     */
    public function exists() : bool;

    /**
     * Get full path of the tile image file
     *
     * @return string
     */
    public function getFullPath() : string;
}
