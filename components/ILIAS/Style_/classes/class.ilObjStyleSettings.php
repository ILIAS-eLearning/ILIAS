<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilObjStyleSettings
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjStyleSettings extends ilObject
{
    /**
     * @param	integer	$a_id reference_id or object_id
     * @param	boolean	$a_call_by_reference treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "stys";
        parent::__construct($a_id, $a_call_by_reference);
    }
}
