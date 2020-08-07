<?php

/**
 * Class ilBasicSkillObjectAdapter
 */
class ilSkillObjectAdapter implements ilSkillObjectAdapterInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * @inheritDoc
     */
    public function getObjIdForRefId(int $a_ref_id) : int
    {
        $trigger_obj_id = ($a_ref_id > 0)
            ? ilObject::_lookupObjId($a_ref_id)
            : 0;

        return $trigger_obj_id;
    }

    /**
     * @inheritDoc
     */
    public function getTypeForObjId(int $a_obj_id) : ?string
    {
        return ilObject::_lookupType($a_obj_id);
    }

    /**
     * @inheritDoc
     */
    public function getTitleForObjId(int $a_obj_id) : ?string
    {
        return ilObject::_lookupTitle($a_obj_id);
    }

}