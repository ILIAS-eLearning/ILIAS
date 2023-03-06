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
final class UnzipOptions extends Options
{
    protected ?string $zip_output_path = null;
    private bool $flat = false;
    private bool $overwrite = false;

    public function getZipOutputPath(): ?string
    {
        return $this->zip_output_path;
    }

    public function withZipOutputPath(string $zip_output_path): self
    {
        $clone = clone $this;
        $clone->zip_output_path = $zip_output_path;
        return $clone;
    }

    public function isFlat(): bool
    {
        return $this->flat;
    }

    public function isOverwrite(): bool
    {
        return $this->overwrite;
    }

    public function withFlat(bool $flat): self
    {
        $clone = clone $this;
        $clone->flat = $flat;
        return $clone;
    }

    public function withOverwrite(bool $overwrite): self
    {
        $clone = clone $this;
        $clone->overwrite = $overwrite;
        return $clone;
    }
}
