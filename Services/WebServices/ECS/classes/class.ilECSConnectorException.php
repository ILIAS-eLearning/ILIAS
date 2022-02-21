<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesWebServicesECS
*/
class ilECSConnectorException extends ilException
{
    /**
     * Constructor
     *
     * @access public
     * @param string message
     * @param int errno
     *
     */
    public function __construct($a_message, $a_errno = 0)
    {
        parent::__construct($a_message, $a_errno);
    }
}
