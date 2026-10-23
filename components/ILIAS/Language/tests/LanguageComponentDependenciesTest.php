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

use ILIAS\AccessControl\PublicInterface\RBAC;
use ILIAS\Component\Dependencies\InType;
use ILIAS\Component\Dependencies\Mocks\EvalLightMockBuilder;
use ILIAS\Component\Dependencies\Reader;
use ILIAS\Database\Connection;
use ILIAS\Environment\Configuration\Installation\ClientIdProvider;
use ILIAS\Environment\Configuration\Installation\Directories;
use PHPUnit\Framework\TestCase;

class LanguageComponentDependenciesTest extends TestCase
{
    /** @return list<string> */
    private function names(InType $type): array
    {
        $result = (new Reader(new EvalLightMockBuilder()))->read(new ILIAS\Language());
        $names = [];
        foreach ($result->getInDependenciesOf($type) as $dependency) {
            $names[] = $dependency->getName();
        }

        return $names;
    }

    public function testDatabaseConnectionIsPulledNotUsed(): void
    {
        self::assertContains(Connection::class, $this->names(InType::PULL));
        self::assertNotContains(Connection::class, $this->names(InType::USE));
    }

    public function testRbacIsUsed(): void
    {
        self::assertContains(RBAC::class, $this->names(InType::USE));
    }

    /** Read while the container closures are evaluated, else the bootstrap does not wire them for Language. */
    public function testTheDirectoriesAndTheClientIdProviderAreUsed(): void
    {
        $used = $this->names(InType::USE);

        self::assertContains(Directories::class, $used);
        self::assertContains(ClientIdProvider::class, $used);
    }
}
