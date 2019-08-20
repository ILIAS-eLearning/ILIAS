<?php

namespace ILIAS\Services\Membership\ChangelogEvents;

/**
 * Class MembershipRequestAccepted
 *
 * @package ILIAS\Changelog\Events\Membership
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipRequestAccepted extends MembershipEvent
{

    const NAME = 'request_accepted';
    /**
     * @var int
     */
    protected $crs_obj_id;
    /**
     * @var int
     */
    protected $subject_user_id;
    /**
     * @var int
     */
    protected $actor_user_id;


    /**
     * MembershipRequestAccepted constructor.
     *
     * @param int $actor_user_id   accepting user
     * @param int $subject_user_id accepted user
     * @param int $crs_obj_id
     *
     */
    public function __construct(int $actor_user_id, int $subject_user_id, int $crs_obj_id)
    {
        $this->crs_obj_id = $crs_obj_id;
        $this->subject_user_id = $subject_user_id;
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
    public function getSubjectUserId() : int
    {
        return $this->subject_user_id;
    }


    /**
     * @return int
     */
    public function getActorUserId() : int
    {
        return $this->actor_user_id;
    }


    /**
     * @return int
     */
    public function getSubjectObjId() : int
    {
        return $this->crs_obj_id;
    }


    /**
     * @return array
     */
    public function getAdditionalData() : array
    {
        return [];
    }
}