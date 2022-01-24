<?php

declare(strict_types=1);

class ilSystemStyleDICMock extends ILIAS\DI\Container
{
    public function language() : ilSystemStyleLanguageMock
    {
        return new ilSystemStyleLanguageMock();
    }

    public function logger() : ilSystemStyleLoggerMock
    {
        return new ilSystemStyleLoggerMock();
    }
}
