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

namespace ILIAS\MetaData\Repository\Dictionary;

use ILIAS\MetaData\Structure\Dictionaries\Tags\NullTag as BaseNullTag;

class NullTag extends BaseNullTag implements TagInterface
{
    public function hasRowInTable(): bool
    {
        return false;
    }

    public function table(): string
    {
        return '';
    }

    public function hasData(): bool
    {
        return false;
    }

    public function dataField(): string
    {
        return '';
    }

    public function hasParent(): bool
    {
        return false;
    }

    public function parent(): string
    {
        return '';
    }
}
