<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wraps ilObject dependencies
 *
 * @author killing@leifos.de
 * @ingroup ServicesNews
 */
class ilNewsObjectAdapter implements ilNewsObjectAdapterInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get object id for reference id
     *
     * @param int $a_ref_id
     * @return int
     */
    public function getObjIdForRefId($a_ref_id)
    {
        return ilObject::_lookupObjId($a_ref_id);
    }

    /**
     * Get object type for object id
     *
     * @param int $a_obj_id
     * @return int
     */
    public function getTypeForObjId($a_obj_id)
    {
        return ilObject::_lookupType($a_obj_id);
    }
}
