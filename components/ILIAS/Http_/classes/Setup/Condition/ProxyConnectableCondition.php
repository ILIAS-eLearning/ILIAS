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

declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\Setup\Condition\ExternalConditionObjective;

class ProxyConnectableCondition extends ExternalConditionObjective
{
    public function __construct($config)
    {
        parent::__construct(
            "Can establish a connection to proxy",
            function (Setup\Environment $env) use ($config): bool {
                try {
                    $host = $config->getProxyHost();
                    if (strspn($host, '.0123456789') != strlen($host) && strstr($host, '/') === false) {
                        $host = gethostbyname($host);
                    }
                    $port = $config->getProxyPort() % 65536;

                    if (!fsockopen($host, $port, $errno, $errstr, 10)) {
                        throw new Exception("Can`t establish connection to proxy.");
                    }
                } catch (\Exception $e) {
                    return false;
                }

                return true;
            },
            "Can`t establish connection to proxy."
        );
    }
}
