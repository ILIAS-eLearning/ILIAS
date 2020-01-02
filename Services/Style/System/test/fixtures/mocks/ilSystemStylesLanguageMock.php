<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilSystemStylesLanguageMock
 */
class ilSystemStylesLanguageMock
{
    public $requested = array();

    /**
     * ilSystemStylesLanguageMock constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $a_topic
     * @return mixed
     */
    public function txt($a_topic)
    {
        $this->requested[] = $a_topic;
        return $a_topic;
    }
}
