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

namespace ILIAS\MetaData\Services\Paths;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Paths\NullFactory;
use ILIAS\MetaData\Paths\BuilderInterface as InternalBuilderInterface;
use ILIAS\MetaData\Paths\NullBuilder as NullInternalBuilder;

class PathsTest extends TestCase
{
    protected function getPaths(): Paths
    {
        return new Paths(new NullFactory());
    }

    public function testCustom(): void
    {
        $paths = $this->getPaths();

        $this->assertInstanceOf(
            BuilderInterface::class,
            $paths->custom()
        );
    }
}
