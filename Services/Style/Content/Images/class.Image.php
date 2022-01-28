<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

use \ILIAS\Data\DataSize;

/**
 * Image of style
 * @author Alexander Killing <killing@leifos.de>
 */
class Image
{
    /**
     * @var int
     */
    protected $path;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int size in bytes
     */
    protected $size;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    public function __construct(
        string $path,
        DataSize $size,
        int $width,
        int $height
    ) {
        $this->path = $path;
        $this->width = $width;
        $this->height = $height;
        $this->size = $size;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function getFilename() : string
    {
        return basename($this->path);
    }

    public function getType() : string
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    public function getSize() : DataSize
    {
        return $this->size;
    }

    public function getWidth() : int
    {
        return $this->width;
    }

    public function getHeight() : int
    {
        return $this->height;
    }
}
