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

namespace ILIAS\MetaData\Search\Clauses;

use ILIAS\MetaData\Search\Clauses\Properties\JoinPropertiesInterface;
use ILIAS\MetaData\Search\Clauses\Properties\BasicPropertiesInterface;

class Clause implements ClauseInterface
{
    protected bool $negated;
    protected bool $join;
    protected ?JoinPropertiesInterface $join_properties;
    protected ?BasicPropertiesInterface $basic_properties;

    public function __construct(
        bool $negated,
        bool $join,
        ?JoinPropertiesInterface $join_properties,
        ?BasicPropertiesInterface $basic_properties
    ) {
        $this->negated = $negated;
        $this->join = $join;
        $this->join_properties = $join_properties;
        $this->basic_properties = $basic_properties;
    }

    public function isNegated(): bool
    {
        return $this->negated;
    }

    public function isJoin(): bool
    {
        return $this->join;
    }

    public function joinProperties(): ?JoinPropertiesInterface
    {
        return $this->join_properties;
    }

    public function basicProperties(): ?BasicPropertiesInterface
    {
        return $this->basic_properties;
    }
}
