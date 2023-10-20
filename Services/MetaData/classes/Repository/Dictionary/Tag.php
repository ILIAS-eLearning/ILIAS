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

use ILIAS\MetaData\Structure\Dictionaries\Tags\Tag as BaseTag;

class Tag extends BaseTag implements TagInterface
{
    protected bool $has_row;
    protected string $table;
    protected string $data_field;
    protected string $parent;

    public function __construct(
        string $table,
        bool $has_row,
        string $data_field = '',
        string $parent = ''
    ) {
        $this->table = $table;
        $this->has_row = $has_row;
        $this->data_field = $data_field;
        $this->parent = $parent;
    }

    public function hasRowInTable(): bool
    {
        return $this->has_row;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function hasData(): bool
    {
        return $this->data_field !== '';
    }

    public function dataField(): string
    {
        return $this->data_field;
    }

    public function hasParent(): bool
    {
        return $this->parent !== '';
    }

    public function parent(): string
    {
        return $this->parent;
    }
}
