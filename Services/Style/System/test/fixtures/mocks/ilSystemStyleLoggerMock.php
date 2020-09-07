<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilSystemStyleLoggerMock
 */
class ilSystemStyleLoggerMock
{

    /**
     * ilSystemStyleLoggerMock constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function root()
    {
        return new ilSystemStyleRootLoggerMock();
    }
}

/**
 * Class ilSystemStyleLoggerMock
 */
class ilSystemStyleRootLoggerMock
{

    /**
     * ilSystemStyleLoggerMock constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function debug($message)
    {
    }
}
