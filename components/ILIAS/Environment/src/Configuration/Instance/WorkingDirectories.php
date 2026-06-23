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

namespace ILIAS\Environment\Configuration\Instance;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class WorkingDirectories implements Directories
{
    public function __construct(
        private IliasIni $ilias_ini,
        private ClientIdProvider $client_id_provider,
    ) {
    }

    public function getRoot(): string
    {
        $root = realpath(__DIR__ . '/../../../../../../');
        if ($root === false) {
            throw new \RuntimeException(
                'Could not resolve the ILIAS root directory relative to ' . __DIR__ . '.'
            );
        }
        return $root;
    }

    public function getPublic(): string
    {
        return $this->getRoot() . '/public';
    }

    public function getDataDir(): string
    {
        return $this->ilias_ini->getDataDirectory();
    }

    public function getWebDir(): string
    {
        return $this->getPublic() . '/data/' . $this->client_id_provider->getClientId()->toString();
    }
}
