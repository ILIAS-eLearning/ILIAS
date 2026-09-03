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

use ILIAS\Data\ClientId;
use ILIAS\Environment\Configuration\Installation\ClientIdProvider;
use ILIAS\Environment\Configuration\Installation\IliasIni;
use ILIAS\Environment\Configuration\Installation\WorkingDirectories;
use PHPUnit\Framework\TestCase;

final class WorkingDirectoriesTest extends TestCase
{
    private function directories(string $client = 'default', string $datadir = '/var/ilias/data'): WorkingDirectories
    {
        $ini = $this->createStub(IliasIni::class);
        $ini->method('getDataDirectory')->willReturn($datadir);

        $provider = $this->createStub(ClientIdProvider::class);
        $provider->method('getClientId')->willReturn(new ClientId($client));

        return new WorkingDirectories($ini, $provider);
    }

    public function testGetRootResolvesToAnExistingCanonicalDirectory(): void
    {
        $root = $this->directories()->getRoot();
        self::assertDirectoryExists($root);
        self::assertSame(realpath($root), $root);
    }

    public function testGetPublicIsRootSlashPublic(): void
    {
        $directories = $this->directories();
        self::assertSame($directories->getRoot() . '/public', $directories->getPublic());
    }

    public function testGetDataDirDelegatesToIliasIni(): void
    {
        self::assertSame('/var/ilias/data', $this->directories(datadir: '/var/ilias/data')->getDataDir());
    }

    public function testGetWebDirCombinesPublicDataAndClientId(): void
    {
        $directories = $this->directories(client: 'acme');
        self::assertSame($directories->getPublic() . '/data/acme', $directories->getWebDir());
    }
}
