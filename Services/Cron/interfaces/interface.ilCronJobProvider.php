<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilCronJobProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilCronJobProvider
{
    /**
     * @return ilCronJob[]
     */
    public function getCronJobInstances() : array;

    /**
     * @param string $jobId
     * @return ilCronJob
     * @throws OutOfBoundsException if the passed argument does not match any cron job
     */
    public function getCronJobInstance(string $jobId) : ilCronJob;
}
