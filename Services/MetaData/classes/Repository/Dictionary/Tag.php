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
    protected string $create;
    protected string $read;
    protected string $update;
    protected string $delete;

    protected string $table;

    protected bool $is_parent;

    /**
     * @var ExpectedParameter[]
     */
    protected array $expected_parameters;

    public function __construct(
        string $create,
        string $read,
        string $update,
        string $delete,
        bool $is_parent,
        string $table,
        ExpectedParameter ...$expected_parameters
    ) {
        $this->create = $create;
        $this->read = $read;
        $this->update = $update;
        $this->delete = $delete;
        $this->is_parent = $is_parent;
        $this->table = $table;
        $this->expected_parameters = $expected_parameters;
    }

    public function create(): string
    {
        return $this->create;
    }

    public function read(): string
    {
        return $this->read;
    }

    public function update(): string
    {
        return $this->update;
    }

    public function delete(): string
    {
        return $this->delete;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function isParent(): bool
    {
        return $this->is_parent;
    }

    /**
     * @return ExpectedParameter[]
     */
    public function expectedParameters(): \Generator
    {
        yield from $this->expected_parameters;
    }
}
