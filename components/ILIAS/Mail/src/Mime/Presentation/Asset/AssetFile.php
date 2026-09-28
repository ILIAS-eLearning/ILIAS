<?php

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

declare(strict_types=1);

namespace ILIAS\Mail\Mime\Presentation\Asset;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;

/**
 * A readable file an HTML mail is built from.
 *
 * Takes a path rather than an {@see \SplFileInfo} on purpose: a {@see \DirectoryIterator} hands
 * out the very same instance on every step, so holding on to one is unsafe.
 */
final class AssetFile
{
    public function __construct(private readonly string $path)
    {
        if (!is_file($this->path) || !is_readable($this->path)) {
            throw new MailAssetUnavailable("Cannot read mail asset at '{$this->path}'.");
        }
    }

    public function path(): string
    {
        return $this->path;
    }

    public function name(): string
    {
        return basename($this->path);
    }

    public function stem(): string
    {
        return strtolower(pathinfo($this->path, PATHINFO_FILENAME));
    }

    public function stream(): FileStream
    {
        $handle = fopen($this->path, 'rb');

        if ($handle === false) {
            throw new MailAssetUnavailable("Cannot open mail asset at '{$this->path}'.");
        }

        return Streams::ofResource($handle);
    }
}
