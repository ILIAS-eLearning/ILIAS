<?php

declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @package ModulesGroup
 */
class ilObjGroupAdministration extends ilObject
{
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "grps";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function delete(): bool
    {
        return false;
    }
}
