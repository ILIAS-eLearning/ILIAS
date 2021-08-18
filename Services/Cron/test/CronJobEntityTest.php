<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class CronJobEntityTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class CronJobEntityTest extends TestCase
{
    /**
     * @return ilCronJobEntity
     */
    private function getEntity() : ilCronJobEntity
    {
        return new ilCronJobEntity($this->createMock(ilCronJob::class), [
            'job_id' => 'phpunit',
            'component' => 'phpunit',
            'schedule_type' => 1,
            'schedule_value' => 1,
            'job_status' => 1,
            'job_status_user_id' => 6,
            'job_status_type' => 1,
            'job_status_ts' => time(),
            'job_result_status' => ilCronJobResult::STATUS_OK,
            'job_result_user_id' => 6,
            'job_result_code' => ilCronJobResult::CODE_NO_RESULT,
            'job_result_message' => 'msg',
            'job_result_type' => 1,
            'job_result_ts' => time(),
            'class' => 'Job',
            'path' => '/',
            'running_ts' => time(),
            'job_result_dur' => time(),
            'alive_ts' => time(),
        ], false);
    }

    public function testEntityCollectionCanBeCreatedWithItems() : ilCronJobEntities
    {
        $entities = new ilCronJobEntities([
            $this->getEntity()
        ]);

        $this->assertCount(1, $entities->toArray());

        return $entities;
    }

    /**
     * @param ilCronJobEntities $entities
     * @return ilCronJobEntities
     * @depends testEntityCollectionCanBeCreatedWithItems
     */
    public function testCollectionCanBeChanged(ilCronJobEntities $entities) : ilCronJobEntities
    {
        $entities->add($this->getEntity());

        $this->assertCount(2, $entities->toArray());

        return $entities;
    }

    /**
     * @param ilCronJobEntities $entities
     * @depends testCollectionCanBeChanged
     */
    public function testCollectionCanBeFilteredAndSliced(ilCronJobEntities $entities) : void
    {
        $this->assertCount(0, $entities->filter(static function (ilCronJobEntity $entity) : bool {
            return $entity->getJobId() !== 'phpunit';
        }));

        $this->assertCount(1, $entities->slice(1, 1));
    }
}
