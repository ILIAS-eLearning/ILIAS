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

/**
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilRpcClientFactory
{
    /**
     * @param string $package Package name
     * @param int    $timeout The maximum number of seconds to allow ilRpcClient to connect.
     */
    public static function factory(string $package, int $timeout = 0): ilRpcClient
    {
        return new ilRpcClient(
            ilRPCServerSettings::getInstance()->getServerUrl(),
            $package . '.',
            $timeout
        );
    }
}
