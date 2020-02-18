<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilStrictCliCronManager
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilCronManagerInterface
{
    /**
     * Run all active jobs
     */
    public function runActiveJobs();
}
