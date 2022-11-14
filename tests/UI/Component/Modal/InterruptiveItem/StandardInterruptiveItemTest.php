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
use ILIAS\UI\Implementation\Component\Modal\InterruptiveItem\Standard;

class StandardInterruptiveItemTest extends ILIAS_UI_TestBase
{
    private string $id;
    private string $title;
    private I\Image\Image $image;
    private string $description;

    public function setUp(): void
    {
        $this->id = 'id';
        $this->title = 'title';
        $this->image = new I\Image\Image(C\Image\Image::STANDARD, 'path', 'alt');
        $this->description = 'description';
    }

    protected function getItem(): Standard
    {
        return new Standard(
            $this->id,
            $this->title,
            $this->image,
            $this->description
        );
    }

    protected function getItemWithoutDescription(): Standard
    {
        return new Standard(
            $this->id,
            $this->title,
            $this->image
        );
    }

    protected function getItemWithoutIcon(): Standard
    {
        return new Standard(
            $this->id,
            $this->title,
            null,
            $this->description
        );
    }

    public function testGetTitle(): void
    {
        $item = $this->getItem();
        $this->assertEquals($this->title, $item->getTitle());
    }

    public function testGetIcon(): void
    {
        $item = $this->getItem();
        $this->assertEquals($this->image, $item->getIcon());
        $item = $this->getItemWithoutIcon();
        $this->assertNull($item->getIcon());
    }

    public function testGetDescription(): void
    {
        $item = $this->getItem();
        $this->assertEquals($this->description, $item->getDescription());
        $item = $this->getItemWithoutDescription();
        $this->assertEquals('', $item->getDescription());
    }

    public function testRender(): void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($this->getItem());

        $expected = <<<EOT
<tr class="il-interruptive-item">
	<td>
		<img src="path" class="img-standard" alt="alt" />
	</td>
	<td>
		title <br>
		description
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

    public function testRenderWithoutDescription(): void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($this->getItemWithoutDescription());

        $expected = <<<EOT
<tr class="il-interruptive-item">
	<td>
		<img src="path" class="img-standard" alt="alt" />
	</td>
	<td>
		title
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

    public function testRenderWithoutIcon(): void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($this->getItemWithoutIcon());

        $expected = <<<EOT
<tr class="il-interruptive-item">
	<td></td>
	<td>
		title <br>
		description
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
