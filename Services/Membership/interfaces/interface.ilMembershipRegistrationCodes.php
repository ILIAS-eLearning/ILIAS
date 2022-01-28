<?php declare(strict_types=1);/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for all objects that offer registration with access codes
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesMembership
 */
interface ilMembershipRegistrationCodes
{
    /**
     * Lookup all objects with reg_access_code enabled and
     * @param string $a_code
     * @return int[]
     */
    public static function lookupObjectsByCode(string $a_code) : array;

    /**
     * Register an user.
     * @todo Throw exeption if registration is impossible due to other restrictions.
     */
    public function register(int $a_user_id) : void;
}
