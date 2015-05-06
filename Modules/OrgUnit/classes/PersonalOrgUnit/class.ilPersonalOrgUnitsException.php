<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPersonalOrgUnitsException
 *
 * @author 	Richard Klees <rklees@concepts-and-training.de>
 */



require_once("Services/Exceptions/classes/class.ilException.php");

// This is not derived from ilObjOrgUnitException as the problems expressed
// by this exception are specific to conditions that need to hold in the 
// personal org units. The exception does not express a general problem in
// the org units.
class ilPersonalOrgUnitsException extends ilException {
}

?>
