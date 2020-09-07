<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilObjectCustomIcon
 */
interface ilObjectCustomIconPresenter
{
    /**
     * @return bool
     */
    public function exists() : bool;

    /**
     * @return string
     */
    public function getFullPath() : string;
}
