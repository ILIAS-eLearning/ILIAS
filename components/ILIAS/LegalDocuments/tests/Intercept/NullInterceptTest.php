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

namespace ILIAS\LegalDocuments\test\Intercept;

use ILIAS\LegalDocuments\Intercept\NullIntercept;
use PHPUnit\Framework\TestCase;
use Exception;

class NullInterceptTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(NullIntercept::class, new NullIntercept());
    }

    public function testIntercept(): void
    {
        $this->assertFalse((new NullIntercept())->intercept());
    }

    public function testId(): void
    {
        $this->expectException(Exception::class);
        (new NullIntercept())->id();
    }

    public function testTarget(): void
    {
        $this->expectException(Exception::class);
        (new NullIntercept())->target();
    }
}
