<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
 * Class ilObjExerciseAdministration
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ModulesExercise
 */
class ilObjExerciseAdministration extends ilObject
{
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "excs";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function delete()
    {
        // DISABLED
        return false;
    }
}
