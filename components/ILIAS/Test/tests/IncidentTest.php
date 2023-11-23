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

namespace ILIAS\components\Test\test;

use Exception;
use PHPUnit\Framework\TestCase;
use ILIAS\components\Test\Incident;

class IncidentTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Incident::class, new Incident());
    }

    public function testAny(): void
    {
        $this->assertTrue((new Incident())->any(static fn(int $x) => $x === 2, [1, 2, 3]));
    }

    public function testAnyBreaksAtFirstTrue(): void
    {
        $throw_error = false;

        $this->assertTrue((new Incident())->any(static function (int $x) use (&$throw_error): bool {
            if ($throw_error) {
                throw new Exception('Should not be called anymore.');
            }
            return $x === 2 && ($throw_error = true);
        }, [1, 2, 3]));
    }

    public function testAnyReturnsFalse(): void
    {
        $this->assertFalse((new Incident())->any(static fn(int $x) => false, [1, 2, 3]));
    }
}
