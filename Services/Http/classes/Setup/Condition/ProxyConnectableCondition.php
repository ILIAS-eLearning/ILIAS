<?php declare(strict_types=1);

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Setup\Condition\ExternalConditionObjective;

class ProxyConnectableCondition extends ExternalConditionObjective
{
    public function __construct($config)
    {
        return parent::__construct(
            "Can establish a connection to proxy",
            function (Setup\Environment $env) use ($config) : bool {
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
