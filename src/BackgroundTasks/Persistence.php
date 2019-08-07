<?php
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
    public function saveBucketAndItsTasks(Bucket $bucket);


    /**
     * @param int    $user_id
     * @param string $order_by
     * @param string $order_direction
     *
     * @return \int[] Returns an array of bucket ids for the given user Id.
     */
    public function getBucketIdsOfUser($user_id, $order_by = "id", $order_direction = "ASC");


    /**
     * @param $state int
     *
     * @return int[] Returns a list of bucket ids for the given Observer State
     */
    public function getBucketIdsByState($state);


    /**
     * @param int $bucket_container_id
     *
     * @return Bucket
     *
     */
    public function loadBucket($bucket_container_id);


    /**
     * @param $bucket_container_id
     *
     * @return Bucket[]
     *
     */
    public function loadBuckets($bucket_container_id);


    /**
     * Deletes the Observer AND all its tasks and values.
     *
     * @param $bucket_id int
     *
     * @return void
     */
    public function deleteBucketById($bucket_id);


    /**
     * Delete the bucket and all its stuff.
     *
     * @param $bucket Bucket
     *
     * @return void
     */
    public function deleteBucket($bucket);


    /**
     * Updates only the bucket! Use this if e.g. the percentage or the current task changes.
     *
     * @param Bucket $bucket
     */
    public function updateBucket(Bucket $bucket);


    /**
     * @param Bucket $bucket
     *
     * @return int Returns the container id of an obvserver.
     */
    public function getBucketContainerId(Bucket $bucket);


    /**
     * @param int $user_id
     *
     * @return BucketMeta[]
     */
    public function getBucketMetaOfUser($user_id);
}