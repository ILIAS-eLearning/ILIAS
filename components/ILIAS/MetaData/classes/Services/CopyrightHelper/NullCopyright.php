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

namespace ILIAS\MetaData\Services\CopyrightHelper;

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Legacy\Legacy;

class NullCopyright implements CopyrightInterface
{
    public function isDefault(): bool
    {
        return false;
    }

    public function isOutdated(): bool
    {
        return false;
    }

    public function identifier(): string
    {
        return '';
    }

    public function title(): string
    {
        return '';
    }

    public function description(): string
    {
        return '';
    }

    /**
     * The copyright as UI Components, as it should be presented in the
     * UI almost everywhere.
     * If only a string can be returned, it is returned in a legacy UI component.
     * @return Image[]|Link[]|Legacy[]
     */
    public function presentAsUIComponents(): array
    {
        return [];
    }

    /**
     * The copyright without image in a reduced presentation, for displaying
     * copyright where no UI components can be used (e.g. exports of tables).
     */
    public function presentAsString(): string
    {
        return '';
    }
}
