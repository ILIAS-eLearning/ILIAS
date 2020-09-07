<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilObjectCustomIconUploadPostProcessor
 */
interface ilObjectCustomIconUploadPostProcessor
{
    /**
     * @param string $fileName
     */
    public function process(string $fileName);
}
