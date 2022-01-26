<?php declare(strict_types=1);

use ILIAS\Services\Logging\LoggingServices;
use ILIAS\Services\Logging\LoggingServicesInterface;

class ilSystemStyleLoggerMock extends LoggingServices implements LoggingServicesInterface
{
    public function __construct(\ILIAS\DI\Container $DIC)
    {
        $this->container = $DIC;
    }

    public function root() : ilLoggerInterface
    {
        return new ilSystemStyleRootLoggerMock();
    }
}
