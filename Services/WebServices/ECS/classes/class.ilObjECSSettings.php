<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilObjECSSettings extends ilObject
{
    /**
    * Constructor
    * @param	$a_id int reference_id or object_id
    * @param	$a_call_by_reference bool treat the id as reference_id (true) or object_id (false)
    */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "ecss";
        parent::__construct($a_id, $a_call_by_reference);
    }
}
