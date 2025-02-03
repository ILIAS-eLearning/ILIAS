<?php

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

declare(strict_types=0);
/**
 * adapter class for nusoap server
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilSoapUserAdministrationAdapter
{
    public SoapServer $server;

    public function __construct()
    {
        $this->server = new SoapServer(null);
        $this->registerMethods();
    }

    public function start(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->server->handle();
        }
    }

    private function registerMethods(): void
    {
        include_once './components/ILIAS/soap/include/inc.soap_functions.php';

        $this->server->addFunction(SOAP_FUNCTIONS_ALL);
    }
}
