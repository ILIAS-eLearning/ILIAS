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

namespace ILIAS\MetaData\Search\Clauses\Properties;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Search\Clauses\Mode;

class BasicProperties implements BasicPropertiesInterface
{
    protected PathInterface $path;
    protected Mode $mode;
    protected bool $is_mode_negated;
    protected string $value;

    public function __construct(
        PathInterface $path,
        Mode $mode,
        string $value,
        bool $is_mode_negated,
    ) {
        $this->path = $path;
        $this->mode = $mode;
        $this->value = $value;
        $this->is_mode_negated = $is_mode_negated;
    }

    public function path(): PathInterface
    {
        return $this->path;
    }

    public function mode(): Mode
    {
        return $this->mode;
    }

    public function isModeNegated(): bool
    {
        return $this->is_mode_negated;
    }

    public function value(): string
    {
        return $this->value;
    }
}
