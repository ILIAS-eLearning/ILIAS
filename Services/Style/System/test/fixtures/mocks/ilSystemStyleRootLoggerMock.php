<?php declare(strict_types=1);

class ilSystemStyleRootLoggerMock extends ilLogger
{
    public function __construct()
    {
    }

    public function debug($a_message, $a_context = []) : void
    {
    }
}
