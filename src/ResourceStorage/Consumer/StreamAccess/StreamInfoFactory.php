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

namespace ILIAS\ResourceStorage\Consumer\StreamAccess;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Flavour\Flavour;
use ILIAS\ResourceStorage\Flavour\FlavourWrapper;
use ILIAS\ResourceStorage\Flavour\Streams\FlavourStream;
use ILIAS\ResourceStorage\Revision\Revision;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class StreamInfoFactory
{
    private string $storage_dir;
    private string $data_dir;

    public function __construct(string $storage_base_dir)
    {
        $this->storage_dir = $storage_base_dir;
        if (!is_dir($this->storage_dir)) {
            throw new \InvalidArgumentException('Storage directory does not exist');
        }
    }

    public function fromAccessKey(string $access_key): StreamInfo
    {
        $path = $this->unhash($access_key);
        $this->checkPath($path);
        $stream = Streams::ofResource(fopen($path, 'rb'));

        return new StreamInfo($stream, $access_key);
    }

    public function fromFileStream(FileStream $stream): StreamInfo
    {
        $path = $stream->getMetadata('uri');
        $this->checkPath($path);
        $access_key = $this->hash($path);

        return new StreamInfo($stream, $access_key);
    }

    private function checkPath(string $path): void
    {
        if (
            strpos($path, $this->storage_dir) === false
            || is_readable($path) === false
            || file_exists($path) === false
        ) {
            throw new \InvalidArgumentException("Invalid access key or path");
        }
    }


    private function hash(string $string): string
    {
        return bin2hex($string);
    }

    private function unhash(string $string): string
    {
        return hex2bin($string);
    }
}
