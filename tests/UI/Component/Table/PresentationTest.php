<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation as I;
use ILIAS\UI\Implementation\Component\Table\PresentationRow;

/**
 * Tests for Presentation Table.
 */
class PresentationTest extends ILIAS_UI_TestBase
{
    private function getFactory() : I\Component\Table\Factory
    {
        return new I\Component\Table\Factory(
            new I\Component\SignalGenerator()
        );
    }

    public function testTableConstruction() : void
    {
        $f = $this->getFactory();
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Table\\Factory", $f);

        $pt = $f->presentation('title', array(), function () {
        });
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Table\\Presentation", $pt);

        $this->assertEquals("title", $pt->getTitle());
        $this->assertEquals(array(), $pt->getViewControls());
        $this->assertInstanceOf(Closure::class, $pt->getRowMapping());

        $pt = $pt
            ->withEnvironment(array('k' => 'v'))
            ->withData(array('dk' => 'dv'));
        $this->assertEquals(array('k' => 'v'), $pt->getEnvironment());
        $this->assertEquals(array('dk' => 'dv'), $pt->getData());
    }

    public function testBareTableRendering() : void
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getFactory();
        $pt = $f->presentation('title', array(), function () {
        });
        $expected = '' .
            '<div class="il-table-presentation">' .
            '	<h3 class="ilHeader">title</h3>' .
            '	<div class="il-table-presentation-data">		</div>' .
            '</div>';
        $this->assertHTMLEquals($expected, $r->render($pt->withData([])));
    }

    public function testRowConstruction() : void
    {
        $f = $this->getFactory();
        $pt = $f->presentation('title', array(), function () {
        });
        $row = new PresentationRow($pt->getSignalGenerator());

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Table\\PresentationRow", $row);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Signal", $row->getShowSignal());
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Signal", $row->getCloseSignal());
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Signal", $row->getToggleSignal());

        $this->assertEquals(
            "headline",
            $row->withHeadline("headline")->getHeadline()
        );
        $this->assertEquals(
            "subheadline",
            $row->withSubheadline("subheadline")->getSubheadline()
        );
        $this->assertEquals(
            array("f1" => "v1"),
            $row->withImportantFields(array("f1" => "v1"))->getImportantFields()
        );
        $this->assertEquals(
            "field_headline",
            $row->withFurtherFieldsHeadline("field_headline")->getFurtherFieldsHeadline()
        );
        $this->assertEquals(
            array("ff1" => "fv1"),
            $row->withFurtherFields(array("ff1" => "fv1"))->getFurtherFields()
        );
    }
}
