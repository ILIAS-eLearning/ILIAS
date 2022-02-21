<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Dummy access handler
 *
 * This can be used in contexts where no (proper) access handling is possible
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilDummyAccessHandler
{
    /**
     * check access for an object
     */
    public function checkAccess(string $permission, string $cmd, int $node_id, string $type = "") : bool
    {
        return true;
    }
}
