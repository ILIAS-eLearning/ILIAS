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

use ILIAS\Refinery\Transformation;
use ILIAS\LegalDocuments\ConsumerToolbox\Convert;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;
use ILIAS\LegalDocuments\ConsumerToolbox\KeyValueStore;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\ConsumerToolbox\SelectSetting;
use stdClass;

require_once __DIR__ . '/../ContainerMock.php';

class SelectSettingTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(SelectSetting::class, new SelectSetting($this->mock(KeyValueStore::class), $this->mock(Marshal::class)));
    }

    public function testTyped(): void
    {
        $read_value = 'a value';
        $write_value = 'some value';
        $set_value = new stdClass();
        $value = new stdClass();

        $marshal = $this->mock(Marshal::class);
        $convert = $this->mockTree(Convert::class, [
            'fromString' => $this->mockMethod(Transformation::class, 'transform', [$read_value], $value),
            'toString' => $this->mockMethod(Transformation::class, 'transform', [$set_value], $write_value),
        ]);

        $store = $this->mock(KeyValueStore::class);
        $store->expects(self::once())->method('value')->with('foo')->willReturn($read_value);
        $store->expects(self::once())->method('update')->with('foo', $write_value);

        $instance = new SelectSetting($store, $marshal);
        $setting = $instance->typed('foo', function (Marshal $m) use ($marshal, $convert): Convert {
            $this->assertSame($marshal, $m);
            return $convert;
        });

        $this->assertSame($value, $setting->value());
        $setting->update($set_value);
    }
}
