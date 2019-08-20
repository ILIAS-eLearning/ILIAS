<?php

namespace ILIAS\Services\Membership\ChangelogEvents;

/**
 * Class MembershipRequested
 *
 * @package ILIAS\Changelog\Events\Membership
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipRequested extends MembershipEvent
{

    const NAME = 'membership_requested';
    /**
     * @var int
     */
    protected $crs_obj_id;
    /**
     * @var int
     */
    protected $subject_user_id;


    /**
     * MembershipRequested constructor.
     *
     * @param int $subject_user_id
     * @param int $crs_obj_id
     */
    public function __construct(int $subject_user_id, int $crs_obj_id)
    {
        $this->crs_obj_id = $crs_obj_id;
        $this->subject_user_id = $subject_user_id;
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
    public function getSubjectUserId() : int
    {
        return $this->subject_user_id;
    }


    /**
     * @return int
     */
    public function getActorUserId() : int
    {
        return 0;
    }


    /**
     * @return array
     */
    public function getAdditionalData() : array
    {
        return [];
    }
}