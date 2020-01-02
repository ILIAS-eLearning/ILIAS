<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMobMultiSrtInt
 */
interface ilMobMultiSrtInt
{
    /**
     * @return string upload directory
     */
    public function getUploadDir();

    /**
     * @return array array of target media objects ids
     */
    public function getMobIds();
}
