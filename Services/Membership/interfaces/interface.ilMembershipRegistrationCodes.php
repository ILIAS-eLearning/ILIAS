<?php declare(strict_types=1);
    
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
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
     * @return int[]
     */
    public static function lookupObjectsByCode(string $a_code) : array;

    /**
     * Register an user.
     * @todo Throw exeption if registration is impossible due to other restrictions.
     */
    public function register(int $a_user_id) : void;
}
