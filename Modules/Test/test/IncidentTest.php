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

namespace ILIAS\Modules\Test\test;

use PHPUnit\Framework\TestCase;
use ILIAS\Modules\Test\Incident;

class IncidentTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Incident::class, new Incident());
    }

    public function testAny(): void
    {
        $incident = new Incident();
        $this->assertTrue($incident->any(static function (int $x) {return $x === 2;}, [1, 2, 3]));
    }

    public function testAnyBreaksAtFirstTrue(): void
    {
        $incident = new Incident();
        $throw_error = false;

        $this->assertTrue($incident->any(static function (int $x) use (&$throw_error): bool {
            if ($throw_error) {
                throw new Exception('Should not be called anymore.');
            } elseif ($x === 2) {
                $throw_error = true;
                return true;
            }
            return false;
        }, [1, 2, 3]));
    }

    public function testAnyReturnsFalse(): void
    {
        $incident = new Incident();
        $this->assertFalse($incident->any(static function (int $x) {return false;}, [1, 2, 3]));
    }
}
