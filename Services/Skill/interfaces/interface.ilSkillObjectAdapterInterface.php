<?php

/**
 * Interface ilBasicSkillObjectAdapter
 */
interface ilSkillObjectAdapterInterface
{
    /**
     * Get object id for reference id
     * @param int $a_ref_id
     * @return int
     */
    public function getObjIdForRefId(int $a_ref_id) : int;

    /**
     * Get object type for object id
     * @param int $a_obj_id
     * @return null|string
     */
    public function getTypeForObjId(int $a_obj_id) : ?string;

    /**
     * Get object title for object id
     * @param int $a_obj_id
     * @return null|string
     */
    public function getTitleForObjId(int $a_obj_id) : ?string;
}