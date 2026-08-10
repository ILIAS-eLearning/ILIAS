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

final readonly class ilLTIConsumerLineItem
{
    public function __construct(
        public int $id,
        public int $contextId,
        public string $clientId,
        public string $label,
        public float $scoreMaximum,
        public string $resourceId,
        public string $resourceLinkId,
        public string $tag
    ) {
    }

    public function withValues(
        string $label,
        float $scoreMaximum,
        string $resourceId,
        string $resourceLinkId,
        string $tag
    ): self {
        return new self(
            $this->id,
            $this->contextId,
            $this->clientId,
            $label,
            $scoreMaximum,
            $resourceId,
            $resourceLinkId,
            $tag
        );
    }
}
