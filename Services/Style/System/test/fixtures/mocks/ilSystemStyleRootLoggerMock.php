<?php

declare(strict_types=1);

class ilSystemStyleRootLoggerMock extends ilLogger
{
    public function __construct()
    {
    }

    public function debug(string $a_message, array $a_context = []): void
    {
    }
}
