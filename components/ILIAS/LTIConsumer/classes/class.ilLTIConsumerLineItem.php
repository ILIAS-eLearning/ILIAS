<?php

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
