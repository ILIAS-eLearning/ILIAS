<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Render;

/**
 * Some Components need to resolve pathes to image-files.
 * However, Icon-sets, e.g., need to be customizable without changing code.
 */
interface ImagePathResolver
{
    public function resolveImagePath(string $image_path) : string;
}
