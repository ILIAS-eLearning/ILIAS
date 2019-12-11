<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for ilObject dependency
 *
 * @author killing@leifos.de
 * @ingroup ServicesConditions
 */
interface ilConditionObjectAdapterInterface
{
    /**
     * Get object id for reference id
     *
     * @param int $a_ref_id
     * @return int
     */
    public function getObjIdForRefId($a_ref_id);

    /**
     * Get object type for object id
     *
     * @param int $a_obj_id
     * @return string
     */
    public function getTypeForObjId($a_obj_id);
}
