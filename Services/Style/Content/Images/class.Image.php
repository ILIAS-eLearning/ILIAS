<?php declare(strict_types=1);

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

namespace ILIAS\Style\Content;

use ILIAS\Data\DataSize;

/**
 * Image of style
 * @author Alexander Killing <killing@leifos.de>
 */
class Image
{
    protected string $path;
    protected string $type;
    protected DataSize $size;
    protected int $width;
    protected int $height;

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
