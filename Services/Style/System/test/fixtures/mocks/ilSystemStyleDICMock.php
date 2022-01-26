<?php

declare(strict_types=1);

use ILIAS\Services\Logging\LoggingServicesInterface;

class ilSystemStyleDICMock extends ILIAS\DI\Container
{
    public function logger() : LoggingServicesInterface
    {
        return new ilSystemStyleLoggerMock($this);
    }
}
