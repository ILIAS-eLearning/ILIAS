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
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation as I;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Table\PresentationRow;

/**
 * Tests for Presentation Table.
 */
class PresentationTest extends ILIAS_UI_TestBase
{
    private function getFactory(): I\Component\Table\Factory
    {
        return new I\Component\Table\Factory(
            new I\Component\SignalGenerator()
        );
    }

    public function testTableConstruction(): void
    {
        $f = $this->getFactory();
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Table\\Factory", $f);

        $pt = $f->presentation('title', [], function (): void {
        });
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Table\\Presentation", $pt);

        $this->assertEquals("title", $pt->getTitle());
        $this->assertEquals([], $pt->getViewControls());
        $this->assertInstanceOf(Closure::class, $pt->getRowMapping());

        $pt = $pt
            ->withEnvironment(array('k' => 'v'))
            ->withData(array('dk' => 'dv'));
        $this->assertEquals(array('k' => 'v'), $pt->getEnvironment());
        $this->assertEquals(array('dk' => 'dv'), $pt->getData());
    }

    public function testBareTableRendering(): void
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getFactory();
        $pt = $f->presentation('title', [], function (): void {
        });
        $expected = '' .
            '<div class="il-table-presentation">' .
            '	<h3 class="ilHeader">title</h3>' .
            '	<div class="il-table-presentation-data">		</div>' .
            '</div>';
        $this->assertHTMLEquals($expected, $r->render($pt->withData([])));
    }

    public function testRowConstruction(): void
    {
        $f = $this->getFactory();
        $pt = $f->presentation('title', [], function (): void {
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

    public function getUIFactory(): NoUIFactory
    {
        $factory = new class () extends NoUIFactory {
            public function button(): C\Button\Factory
            {
                return new I\Component\Button\Factory(
                    new I\Component\SignalGenerator()
                );
            }
            public function symbol(): ILIAS\UI\Component\Symbol\Factory
            {
                return new I\Component\Symbol\Factory(
                    new I\Component\Symbol\Icon\Factory(),
                    new I\Component\Symbol\Glyph\Factory(),
                    new I\Component\Symbol\Avatar\Factory()
                );
            }
        };
        $factory->sig_gen = new I\Component\SignalGenerator();
        return $factory;
    }

    protected function getDummyData(): array
    {
        return [[
            'headline' => 'some title',
            'subhead' => 'some type',
            'important_fields' => ['important-1','important-2'],
            'content' => ['1st' => 'first content', '2nd' => 'second content'],
            'further_headline' => 'further fields',
            'further_fields' => ['f-1' => 'further', 'f-2' => 'way further'],
            'action' => 'do'
        ]];
    }

    public function testFullRendering(): void
    {
        $mapping = function ($row, $record, $ui_factory, $environment) {
            return $row
                ->withHeadline($record['headline'])
                ->withSubheadline($record['subhead'])
                ->withImportantFields($record['important_fields'])
                ->withContent((new I\Component\Listing\Descriptive($record['content'])))
                ->withFurtherFieldsHeadline($record['further_headline'])
                ->withFurtherFields($record['further_fields'])
                ->withAction((new I\Component\Button\Standard($record['action'], '#')));
        };

        $expected = <<<EXP
<div class="il-table-presentation">
    <h3 class="ilHeader">title</h3>
    <div class="il-table-presentation-data">
        <div class="il-table-presentation-row row collapsed" id="id_1">

            <div class="il-table-presentation-row-controls">
                <div class="il-table-presentation-row-controls-expander inline">
                    <a class="glyph" href="#" aria-label="expand_content" id="id_2">
                        <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                    </a>
                </div>
                <div class="il-table-presentation-row-controls-collapser">
                    <a class="glyph" href="#" aria-label="collapse_content" id="id_3">
                        <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                    </a>
                </div>
            </div>

            <div class="il-table-presentation-row-contents">
                <div class="il-table-presentation-actions">
                    <button class="btn btn-default" data-action="#" id="id_5">do</button>
                    <br />
                </div>
                <div class="il-table-presentation-row-header">
                    <h4 class="il-table-presentation-row-header-headline" onClick="$(document).trigger('il_signal...');">some title<br /><small>some type</small>
                    </h4>
                    <div class="il-table-presentation-row-header-fields">important-1|important-2|<button class="btn btn-link" id="id_4">presentation_table_more</button></div>
                </div>

                <div class="il-table-presentation-row-expanded">
                    <div class="il-table-presentation-desclist inline desclist-column">
                        <dl>
                            <dt>1st</dt>
                            <dd>first content</dd>
                            <dt>2nd</dt>
                            <dd>second content</dd>
                        </dl>
                    </div>
                    
                    <div class="il-table-presentation-details inline">
                        <div class="il-table-presentation-fields">
                            <h5>further fields</h5>
                            <span class="il-item-property-name">f-1</span>
                            <span class="il-item-property-value">further</span>
                            <br />
                            <span class="il-item-property-name">f-2</span>
                            <span class="il-item-property-value">way further</span>
                            <br />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
EXP;

        $r = $this->getDefaultRenderer();
        $f = $this->getFactory();
        $pt = $f->presentation('title', [], $mapping);
        $actual = $r->render($pt->withData($this->getDummyData()));
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->brutallyTrimSignals($actual))
        );
    }


    public function testMinimalRendering(): void
    {
        $mapping = function ($row, $record, $ui_factory, $environment) {
            return $row
                ->withHeadline($record['headline'])
                ->withContent((new I\Component\Listing\Descriptive($record['content'])));
        };

        $expected = <<<EXP
<div class="il-table-presentation">
    <h3 class="ilHeader">title</h3>
    <div class="il-table-presentation-data">
        <div class="il-table-presentation-row row collapsed" id="id_1">

            <div class="il-table-presentation-row-controls">
                <div class="il-table-presentation-row-controls-expander inline">
                    <a class="glyph" href="#" aria-label="expand_content" id="id_2">
                        <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                    </a>
                </div>
                <div class="il-table-presentation-row-controls-collapser">
                    <a class="glyph" href="#" aria-label="collapse_content" id="id_3">
                        <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                    </a>
                </div>
            </div>

            <div class="il-table-presentation-row-contents">
                <div class="il-table-presentation-actions"></div>
                <div class="il-table-presentation-row-header">
                    <h4 class="il-table-presentation-row-header-headline" onClick="$(document).trigger('il_signal...');">some title</h4>
                    <div class="il-table-presentation-row-header-fields">
                        <button class="btn btn-link" id="id_4">presentation_table_more</button>
                    </div>
                </div>
                <div class="il-table-presentation-row-expanded">
                    <div class="il-table-presentation-desclist inline">
                        <dl>
                            <dt>1st</dt>
                            <dd>first content</dd>
                            <dt>2nd</dt>
                            <dd>second content</dd>
                        </dl>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
EXP;
        $r = $this->getDefaultRenderer();
        $f = $this->getFactory();
        $pt = $f->presentation('title', [], $mapping);
        $actual = $r->render($pt->withData($this->getDummyData()));
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->brutallyTrimSignals($actual))
        );
    }
}
