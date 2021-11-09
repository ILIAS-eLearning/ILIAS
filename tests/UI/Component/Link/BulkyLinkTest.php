<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component\Link as C;
use \ILIAS\UI\Implementation\Component as I;

/**
 * Testing behavior of the Bulky Link.
 */
class BulkyLinkTest extends ILIAS_UI_TestBase
{
    /**
     * @var I\Link\Factory
     */
    protected $factory;

    public function setUp() : void
    {
        $this->factory = new I\Link\Factory();
        $this->glyph = new I\Symbol\Glyph\Glyph("briefcase", "briefcase");
        $this->icon = new I\Symbol\Icon\Standard("someExample", "Example", "small", false);
        $this->target = new \ILIAS\Data\URI("http://www.ilias.de");
    }

    public function testImplementsInterfaces()
    {
        $link = $this->factory->bulky($this->glyph, "label", $this->target);
        $this->assertInstanceOf(C\Bulky::class, $link);
        $this->assertInstanceOf(C\Link::class, $link);
    }

    public function testWrongConstruction()
    {
        $this->expectException(\TypeError::class);
        $link = $this->factory->bulky('wrong param', "label", $this->target);
    }
    
    public function testWithAriaRole()
    {
        try {
            $b = $this->factory->bulky($this->glyph, "label", $this->target)
            ->withAriaRole(I\Button\Bulky::MENUITEM);
            $this->assertEquals("menuitem", $b->getAriaRole());
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen");
        }
    }
    
    public function testWithAriaRoleIncorrect()
    {
        try {
            $this->factory->bulky($this->glyph, "label", $this->target)
            ->withAriaRole("loremipsum");
            $this->assertFalse("This should not happen");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetLabell()
    {
        $label = 'some label for the link';
        $link = $this->factory->bulky($this->glyph, $label, $this->target);
        $this->assertEquals($label, $link->getLabel());
    }

    public function testGetGlyphSymbol()
    {
        $link = $this->factory->bulky($this->glyph, "label", $this->target);
        $this->assertEquals($this->glyph, $link->getSymbol());
        $link = $this->factory->bulky($this->icon, "label", $this->target);
        $this->assertEquals($this->icon, $link->getSymbol());
    }

    public function testGetAction()
    {
        $plain = "http://www.ilias.de";
        $with_query = $plain . "?query1=1";
        $with_multi_query = $with_query . "&query2=2";
        $with_fragment = $plain . "#fragment";
        $with_multi_query_and_fragment_uri = $with_multi_query . $with_fragment;

        $plain_uri = new \ILIAS\Data\URI($plain);
        $with_query_uri = new \ILIAS\Data\URI($with_query);
        $with_multi_query_uri = new \ILIAS\Data\URI($with_multi_query);
        $with_fragment_uri = new \ILIAS\Data\URI($with_fragment);
        $with_multi_query_and_fragment_uri = new \ILIAS\Data\URI($with_multi_query_and_fragment_uri);

        $this->assertEquals($plain, $this->factory->bulky($this->glyph, "label", $plain_uri)->getAction());
        $this->assertEquals($with_query, $this->factory->bulky($this->glyph, "label", $with_query_uri)->getAction());
        $this->assertEquals($with_multi_query, $this->factory->bulky($this->glyph, "label", $with_multi_query_uri)->getAction());
        $this->assertEquals($with_fragment_uri, $this->factory->bulky($this->glyph, "label", $with_fragment_uri)->getAction());
        $this->assertEquals($with_multi_query_and_fragment_uri, $this->factory->bulky($this->glyph, "label", $with_multi_query_and_fragment_uri)->getAction());
    }

    public function testRenderingGlyph()
    {
        $r = $this->getDefaultRenderer();
        $b = $this->factory->bulky($this->glyph, "label", $this->target);

        $expected = ''
            . '<a class="il-link link-bulky" href="http://www.ilias.de">'
            . '	<span class="glyph" aria-label="briefcase" role="img">'
            . '		<span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span>'
            . '	</span>'
            . '	<span class="bulky-label">label</span>'
            . '</a>';

        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }

    public function testRenderingIcon()
    {
        $r = $this->getDefaultRenderer();
        $b = $this->factory->bulky($this->icon, "label", $this->target);

        $expected = ''
            . '<a class="il-link link-bulky" href="http://www.ilias.de">'
            . '	<img class="icon someExample small" src="./templates/default/images/icon_default.svg" alt="Example"/>'
            . '	<span class="bulky-label">label</span>'
            . '</a>';

        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }
    public function testRenderingWithId()
    {
        $r = $this->getDefaultRenderer();
        $b = $this->factory->bulky($this->icon, "label", $this->target)
            ->withAdditionalOnloadCode(function ($id) {
                return '';
            });

        $expected = ''
            . '<a class="il-link link-bulky" href="http://www.ilias.de" id="id_1">'
            . '<img class="icon someExample small" src="./templates/default/images/icon_default.svg" alt="Example"/>'
            . ' <span class="bulky-label">label</span>'
            . '</a>';

        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }
        
    public function testRenderWithAriaRoleMenuitem()
    {
        $r = $this->getDefaultRenderer();
        $b = $this->factory->bulky($this->icon, "label", $this->target)
        ->withAriaRole(I\Button\Bulky::MENUITEM);
        
        $expected = ''
        . '<a class="il-link link-bulky" href="http://www.ilias.de" role="menuitem">'
        . '<img class="icon someExample small" src="./templates/default/images/icon_default.svg" alt="Example"/>'
        . ' <span class="bulky-label">label</span>'
        . '</a>';
                        
        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }
}
