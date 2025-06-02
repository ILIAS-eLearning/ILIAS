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

namespace ILIAS\Registration;

class CodeFilter
{
    public function __construct(
        private string $code = '',
        private int $role = 0,
        private string $generated = '',
        private string $access_limitation = '',
    ) {
    }

    /**
     * @param array{code?: string, role?: int, generated?: string, access_limitation?: string} $filter
     */
    public function withData(array $filter): CodeFilter
    {
        $clone = clone $this;

        $clone->code = (string) ($filter['code'] ?? '');
        $clone->role = (int) ($filter['role'] ?? 0);
        $clone->generated = (string) ($filter['generated'] ?? '');
        $clone->access_limitation = (string) ($filter['access_limitation'] ?? '');

        return $clone;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getRole(): int
    {
        return $this->role;
    }

    public function getGenerated(): string
    {
        return $this->generated;
    }

    public function getAccessLimitation(): string
    {
        return $this->access_limitation;
    }

    /**
     * @return array{code: string, role: int, generated: string, access_limitation: string}
     */
    public function getData(): array
    {
        return [
            'code' => $this->getCode(),
            'role' => $this->getRole(),
            'generated' => $this->getGenerated(),
            'access_limitation' => $this->getAccessLimitation(),
        ];
    }
}
