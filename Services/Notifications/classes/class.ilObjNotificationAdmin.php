<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesNotifications Services/Notifications
 */

/**
* Class ilObjNotificationAdmin
*
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
*
* @ingroup ModulesNotification
*/
class ilObjNotificationAdmin extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = 'nota';
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
    * create object
    *
    * @param bool upload mode (if enabled no entries in file_data will be done)
    */
    public function create()
    {
        return parent::create();
    }

    /**
    * @access	public
    */
    public function delete()
    {
    }
} // END class.ilObjFile
