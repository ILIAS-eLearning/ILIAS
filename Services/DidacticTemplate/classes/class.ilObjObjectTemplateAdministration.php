<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjObjectTemplateAdministration
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id:$
*
* @package ServicesDidacticTemplate
*/

require_once "./Services/Object/classes/class.ilObject.php";

class ilObjObjectTemplateAdministration extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "otpl";
        parent::__construct($a_id, $a_call_by_reference);

        $this->lng->loadLanguageModule("didactic");
    }
}
