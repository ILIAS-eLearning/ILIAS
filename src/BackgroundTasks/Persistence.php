<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Created by PhpStorm.
 * User: otruffer
 * Date: 05.05.17
 * Time: 10:14
 */

namespace ILIAS\BackgroundTasks;

interface Persistence
{
    /**
     * @param Bucket $bucket The bucket you want to save.
     */
    public function saveBucketAndItsTasks(Bucket $bucket): void;

    /**
     * @return \int[] Returns an array of bucket ids for the given user Id.
     */
    public function getBucketIdsOfUser(int $user_id, string $order_by = "id", string $order_direction = "ASC"): array;

    /**
     * @return int[] Returns a list of bucket ids for the given Observer State
     */
    public function getBucketIdsByState(int $state): array;

    public function loadBucket(int $bucket_container_id): Bucket;

    /**
     * @return Bucket[]
     */
    public function loadBuckets(array $bucket_container_ids): array;

    /**
     * Deletes the Observer AND all its tasks and values.
     */
    public function deleteBucketById(int $bucket_id): void;

    /**
     * Delete the bucket and all its stuff.
     */
    public function deleteBucket(Bucket $bucket): void;

    /**
     * Updates only the bucket! Use this if e.g. the percentage or the current task changes.
     */
    public function updateBucket(Bucket $bucket): void;

    /**
     * @return int Returns the container id of an observer.
     */
    public function getBucketContainerId(Bucket $bucket): int;

    /**
     * @return BucketMeta[]
     */
    public function getBucketMetaOfUser(int $user_id): array;
}
