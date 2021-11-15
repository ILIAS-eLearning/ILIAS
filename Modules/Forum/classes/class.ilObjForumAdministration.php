<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjForumAdministration
 * @author  Nadia Matuschek <nmatuschek@databay.de>
 * @version $Id:$
 * @extends ilObject
 * @package ilias-core
 */
class ilObjForumAdministration extends ilObject
{
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = 'frma';
        parent::__construct($a_id, $a_call_by_reference);
        $this->lng->loadLanguageModule('forum');
    }
}
