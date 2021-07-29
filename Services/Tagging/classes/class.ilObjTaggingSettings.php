<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjTaggingSettings
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjTaggingSettings extends ilObject
{
    /**
     * @inheritDoc
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "tags";
        parent::__construct($a_id, $a_call_by_reference);
    }
}
