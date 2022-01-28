<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjStudyProgrammeAdmin
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @author  Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjStudyProgrammeAdmin extends ilObject2
{
    const SETTING_VISIBLE_ON_PD = "prgs_visible_on_personal_desktop";
    const SETTING_VISIBLE_ON_PD_ALLWAYS = "allways";
    const SETTING_VISIBLE_ON_PD_READ = "read";

    public function __construct(int $id = 0, bool $call_by_reference = true)
    {
        parent::__construct($id, $call_by_reference);
    }

    public function initType() : void
    {
        $this->type = "prgs";
    }
}
