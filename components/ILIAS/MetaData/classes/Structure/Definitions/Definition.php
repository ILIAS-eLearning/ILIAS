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

namespace ILIAS\MetaData\Structure\Definitions;

use ILIAS\MetaData\Elements\Data\Type;

class Definition implements DefinitionInterface
{
    protected string $name;
    protected bool $unique;
    protected Type $data_type;

    public function __construct(
        string $name,
        bool $unique,
        Type $data_type
    ) {
        $this->name = $name;
        $this->unique = $unique;
        $this->data_type = $data_type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function unique(): bool
    {
        return $this->unique;
    }

    public function dataType(): Type
    {
        return $this->data_type;
    }
}
