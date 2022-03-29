<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjTypeDefinition
 *
 * CLASS IS DEPRECATED
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjTypeDefinition extends ilObject
{
    public function __construct(int $id = 0, bool $call_by_reference = false)
    {
        parent::__construct($id, $call_by_reference);
        $this->type = "typ";
    }
}
