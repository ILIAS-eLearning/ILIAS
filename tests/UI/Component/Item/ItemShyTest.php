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

require_once(__DIR__ . '/../../../../libs/composer/vendor/autoload.php');
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

    public function test_implements_factory_interface(): void
    {
        $f = $this->getFactory();

        $shy = $f->shy('shy');

        $this->assertInstanceOf('ILIAS\\UI\\Component\\Item\\Shy', $shy);
    }

    public function test_with_description(): void
    {
        $f = $this->getFactory();
        $c = $f->shy('shy')->withDescription('This is a shy');
        $this->assertEquals('This is a shy', $c->getDescription());
    }

    public function test_with_property(): void
    {
        $f = $this->getFactory();
        $c = $f->shy('shy')->withProperties(['name' => 'value']);
        $this->assertEquals(['name' => 'value'], $c->getProperties());
    }

    public function test_with_close(): void
    {
        $f = $this->getFactory();
        $c = $f->shy('shy')->withClose((new I\Component\Button\Factory())->close());
        $this->assertInstanceOf(I\Component\Button\Close::class, $c->getClose());
    }

    public function test_with_lead_icon(): void
    {
        $f = $this->getFactory();
        $c = $f->shy('shy')->withLeadIcon(
            (new I\Component\Symbol\Icon\Factory())->standard('name', 'label')
        );
        $this->assertInstanceOf(I\Component\Symbol\Icon\Icon::class, $c->getLeadIcon());
    }

    public function test_render_base(): void
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

    public function test_render_critical(): void
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

    public function test_render_with_description(): void
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

    public function test_render_with_property(): void
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


    public function test_render_with_lead_icon(): void
    {
        $c = $this->getFactory()->shy('shy')->withLeadIcon(
            new I\Component\Symbol\Icon\Standard('name', 'aria_label', 'small', false)
        );

        $expected = <<<EOT
<div class="il-item il-item-shy">
    <img class="icon name small" src="./templates/default/images/icon_default.svg" alt="aria_label" />
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

    public function test_render_with_close(): void
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
