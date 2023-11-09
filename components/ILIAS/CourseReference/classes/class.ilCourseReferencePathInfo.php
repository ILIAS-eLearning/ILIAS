<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCourseReferencePathInfo
 */
class ilCourseReferencePathInfo
{
    /**
     * @var null | \ilCourseReferencePathInfo[]
     */
    private static $instances = [];

    /**
     * @var null | \ilLogger
     */
    private $logger = null;

    /**
     * @var \ilTree|null
     */
    private $tree = null;

    /**
     * @var \ilAccessHandler|null
     */
    private $access = null;

    /**
     * @var int
     */
    private $ref_id = 0;

    /**
     * @var int
     */
    private $target_ref_id = 0;


    /**
     * @var bool
     */
    private $has_parent_course = false;

    /**
     * @var int
     */
    private $parent_course_ref_id = 0;


    /**
     * @var bool
     */
    private $member_update_enabled = false;


    /**
     * ilCourseReferencePathInfo constructor.
     */
    public function __construct(int $ref_id, int $target_ref_id = 0)
    {
        global $DIC;

        $this->logger = $DIC->logger()->crsr();
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();

        $this->ref_id = $ref_id;
        $this->target_ref_id = $target_ref_id;
        $this->init();

        $this->logger->info('ref_id: ' . $this->ref_id);
        $this->logger->info('target_ref_id: ' . $this->target_ref_id);
        $this->logger->info('parent course: ' . $this->parent_course_ref_id);
        $this->logger->info('has parent course: ' . $this->has_parent_course);
    }

    /**
     * @param int $ref_id
     * @param int $target_ref_id
     * @return \ilCourseReferencePathInfo
     */
    public static function getInstanceByRefId(int $ref_id, int $target_ref_id = 0): \ilCourseReferencePathInfo
    {
        if (!array_key_exists($ref_id, self::$instances)) {
            self::$instances[$ref_id] = new self($ref_id, $target_ref_id);
        }
        return self::$instances[$ref_id];
    }


    /**
     * @param int $course_ref_id
     * @param int[] $participants
     * @return bool
     */
    public static function isReferenceMemberUpdateConfirmationRequired(int $course_ref_id, array $participants): bool
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $childs = $tree->getChildsByType($course_ref_id, 'crsr');
        foreach ($childs as $tree_node) {
            $path_info = self::getInstanceByRefId($tree_node['child']);
            if (!$path_info->hasParentCourse()) {
                continue;
            }
            if (!$path_info->isMemberUpdateEnabled()) {
                continue;
            }
            if (!$path_info->checkManagmentAccess()) {
                continue;
            }
            $part = ilParticipants::getInstance($path_info->getTargetId());
            foreach ($participants as $part_id) {
                if ($part->isMember($part_id)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public function getParentCourseRefId(): int
    {
        return $this->parent_course_ref_id;
    }

    /**
     * @return bool
     */
    public function hasParentCourse(): bool
    {
        return $this->has_parent_course;
    }

    /**
     * @return int
     */
    public function getTargetId(): int
    {
        return $this->target_ref_id;
    }

    /**
     * @return bool
     */
    public function isMemberUpdateEnabled(): bool
    {
        return $this->member_update_enabled;
    }

    /**
     * Check manage member for both target and parent course
     * @return bool
     */
    public function checkManagmentAccess()
    {
        if (!$this->hasParentCourse()) {
            $this->logger->dump('Access failed: no parent course');
            return false;
        }
        return
            $this->access->checkAccess('manage_members', '', $this->target_ref_id) &&
            $this->access->checkAccess('manage_members', '', $this->parent_course_ref_id);
    }


    /**
     * Init path info
     */
    protected function init()
    {
        if (!$this->target_ref_id) {
            $target_obj_id = ilObjCourseReference::_lookupTargetId(ilObject::_lookupObjId($this->ref_id));
            $target_ref_ids = ilObject::_getAllReferences($target_obj_id);
            $this->target_ref_id = end($target_ref_ids);
        }

        $this->member_update_enabled = ilObjCourseReference::lookupMemberUpdateEnabled(ilObject::_lookupObjId($this->ref_id));
        $this->parent_course_ref_id = $this->tree->checkForParentType($this->ref_id, 'crs');

        if ($this->getParentCourseRefId() && !$this->tree->checkForParentType($this->ref_id, 'grp')) {
            $this->has_parent_course = true;
        }
    }
}
