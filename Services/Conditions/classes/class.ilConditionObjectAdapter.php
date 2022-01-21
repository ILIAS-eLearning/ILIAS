<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wraps ilObject dependencies
 * @author @leifos.de
 * @ingroup
 */
class ilConditionObjectAdapter implements ilConditionObjectAdapterInterface
{
    /**
     * @inheritdoc
     */
    public function getObjIdForRefId(int $a_ref_id) : int
    {
        return ilObject::_lookupObjId($a_ref_id);
    }

    /**
     * @inheritdoc
     */
    public function getTypeForObjId(int $a_obj_id) : string
    {
        return ilObject::_lookupType($a_obj_id);
    }
}
