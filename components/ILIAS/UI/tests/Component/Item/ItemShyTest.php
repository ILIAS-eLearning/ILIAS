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

require_once(__DIR__ . '/../../../../../../vendor/composer/vendor/autoload.php');
require_once(__DIR__ . '/../../Base.php');

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Test items shy
 */
class ItemShyTest extends ILIAS_UI_TestBase
{
    public function getFactory(): C\Item\Factory
    {
        return new I\Component\Item\Factory();
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFactory();

        $shy = $f->shy('shy');

        $this->assertInstanceOf('ILIAS\\UI\\Component\\Item\\Shy', $shy);
    }

    public function testWithDescription(): void
    {
        $f = $this->getFactory();
        $c = $f->shy('shy')->withDescription('This is a shy');
        $this->assertEquals('This is a shy', $c->getDescription());
    }

    public function testWithProperty(): void
    {
        $f = $this->getFactory();
        $c = $f->shy('shy')->withProperties(['name' => 'value']);
        $this->assertEquals(['name' => 'value'], $c->getProperties());
    }

    public function testWithClose(): void
    {
        $f = $this->getFactory();
        $c = $f->shy('shy')->withClose((new I\Component\Button\Factory())->close());
        $this->assertInstanceOf(I\Component\Button\Close::class, $c->getClose());
    }

    public function testWithLeadIcon(): void
    {
        $f = $this->getFactory();
        $c = $f->shy('shy')->withLeadIcon(
            (new I\Component\Symbol\Icon\Factory())->standard('name', 'label')
        );
        $this->assertInstanceOf(I\Component\Symbol\Icon\Icon::class, $c->getLeadIcon());
    }

    public function testRenderBase(): void
    {
        $c = $this->getFactory()->shy('shy');

        $expected = <<<EOT
<div class="il-item il-item-shy">
	<div class="content">
		<div class="il-item-title">shy</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($c))
        );
    }

    public function testRenderCritical(): void
    {
        $c = $this->getFactory()->shy('noid"><script>alert(\'CRITICAL\')</script');

        $expected = <<<EOT
<div class="il-item il-item-shy">
	<div class="content">
		<div class="il-item-title">noid"&gt;&lt;script&gt;alert('CRITICAL')&lt;/script</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($c))
        );
    }

    public function testRenderWithDescription(): void
    {
        $c = $this->getFactory()->shy('shy')->withDescription('This is a shy');

        $expected = <<<EOT
<div class="il-item il-item-shy">
	<div class="content">
		<div class="il-item-title">shy</div>
        <div class="il-item-description">This is a shy</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($c))
        );
    }

    public function testRenderWithProperty(): void
    {
        $c = $this->getFactory()->shy('shy')->withProperties(['name' => 'value']);

        $expected = <<<EOT
<div class="il-item il-item-shy">
	<div class="content">
		<div class="il-item-title">shy</div>
		<hr class="il-item-divider" />
		<div class="il-item-properties">
            <div class="il-item-property-name">name</div>
            <div class="il-item-property-value">value</div>
		</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($c))
        );
    }


    public function testRenderWithLeadIcon(): void
    {
        $c = $this->getFactory()->shy('shy')->withLeadIcon(
            new I\Component\Symbol\Icon\Standard('name', 'aria_label', 'small', false)
        );

        $expected = <<<EOT
<div class="il-item il-item-shy">
    <img class="icon name small" src="./templates/default/images/standard/icon_default.svg" alt="aria_label" />
	<div class="content">
		<div class="il-item-title">shy</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($c))
        );
    }

    public function testRenderWithClose(): void
    {
        $c = $this->getFactory()->shy('shy')->withClose(new I\Component\Button\Close());

        $expected = <<<EOT
<div class="il-item il-item-shy">
	<div class="content">
		<div class="il-item-title">shy</div>
		<button type="button" class="close" aria-label="close">
            <span aria-hidden="true">&times;</span>
        </button>
	</div>
</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($c))
        );
    }
}
