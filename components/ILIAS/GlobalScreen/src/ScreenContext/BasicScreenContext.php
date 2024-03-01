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

namespace ILIAS\GlobalScreen\ScreenContext;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\ScreenContext\AdditionalData\Collection;

/**
 * @internal
 */
class BasicScreenContext implements ScreenContext
{
    protected ReferenceId $reference_id;
    protected Collection $additional_data;
    protected string $context_identifier = '';

    public function __construct(string $context_identifier)
    {
        $this->context_identifier = $context_identifier;
        $this->additional_data = new Collection();
        $this->reference_id = new ReferenceId(-1);
    }

    public function hasReferenceId(): bool
    {
        return $this->reference_id->toInt() > 0;
    }

    public function getReferenceId(): ReferenceId
    {
        return $this->reference_id;
    }

    public function withReferenceId(ReferenceId $reference_id): ScreenContext
    {
        if ($reference_id->toInt() < 1) {
            throw new \InvalidArgumentException('ReferenceId must be greater than 0');
        }

        $clone = clone $this;
        $clone->reference_id = $reference_id;

        return $clone;
    }

    public function withAdditionalData(Collection $collection): ScreenContext
    {
        $clone = clone $this;
        $clone->additional_data = $collection;

        return $clone;
    }

    public function getAdditionalData(): Collection
    {
        return $this->additional_data;
    }

    public function addAdditionalData(string $key, $value): ScreenContext
    {
        $this->additional_data->add($key, $value);

        return $this;
    }

    public function getUniqueContextIdentifier(): string
    {
        return $this->context_identifier;
    }
}
