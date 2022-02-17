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
 * HTTP auth credentials
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthFrontendCredentialsHTTP extends ilAuthFrontendCredentials implements ilAuthCredentials
{
    /**
     * Init credentials from request
     */
    public function initFromRequest()
    {
        $this->setUsername($_SERVER['PHP_AUTH_USER']);
        $this->setPassword($_SERVER['PHP_AUTH_PW']);
    }
}
