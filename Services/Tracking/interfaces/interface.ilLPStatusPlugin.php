<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Interface for plugin classes that want to support Learning Progress
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesTracking
*/
interface ilLPStatusPluginInterface
{
    /**
     * Get all user ids with LP status completed
     *
     * @return array
     */
    public function getLPCompleted();
    
    /**
     * Get all user ids with LP status not attempted
     *
     * @return array
     */
    public function getLPNotAttempted();
    
    /**
     * Get all user ids with LP status failed
     *
     * @return array
     */
    public function getLPFailed();
    
    /**
     * Get all user ids with LP status in progress
     *
     * @return array
     */
    public function getLPInProgress();
    
    /**
     * Get current status for given user
     *
     * @param int $a_user_id
     * @return int
     */
    public function getLPStatusForUser($a_user_id);
    
    /*
    public static function isLPMember(array &$a_res, $a_usr_id,  $a_obj_ids);
    */
}
