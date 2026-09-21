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

final class InlineImage
{
    private const string CID_PREFIX = 'img/';

    public function __construct(private readonly AssetFile $file)
    {
    }

    public static function at(string $path): self
    {
        return new self(new AssetFile($path));
    }

    public function cid(): string
    {
        return self::CID_PREFIX . $this->file->name();
    }

    public function name(): string
    {
        return $this->file->name();
    }

    public function path(): string
    {
        return $this->file->path();
    }

    public function stem(): string
    {
        return $this->file->stem();
    }
}
