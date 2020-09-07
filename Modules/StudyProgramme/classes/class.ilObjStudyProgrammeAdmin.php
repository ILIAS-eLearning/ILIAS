<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/Object/classes/class.ilObject2.php");
/**
 * Class ilObjStudyProgrammeAdmin
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @author  Stefan Hecken <stefan.hecken@concepts-and-training.de>
 *
 */
class ilObjStudyProgrammeAdmin extends ilObject2
{
    const SETTING_VISIBLE_ON_PD = "prgs_visible_on_personal_desktop";
    const SETTING_VISIBLE_ON_PD_ALLWAYS = "allways";
    const SETTING_VISIBLE_ON_PD_READ = "read";

    /**
     * Constructor
     *
     * @param    integer    reference_id or object_id
     * @param    boolean    treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * initType
     *
     * @return void
     */
    public function initType()
    {
        $this->type = "prgs";
    }
}
