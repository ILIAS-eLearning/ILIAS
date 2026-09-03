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

namespace ILIAS;

use ILIAS\Component\Component;
use ILIAS\Environment\Configuration\Installation\IliasIni;
use ILIAS\Environment\Configuration\Installation\IliasIniFile;
use ILIAS\Environment\Configuration\Installation\ClientIdProvider;
use ILIAS\Environment\Configuration\Installation\DefaultClientIdProvider;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Environment\Configuration\Installation\ClientIni;
use ILIAS\Environment\Configuration\Installation\ClientIniFile;
use ILIAS\Environment\Configuration\Server\ServerConfiguration;
use ILIAS\Environment\Configuration\Server\PhpServerConfiguration;
use ILIAS\Environment\Configuration\Installation\Directories;
use ILIAS\Environment\Configuration\Installation\WorkingDirectories;

class Environment implements Component
{
    public function init(
        array|\ArrayAccess &$define,
        array|\ArrayAccess &$implement,
        array|\ArrayAccess &$use,
        array|\ArrayAccess &$contribute,
        array|\ArrayAccess &$seek,
        array|\ArrayAccess &$provide,
        array|\ArrayAccess &$pull,
        array|\ArrayAccess &$internal,
    ): void {
        $define[] = IliasIni::class;
        $define[] = ClientIdProvider::class;
        $define[] = ClientIni::class;
        $define[] = ServerConfiguration::class;
        $define[] = Directories::class;

        $implement[IliasIni::class] = static fn() => new IliasIniFile(
            __DIR__ . '/../../../ilias.ini.php'
        );

        $implement[ClientIdProvider::class] = static fn() => new DefaultClientIdProvider(
            $use[IliasIni::class],
            $use[GlobalHttpState::class],
        );

        $implement[ClientIni::class] = static fn() => new ClientIniFile(
            $use[IliasIni::class]->getAbsolutePath()
            . '/' . $use[IliasIni::class]->getClientsPath()
            . '/' . $use[ClientIdProvider::class]->getClientId()->toString()
            . '/' . $use[IliasIni::class]->getClientIniFile()
        );

        $implement[ServerConfiguration::class] = static fn() => new PhpServerConfiguration();

        $implement[Directories::class] = static fn() => new WorkingDirectories(
            $use[IliasIni::class],
            $use[ClientIdProvider::class],
        );
    }
}
