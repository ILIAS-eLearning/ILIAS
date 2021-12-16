<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wraps ilObject dependencies
 *
 * @author @leifos.de
 * @ingroup
 */
class ilConditionObjectAdapter implements ilConditionObjectAdapterInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get object id for reference id
     * @param int $a_ref_id
     * @return int
     */
    public function getObjIdForRefId(int $a_ref_id):int
    {
        return ilObject::_lookupObjId($a_ref_id);
    }

    /**
     * Get object type for object id
     * @param int $a_obj_id
     * @return string
     */
    public function getTypeForObjId(int $a_obj_id):string
    {
        return ilObject::_lookupType($a_obj_id);
    }
}
