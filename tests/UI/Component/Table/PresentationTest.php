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

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/TableTestBase.php");

use ILIAS\UI\Implementation as I;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Table\PresentationRow;

/**
 * Tests for Presentation Table.
 */
class PresentationTest extends TableTestBase
{
    public function testTableConstruction(): void
    {
        $f = $this->getTableFactory();
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

    public function testRowConstruction(): void
    {
        $f = $this->getTableFactory();
        $pt = $f->presentation('title', [], function (): void {
        });
        $row = new PresentationRow($pt->getSignalGenerator(), 'table_id');

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
            public I\Component\SignalGenerator $sig_gen;

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
<div class="il-table-presentation" id="id_3">
    <h3 class="ilHeader">title</h3>
    <div class="il-table-presentation-viewcontrols">
        <div class="l-bar__space-keeper l-bar__space-keeper--space-between">
            <div class="l-bar__group">
                <div class="l-bar__element">
                    <button class="btn btn-default" id="id_1">presentation_table_expand</button>
                    <button class="btn btn-default" id="id_2">presentation_table_collapse</button>
                </div>
            </div>
            <div class="l-bar__group"></div>
        </div>
    </div>
    <div class="il-table-presentation-data">
        <div class="il-table-presentation-row row collapsed" id="id_4">

            <div class="il-table-presentation-row-controls col-lg-auto col-sm-12">
                <div class="il-table-presentation-row-controls-expander inline">
                    <a tabindex="0" class="glyph" href="#" aria-label="expand_content" id="id_5">
                        <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                    </a>
                </div>
                <div class="il-table-presentation-row-controls-collapser">
                    <a tabindex="0" class="glyph" href="#" aria-label="collapse_content" id="id_6">
                        <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                    </a>
                </div>
            </div>

            <div class="il-table-presentation-row-contents col-lg col-sm-12">
                <div class="row">
                   <div class="il-table-presentation-row-header col-lg col-sm-12">
                       <h4 class="il-table-presentation-row-header-headline" onClick="$(document).trigger('il_signal...');">some title<br /><small>some type</small>
                       </h4>
                       <div class="il-table-presentation-row-header-fields">
                          <div class="l-bar__space-keeper">
                              <div class="l-bar__group">
                                  <div class="il-table-presentation-row-header-fields-value l-bar__element">important-1</div>
                              </div>
                          </div>
                          <div class="l-bar__space-keeper">
                              <div class="l-bar__group">
                                  <div class="il-table-presentation-row-header-fields-value l-bar__element">important-2</div>
                              </div>
                          </div>
                          <button class="btn btn-link" id="id_7">presentation_table_more</button>
                       </div>
                   </div>
                   <div class="il-table-presentation-actions col-lg-auto col-sm-12">
                        <button class="btn btn-default" data-action="#" id="id_8">do</button><br />
                   </div>
                   <div class="il-table-presentation-row-expanded col-lg-12 col-sm-12">
                        <div class="row">
                            <div class="il-table-presentation-desclist col-lg col-sm-12 desclist-column">
                                <dl>
                                   <dt>1st</dt>
                                   <dd>first content</dd>
                                   <dt>2nd</dt>
                                   <dd>second content</dd>
                                </dl>
                            </div>
                            <div class="il-table-presentation-details col-lg-5 col-sm-12">
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
    </div>
</div>
EXP;

        $r = $this->getDefaultRenderer();
        $f = $this->getTableFactory();
        $pt = $f->presentation('title', [], $mapping);
        $actual = $r->render($pt->withData($this->getDummyData()));
        $this->assertEquals(
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
<div class="il-table-presentation" id="id_3">
    <h3 class="ilHeader">title</h3>
    <div class="il-table-presentation-viewcontrols">

        <div class="l-bar__space-keeper l-bar__space-keeper--space-between">
            <div class="l-bar__group">
                <div class="l-bar__element">

                    <button class="btn btn-default" id="id_1">presentation_table_expand</button>
                    <button class="btn btn-default" id="id_2">presentation_table_collapse</button>
                </div>
            </div>
            <div class="l-bar__group"></div>
        </div>
    </div>
    <div class="il-table-presentation-data">
        <div class="il-table-presentation-row row collapsed" id="id_4">

            <div class="il-table-presentation-row-controls col-lg-auto col-sm-12">
                <div class="il-table-presentation-row-controls-expander inline">
                    <a tabindex="0" class="glyph" href="#" aria-label="expand_content" id="id_5">
                        <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                    </a>
                </div>
                <div class="il-table-presentation-row-controls-collapser">
                    <a tabindex="0" class="glyph" href="#" aria-label="collapse_content" id="id_6">
                        <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                    </a>
                </div>
            </div>

            <div class="il-table-presentation-row-contents col-lg col-sm-12">
                <div class="row">
                    <div class="il-table-presentation-row-header col-lg col-sm-12">
                        <h4 class="il-table-presentation-row-header-headline" onClick="$(document).trigger('il_signal...');">some title</h4>
                        <div class="il-table-presentation-row-header-fields">                  
                            <button class="btn btn-link" id="id_7">presentation_table_more</button>
                        </div>
                    </div>
                    <div class="il-table-presentation-actions col-lg-auto col-sm-12"></div>
                    <div class="il-table-presentation-row-expanded col-lg-12 col-sm-12">
                        <div class="row">
                            <div class="il-table-presentation-desclist col-lg col-sm-12">
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
    </div>
</div>
EXP;
        $r = $this->getDefaultRenderer();
        $f = $this->getTableFactory();
        $pt = $f->presentation('title', [], $mapping);
        $actual = $r->render($pt->withData($this->getDummyData()));
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->brutallyTrimSignals($actual))
        );
    }

    public function testRenderEmptyTableEntry(): void
    {
        $mapping = fn(PresentationRow $row, mixed $record, \ILIAS\UI\Factory $ui_factory, mixed $environment) => $row;

        $table = $this->getTableFactory()->presentation('', [], $mapping);

        $html = $this->getDefaultRenderer()->render($table);

        $translation = $this->getLanguage()->txt('ui_table_no_records');

        $this->assertTrue(str_contains($html, $translation));
    }
}
