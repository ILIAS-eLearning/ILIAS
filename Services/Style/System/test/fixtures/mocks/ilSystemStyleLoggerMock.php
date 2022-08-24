<?php

declare(strict_types=1);

class ilSystemStyleLoggerMock extends \ILIAS\DI\LoggingServices
{
    public function __construct(\ILIAS\DI\Container $DIC)
    {
        $this->container = $DIC;
    }

    public function root(): ilLogger
    {
        return new ilSystemStyleRootLoggerMock();
    }
}
