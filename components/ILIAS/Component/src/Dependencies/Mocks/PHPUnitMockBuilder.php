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

/** @noinspection ALL */
declare(strict_types=1);

namespace ILIAS\Component\Dependencies\Mocks;

use PHPUnit\Framework\TestCase;

/**
 * @internal This class can only be used in Bootstrap and requires PHPUnit,
 *           which is a dev-dependency. Use {@see EvalLightMockBuilder} in production.
 */
final class PHPUnitMockBuilder implements MockBuilder
{
    public function create(string $fqdn): object
    {
        $mock_builder = new \PHPUnit\Framework\MockObject\MockBuilder(
            new class ('dummy') extends TestCase {
                public function dummy(): void
                {
                }
            },
            $fqdn
        );
        return $mock_builder
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
    }
}
