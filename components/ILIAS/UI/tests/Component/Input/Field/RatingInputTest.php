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

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class RatingInputTest extends ILIAS_UI_TestBase
{
    protected DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory(): I\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        return new I\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new SignalGenerator(),
            $df,
            new Refinery($df, $language),
            $language
        );
    }

    protected function buildRating(): \ILIAS\UI\Component\Input\Container\Form\FormInput
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        return $f
            ->rating($label, $byline)
            ->withNameFrom($this->name_source);
    }

    public function testRatingImplementsFactoryInterface(): void
    {
        $f = $this->buildFactory();
        $rating = $f->rating("label", "byline");
        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $rating);
        $this->assertInstanceOf(Field\Rating::class, $rating);
    }

    public function testRatingRenderBasic(): void
    {
        $r = $this->getDefaultRenderer();
        $rating = $this->buildRating();

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
            <div class="col-sm-8 col-md-9 col-lg-10">

                <div class="input-group il-input-rating" id="id_1">
                    <div class="il-input-rating__stars">
                        <input type="radio" id="id_1-5" name="name_0" value="5" aria-label="5" class="il-input-rating-scaleoption" />
                        <label class="glyphicon-star il-input-rating-star" for="id_1-5"></label>
                        <span class="label"><label class="glyphicon-saltire il-input-rating-reset" for="id_1-0"></label></span>

                        <input type="radio" id="id_1-4" name="name_0" value="4" aria-label="4" class="il-input-rating-scaleoption" />
                        <label class="glyphicon-star il-input-rating-star" for="id_1-4"></label>
                        <span class="label"><label class="glyphicon-saltire il-input-rating-reset" for="id_1-0"></label></span>

                        <input type="radio" id="id_1-3" name="name_0" value="3" aria-label="3" class="il-input-rating-scaleoption" />
                        <label class="glyphicon-star il-input-rating-star" for="id_1-3"></label>
                        <span class="label"><label class="glyphicon-saltire il-input-rating-reset" for="id_1-0"></label></span>

                        <input type="radio" id="id_1-2" name="name_0" value="2" aria-label="2" class="il-input-rating-scaleoption" />
                        <label class="glyphicon-star il-input-rating-star" for="id_1-2"></label>
                        <span class="label"><label class="glyphicon-saltire il-input-rating-reset" for="id_1-0"></label></span>

                        <input type="radio" id="id_1-1" name="name_0" value="1" aria-label="1" class="il-input-rating-scaleoption" />
                        <label class="glyphicon-star il-input-rating-star" for="id_1-1"></label>
                        <span class="label"><label class="glyphicon-saltire il-input-rating-reset" for="id_1-0"></label></span>
                    </div>

                    <input type="radio" id="id_1-0" name="name_0" value="0" class="il-input-rating-scaleoption reset-option" />
                </div>

                <div class="help-block">byline</div>
            </div>
        </div>');

        $this->assertEquals($expected, $this->brutallyTrimHTML($r->render($rating)));
    }

    public function testRatingRenderFull(): void
    {
        $r = $this->getDefaultRenderer();
        $rating = $this->buildRating()
            ->withQuestionText('question text')
            ->withOptionLabels('l1', 'l2', 'l3', 'l4', 'l5')
            ->withDisabled(true)
            ->withValue(4);

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
            <div class="col-sm-8 col-md-9 col-lg-10">

                <div class="input-group il-input-rating disabled" id="id_1">
                    <p class="il-input-rating__text">question text</p>
                    <div class="il-input-rating__stars">
                        <input type="radio" id="id_1-5" name="name_0" value="5" aria-label="l5" class="il-input-rating-scaleoption" disabled="disabled"/>
                        <label class="glyphicon-star il-input-rating-star" for="id_1-5"></label>
                        <span class="label">l5<label class="glyphicon-saltire il-input-rating-reset" for="id_1-0"></label></span>

                        <input type="radio" id="id_1-4" name="name_0" value="4" aria-label="l4" class="il-input-rating-scaleoption" disabled="disabled" checked="checked"/>
                        <label class="glyphicon-star il-input-rating-star" for="id_1-4"></label>
                        <span class="label">l4<label class="glyphicon-saltire il-input-rating-reset" for="id_1-0"></label></span>

                        <input type="radio" id="id_1-3" name="name_0" value="3" aria-label="l3" class="il-input-rating-scaleoption" disabled="disabled"/>
                        <label class="glyphicon-star il-input-rating-star" for="id_1-3"></label>
                        <span class="label">l3<label class="glyphicon-saltire il-input-rating-reset" for="id_1-0"></label></span>

                        <input type="radio" id="id_1-2" name="name_0" value="2" aria-label="l2" class="il-input-rating-scaleoption" disabled="disabled"/>
                        <label class="glyphicon-star il-input-rating-star" for="id_1-2"></label>
                        <span class="label">l2<label class="glyphicon-saltire il-input-rating-reset" for="id_1-0"></label></span>

                        <input type="radio" id="id_1-1" name="name_0" value="1" aria-label="l1" class="il-input-rating-scaleoption" disabled="disabled"/>
                        <label class="glyphicon-star il-input-rating-star" for="id_1-1"></label>
                        <span class="label">l1<label class="glyphicon-saltire il-input-rating-reset" for="id_1-0"></label></span>
                    </div>

                    <input type="radio" id="id_1-0" name="name_0" value="0" class="il-input-rating-scaleoption reset-option" disabled="disabled" />
                </div>

                <div class="help-block">byline</div>
            </div>
        </div>');

        $this->assertEquals($expected, $this->brutallyTrimHTML($r->render($rating)));
    }
}
