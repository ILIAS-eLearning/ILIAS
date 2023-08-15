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

namespace ILIAS\Filesystem\Util\Archive;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ZipOptions extends Options
{
    private ?string $zip_output_path = null;
    private ?string $zip_output_name = null;
    private int $iterations = 1000;
    private int $deflate_level = 9;


    public function withZipOutputPath(string $zip_output_path): self
    {
        $clone = clone $this;
        $clone->zip_output_path = $zip_output_path;
        return $clone;
    }

    public function getZipOutputName(): ?string
    {
        return $this->zip_output_name;
    }

    public function withZipOutputName(string $zip_output_name): self
    {
        $clone = clone $this;
        $clone->zip_output_name = $zip_output_name;
        return $clone;
    }

    public function getZipOutputPath(): ?string
    {
        return $this->zip_output_path;
    }

    public function getIterations(): int
    {
        return $this->iterations;
    }

    public function getDeflateLevel(): int
    {
        return $this->deflate_level;
    }
}
