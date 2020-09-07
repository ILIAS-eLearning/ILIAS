<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');

/**
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesBadge
*/
class ilFSStorageBadge extends ilFileSystemStorage
{
    public function __construct($a_container_id = 0)
    {
        parent::__construct(self::STORAGE_SECURED, true, $a_container_id);
    }
    
    protected function getPathPostfix()
    {
        return 'badge';
    }
    
    protected function getPathPrefix()
    {
        return 'ilBadge';
    }
}
