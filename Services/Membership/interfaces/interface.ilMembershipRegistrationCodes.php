<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Interface for all objects that offer registration with access codes
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesMembership
*/
interface ilMembershipRegistrationCodes
{
    /**
     * Lookup all objects with reg_access_code enabled and
     * @param object $a_code
     * @return
     */
    public static function lookupObjectsByCode($a_code);
    
    
    /**
     * Register an user.
     *
     * @todo Throw exeption if registration is impossible due to other restrictions.
     *
     * @param object $a_user_id
     * @return
     */
    public function register($a_user_id);
}
