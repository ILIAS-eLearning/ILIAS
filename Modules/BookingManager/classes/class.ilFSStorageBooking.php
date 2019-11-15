<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* 
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* 
* @ingroup ModulesBookingManager
*/
class ilFSStorageBooking extends ilFileSystemStorage
{
	public function __construct($a_container_id = 0)
	{
		parent::__construct(self::STORAGE_WEB, true, $a_container_id);
	}
	
	protected function getPathPostfix()
	{
	 	return 'book';
	}
	
	protected function getPathPrefix()
	{
	 	return 'ilBookingManager';
	}
}

?>