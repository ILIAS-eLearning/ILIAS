<?php

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component\Input\Field;
use \ILIAS\Data;

class SectionInputTest extends ILIAS_UI_TestBase
{
    public function getFieldFactory()
    {
        $factory = new Field\Factory(
            new IncrementalSignalGenerator(),
            new Data\Factory(),
            $this->getRefinery(),
            $this->getLanguage()
        );
        return $factory;
    }

    public function testSectionRendering()
    {
        $f = $this->getFieldFactory();
        $r = $this->getDefaultRenderer();
        $inputs = [
            $f->text("input1", "in 1"),
            $f->text("input2", "in 2")
        ];
        $label = 'section label';
        $byline = 'section byline';
        $section = $f->section($inputs, $label, $byline);
        $actual = $this->brutallyTrimHTML($r->render($section));
        $expected = <<<EOT
            <div class="il-section-input">
                <div class="il-section-input-header">
                    <h2>section label</h2>
                    <div class="il-section-input-header-byline">section byline</div>
                </div>
                <div class="form-group row">
                    <label for="" class="control-label col-sm-3">input1</label>
                    <div class="col-sm-9"><div class="help-block">in 1</div></div>
                </div>
                <div class="form-group row">
                    <label for="" class="control-label col-sm-3">input2</label>
                    <div class="col-sm-9"><div class="help-block">in 2</div></div>
                </div>
            </div>
EOT;
        $expected = $this->brutallyTrimHTML($expected);
        $this->assertEquals($expected, $actual);
    }
}
