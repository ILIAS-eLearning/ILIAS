<?php

declare(strict_types=1);

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

use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\Filesystem\Exception\IOException;

interface ilObjectTileImageInterface
{
    public function getExtension(): string;

    public function copy(int $target_obj_id): void;

    public function delete(): void;

    /**
     * Save image from request
     * @throws IllegalStateException
     * @throws FileNotFoundException
     * @throws IOException
     */
    public function saveFromHttpRequest(string $tmp_name): void;

    public function exists(): bool;

    public function getFullPath(): string;

    public function createFromImportDir(string $source_dir, string $ext): void;
}
