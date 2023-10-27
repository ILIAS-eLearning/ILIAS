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

namespace ILIAS\LegalDocuments\test\ConsumerToolbox;

use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use stdClass;

class SettingTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Setting::class, new Setting($this->fail(...), $this->fail(...)));
    }

    public function testValue(): void
    {
        $value = new stdClass();
        $instance = new Setting(fn() => $value, $this->fail(...));
        $this->assertSame($value, $instance->value());
    }

    public function testUpdate(): void
    {
        $value = new stdClass();

        $instance = new Setting($this->fail(...), function ($x) use (&$called, $value): void {
            $called = true;
            $this->assertSame($value, $x);
        });

        $instance->update($value);
        $this->assertTrue($called);
    }
}
