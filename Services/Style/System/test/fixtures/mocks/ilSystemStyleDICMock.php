<?php

declare(strict_types=1);

class ilSystemStyleDICMock extends ILIAS\DI\Container
{
    public function logger(): ilSystemStyleLoggerMock
    {
        return new ilSystemStyleLoggerMock($this);
    }
}
