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

namespace ILIAS\LegalDocuments\test;

use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Config;
use ILIAS\LegalDocuments\Provide;

require_once __DIR__ . '/ContainerMock.php';

class ConfigTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Config::class, new Config($this->mock(Provide::class)));
    }

    public function testEditable(): void
    {
        $this->assertFalse((new Config($this->mock(Provide::class)))->editable());
        $this->assertTrue((new Config($this->mock(Provide::class), true))->editable());
    }

    public function testAllowEditing(): void
    {
        $this->assertTrue((new Config($this->mock(Provide::class)))->allowEditing()->editable());
    }

    public function testEditableLegalDocuments(): void
    {
        $readonly = $this->mock(Provide::class);
        $provide = $this->mockMethod(Provide::class, 'allowEditing', [], $readonly);
        $this->assertSame($readonly, (new Config($provide))->allowEditing()->legalDocuments());
    }

    public function testNonEditableLegalDocuments(): void
    {
        $provide = $this->mockMethod(Provide::class, 'allowEditing', [], $this->mock(Provide::class), self::never());
        $this->assertSame($provide, (new Config($provide))->legalDocuments());
    }
}
