<?php

namespace ILIAS\Changelog\Events\GlobalEvents;

/**
 * Class ChangelogActivated
 *
 * @package ILIAS\Changelog\Events\GlobalEvents
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ChangelogActivated extends GlobalEvent
{

    const NAME = 'changelog_activated';
    /**
     * @var int
     */
    protected $actor_user_id;


    /**
     * ChangelogActivated constructor.
     *
     * @param int $actor_user_id
     */
    public function __construct(int $actor_user_id)
    {
        $this->actor_user_id = $actor_user_id;
    }


    /**
     * @return int
     */
    public function getActorUserId() : int
    {
        return $this->actor_user_id;
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
        return 0;
    }


    /**
     * @return int
     */
    public function getSubjectObjId() : int
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