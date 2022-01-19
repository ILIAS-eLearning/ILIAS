<?php

declare(strict_types=1);

class ilSystemStyleLoggerMock
{
    public function __construct()
    {
    }

    public function root(): ilSystemStyleRootLoggerMock
    {
        return new ilSystemStyleRootLoggerMock();
    }
}
