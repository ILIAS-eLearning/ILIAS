<?php declare(strict_types=1);

use ILIAS\UI\Implementation\Render\ImagePathResolver;

class ilImagePathResolver implements ImagePathResolver
{
    public function resolveImagePath(string $image_path) : string
    {
        return ilUtil::getImagePath($image_path);
    }
}
