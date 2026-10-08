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

namespace ILIAS\Forum\Posting;

use LogicException;

final readonly class BindingDecision
{
    private function __construct(
        private bool $granted,
        private ?BindingDenial $reason
    ) {
    }

    public static function granted(): self
    {
        return new self(true, null);
    }

    public static function denied(BindingDenial $reason): self
    {
        return new self(false, $reason);
    }

    public function isGranted(): bool
    {
        return $this->granted;
    }

    public function denialReason(): BindingDenial
    {
        if ($this->reason === null) {
            throw new LogicException('A granted decision does not carry a denial reason');
        }

        return $this->reason;
    }
}
