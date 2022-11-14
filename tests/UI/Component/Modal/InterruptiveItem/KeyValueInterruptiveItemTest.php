<?php

declare(strict_types=1);

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

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "../../../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Modal\InterruptiveItem\KeyValue;

class KeyValueInterruptiveItemTest extends ILIAS_UI_TestBase
{
    private string $id;
    private string $key;
    private string $value;

    public function setUp(): void
    {
        $this->id = 'id';
        $this->key = 'key';
        $this->value = 'value';
    }

    protected function getItem(): KeyValue
    {
        return new KeyValue(
            $this->id,
            $this->key,
            $this->value
        );
    }

    public function testGetKey(): void
    {
        $item = $this->getItem();
        $this->assertEquals($this->key, $item->getKey());
    }

    public function testGetValue(): void
    {
        $item = $this->getItem();
        $this->assertEquals($this->value, $item->getValue());
    }

    public function testRender(): void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($this->getItem());

        $expected = <<<EOT
<tr class="il-interruptive-item">
	<td></td>
	<td>
		key
		<span class="item-value">value</span>
	</td>
	<td>
		<input type="hidden" name="interruptive_items[]" value="id">
	</td>
</tr>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
