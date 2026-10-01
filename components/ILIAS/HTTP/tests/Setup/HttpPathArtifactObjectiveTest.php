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

namespace ILIAS\Tests\HTTP\Setup;

use ILIAS\HTTP\Setup\HttpPathArtifactObjective;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Environment;
use PHPUnit\Framework\TestCase;

class HttpPathArtifactObjectiveTest extends TestCase
{
    public function testHttpPathIsTakenFromTheIliasIni(): void
    {
        $this->assertSame(
            [HttpPathArtifactObjective::KEY => 'https://ilias.example.org/ilias'],
            $this->artifactFor('https://ilias.example.org/ilias/')
        );
    }

    public function testNothingIsStoredWithoutHttpPath(): void
    {
        $this->assertSame([], $this->artifactFor(''));
    }

    private function artifactFor(string $http_path): array
    {
        $ini = $this->createStub(\ilIniFile::class);
        $ini->method('readVariable')->willReturnMap([['server', 'http_path', $http_path]]);

        $artifact = (new HttpPathArtifactObjective())->build(
            new ArrayEnvironment([Environment::RESOURCE_ILIAS_INI => $ini])
        );

        return eval('?>' . $artifact->serialize());
    }
}
