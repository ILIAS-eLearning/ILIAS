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

use PHPUnit\Framework\TestCase;

class ilMustacheFactoryTest extends TestCase
{
    public function testCreatInstance(): void
    {
        $f = new ilMustacheFactory();
        $this->assertInstanceOf(ilMustacheFactory::class, $f);
    }

    public function testCreateBasicEngine(): void
    {
        $f = new ilMustacheFactory();
        $engine = $f->getBasicEngine();
        $this->assertInstanceOf(Mustache_Engine::class, $engine);
    }
}
