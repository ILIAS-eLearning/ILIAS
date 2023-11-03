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

namespace ILIAS\LegalDocuments\Value;

class CriterionContent
{
    public function __construct(private readonly string $type, private readonly array $arguments)
    {
    }

    public function type(): string
    {
        return $this->type;
    }

    public function arguments(): array
    {
        return $this->arguments;
    }

    public function equals(CriterionContent $other): bool
    {
        return $this->type() === $other->type()
            && $this->arguments() === $other->arguments();
    }
}
