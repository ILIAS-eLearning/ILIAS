<?php

namespace ILIAS\Services\Membership\ChangelogEvents;

/**
 * Class UnsubscribedFromCourse
 *
 * @package ILIAS\Changelog\Events\Membership
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class UnsubscribedFromCourse extends MembershipEvent
{

    const NAME = 'unsubscribed_from_course';
    /**
     * @var int
     */
    protected $crs_obj_id;
    /**
     * @var int
     */
    protected $actor_user_id;


    /**
     * UnsubscribedFromCourse constructor.
     *
     * @param int $actor_user_id
     *
     * @param int $crs_obj_id unsubscribing user
     */
    public function __construct(int $actor_user_id, int $crs_obj_id)
    {
        $this->crs_obj_id = $crs_obj_id;
        $this->actor_user_id = $actor_user_id;
    }


    /**
     * @return String
     */
    public function getName() : String
    {
        return self::NAME;
    }


    /**
     * @return int
     */
    public function getSubjectObjId() : int
    {
        return $this->crs_obj_id;
    }


    /**
     * @return int
     */
    public function getActorUserId() : int
    {
        return $this->actor_user_id;
    }


    /**
     *  actor and subject are the same here
     *
     * @return int
     */
    public function getSubjectUserId() : int
    {
        return $this->actor_user_id;
    }


    /**
     * May be empty
     *
     * @return array
     */
    public function getAdditionalData() : array
    {
        // TODO: Implement getAdditionalData() method.
    }
}