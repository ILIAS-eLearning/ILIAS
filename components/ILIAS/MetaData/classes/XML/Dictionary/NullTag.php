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

namespace ILIAS\MetaData\XML\Dictionary;

use ILIAS\MetaData\XML\Version;

class NullTag implements TagInterface
{
    public function version(): Version
    {
        return Version::V10_0;
    }
    public function isExportedAsLangString(): bool
    {
        return false;
    }

    public function isTranslatedAsCopyright(): bool
    {
        return false;
    }

    public function isOmitted(): bool
    {
        return false;
    }

    public function isExportedAsAttribute(): bool
    {
        return false;
    }

    public function isRestrictedToIndices(): bool
    {
        return false;
    }

    public function indices(): \Generator
    {
        yield from [];
    }
}
