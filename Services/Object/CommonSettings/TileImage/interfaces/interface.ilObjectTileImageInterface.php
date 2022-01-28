<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilObjectTileImageInterface
{
    public function getExtension() : string;

    public function copy(int $target_obj_id) : void;

    public function delete() : void;

    /**
     * Save image from request
     * @param string $tmpname
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function saveFromHttpRequest(string $tmpname) : void;

    public function exists() : bool;

    public function getFullPath() : string;

    public function createFromImportDir(string $source_dir, string $ext) : void;
}
