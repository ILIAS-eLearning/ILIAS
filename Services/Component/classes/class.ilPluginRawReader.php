<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

class ilPluginRawReader
{
    const SEARCH_PATTERN = 'plugin.php';

    public function getPluginNames() : ?\Iterator
    {
        if (!@is_dir(ilComponentRepository::PLUGIN_BASE_PATH)) {
            throw new LogicException('Path not found: ' . ilComponentRepository::PLUGIN_BASE_PATH);
        }

        $it = new RecursiveDirectoryIterator(ilComponentRepository::PLUGIN_BASE_PATH);
        foreach (new RecursiveIteratorIterator($it) as $file) {
            $path = $file->getPathName();
            if (is_file($path) && basename($path) === self::SEARCH_PATTERN) {
                yield basename(dirname($path));
            }
        }
    }

    public function hasPlugin($name) : bool
    {
        $names = iterator_to_array($this->getPluginNames());
        return in_array($name, $names);
    }
}
