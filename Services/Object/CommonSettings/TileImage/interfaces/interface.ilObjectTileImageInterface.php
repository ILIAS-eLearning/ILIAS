<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\Filesystem\Exception\IOException;

interface ilObjectTileImageInterface
{
    public function getExtension() : string;

    public function copy(int $target_obj_id) : void;

    public function delete() : void;

    /**
     * Save image from request
     * @throws IllegalStateException
     * @throws FileNotFoundException
     * @throws IOException
     */
    public function saveFromHttpRequest(string $tmp_name) : void;

    public function exists() : bool;

    public function getFullPath() : string;

    public function createFromImportDir(string $source_dir, string $ext) : void;
}
