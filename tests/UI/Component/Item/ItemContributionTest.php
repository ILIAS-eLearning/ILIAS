<?php declare(strict_types=1);
/* Copyright (c) 2021 Extended GPL, see docs/LICENSE */

require_once(__DIR__ . '/../../../../libs/composer/vendor/autoload.php');
require_once(__DIR__ . '/../../Base.php');

use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\Data\Factory;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Test items contributions
 */
class ItemContributionTest extends ILIAS_UI_TestBase
{
    public function getFactory() : C\Item\Factory
    {
        return new I\Component\Item\Factory;
    }

    public function test_implements_factory_interface() : void
    {
        $f = $this->getFactory();

        $contribution = $f->contribution('contribution');

        $this->assertInstanceOf('ILIAS\\UI\\Component\\Item\\Contribution', $contribution);
    }

    public function test_with_description() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution('contribution');
        $this->assertEquals('contribution', $c->getDescription());
    }

    public function test_with_contributor() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution(
            'contribution',
            'contributor'
        );
        $this->assertEquals('contributor', $c->getContributor());
    }

    public function test_with_create_datetime() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution(
            'contribution',
            null,
            new DateTimeImmutable()
        );
        $this->assertInstanceOf(DateTimeImmutable::class, $c->getCreateDatetime());
    }

    public function test_with_date_format() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution('contribution')->withDateFormat((new Factory())->dateFormat()->standard());
        $this->assertInstanceOf(DateFormat::class, $c->getDateFormat());
    }

    public function test_with_close() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution('contribution')->withClose((new I\Component\Button\Factory())->close());
        $this->assertInstanceOf(I\Component\Button\Close::class, $c->getClose());
    }

    public function test_with_lead_icon() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution('contribution')->withLeadIcon(
            (new I\Component\Symbol\Icon\Factory())->standard('name', 'label')
        );
        $this->assertInstanceOf(I\Component\Symbol\Icon\Icon::class, $c->getLeadIcon());
    }

    public function test_with_identifier() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution('contribution')->withIdentifier('testid');
        $this->assertEquals('testid', $c->getIdentifier());
    }

    public function test_render_base() : void
    {
        $c = $this->getFactory()->contribution(
            'Test quote',
            'Contributor',
            new DateTimeImmutable('2000-01-01')
        );

        $expected = <<<EOT
<div class="il-item il-contribution-item">
	<div class="contribution" title="">
		<div class="contributor">Contributor</div>
		<div class="description">Test quote</div>
		<div class="datetime">Time: 2000-01-01</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($c))
        );
    }

    public function test_render_unknown() : void
    {
        $c = $this->getFactory()->contribution('Test quote');

        $expected = <<<EOT
<div class="il-item il-contribution-item">
	<div class="contribution" title="">
		<div class="contributor">unknown</div>
		<div class="description">Test quote</div>
		<div class="datetime">Time: unknown</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($c))
        );
    }

    public function test_render_critical() : void
    {
        $c = $this->getFactory()->contribution(
            'noid"><script>alert(\'CRITICAL\')</script',
            'noid"><script>alert(\'CRITICAL\')</script',
            new DateTimeImmutable('2000-01-01')
        );

        $expected = <<<EOT
<div class="il-item il-contribution-item">
	<div class="contribution" title="">
		<div class="contributor">noid"&gt;alert('CRITICAL')</div>
		<div class="description">noid"&gt;alert('CRITICAL')</div>
		<div class="datetime">Time: 2000-01-01</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($c))
        );
    }

    public function test_render_with_identifier() : void
    {
        $c = $this->getFactory()->contribution(
            'Test quote',
            'Contributor',
            new DateTimeImmutable('2000-01-01')
        )->withIdentifier('ISBN-1234');

        $expected = <<<EOT
<div class="il-item il-contribution-item" data-id="ISBN-1234">
	<div class="contribution" title="">
		<div class="contributor">Contributor</div>
		<div class="description">Test quote</div>
		<div class="datetime">Time: 2000-01-01</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($c))
        );
    }
    
    public function test_render_with_critical_identifier() : void
    {
        $c = $this->getFactory()->contribution(
            'Test quote',
            'Contributor',
            new DateTimeImmutable('2000-01-01')
        )->withIdentifier('noid"><script>alert(\'CRITICAL\')</script');

        $expected = <<<EOT
<div class="il-item il-contribution-item" data-id="noid">alert('CRITICAL')">
	<div class="contribution" title="">
		<div class="contributor">Contributor</div>
		<div class="description">Test quote</div>
		<div class="datetime">Time: 2000-01-01</div>
	</div>
</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($c))
        );
    }

    public function test_render_with_lead_icon() : void
    {
        $c = $this->getFactory()->contribution(
            'Test quote',
            'Contributor',
            new DateTimeImmutable('2000-01-01')
        )->withLeadIcon(new I\Component\Symbol\Icon\Standard('name', 'aria_label', 'small', false));

        $expected = <<<EOT
<div class="il-item il-contribution-item">
    <img class="icon name small" src="./templates/default/images/icon_default.svg" alt="aria_label" />
	<div class="contribution" title="">
		<div class="contributor">Contributor</div>
		<div class="description">Test quote</div>
		<div class="datetime">Time: 2000-01-01</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->getDefaultRenderer()->render($c))
        );
    }

    public function test_render_with_close() : void
    {
        $c = $this->getFactory()->contribution(
            'Test quote',
            'Contributor',
            new DateTimeImmutable('2000-01-01')
        )->withClose(new I\Component\Button\Close());

        $expected = <<<EOT
<div class="il-item il-contribution-item">
	<div class="contribution" title="">
		<div class="contributor">Contributor</div>
		<div class="description">Test quote</div>
		<div class="datetime">Time: 2000-01-01</div>
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
