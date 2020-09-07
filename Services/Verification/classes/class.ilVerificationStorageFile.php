<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');
/**
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesVerification
*/
class ilVerificationStorageFile extends ilFileSystemStorage
{
    /**
     * Constructor
     *
     * @access public
     * @param int storage type
     * @param bool En/Disable automatic path conversion. If enabled files with id 123 will be stored in directory files/1/file_123
     * @param int object id of container (e.g file_id or mob_id)
     *
     */
    public function __construct($a_container_id = 0)
    {
        parent::__construct(self::STORAGE_DATA, true, $a_container_id);
    }
    
    /**
     * Implementation of abstract method
     *
     * @access protected
     *
     */
    protected function getPathPostfix()
    {
        return 'vrfc';
    }
    
    /**
     * Implementation of abstract method
     *
     * @access protected
     *
     */
    protected function getPathPrefix()
    {
        return 'ilVerification';
    }
}
