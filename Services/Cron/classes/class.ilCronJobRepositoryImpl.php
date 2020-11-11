<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCronJobRepositoryImpl
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilCronJobRepositoryImpl implements ilCronJobRepository
{
    /**
     * @return ilCronJobCollection
     */
    public function findAll() : ilCronJobCollection
    {
        $collection = new ilCronJobEntities();

        foreach (ilCronManager::getCronJobData() as $item) {
            $job = ilCronManager::getJobInstance(
                $item['job_id'],
                $item['component'],
                $item['class'],
                $item['path']
            );
            if ($job) {
                $collection->add(new ilCronJobEntity($job, $item));
            }
        }

        foreach (ilCronManager::getPluginJobs() as $item) {
            $collection->add(new ilCronJobEntity($item[0], $item[1], true));
        }

        return $collection;
    }
}
