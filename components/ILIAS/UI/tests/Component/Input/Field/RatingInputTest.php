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

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\FiveStarRatingScale;

class RatingInputTest extends ILIAS_UI_TestBase
{
    protected DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory(): I\Input\Field\Factory
    {
        $df = new DataFactory();
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

        $expected = $this->brutallyTrimHTML(
            '<div class="form-group row">
                <label class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
                <div class="col-sm-8 col-md-9 col-lg-10">

                    <fieldset class="input-group il-input-rating" id="id_1">
                        <legend class="il-input-rating__text" id="id_1_desc"></legend>
                        <div class="il-input-rating__stars" role="radiogroup">

                            <div class="il-input-rating__options">
                                <input aria-describedby="id_1_desc" type="radio" id="id_1-5" name="name_0" value="5" class="il-input-rating-scaleoption" />
                                <label class="glyphicon-star il-input-rating-star" for="id_1-5" aria-label="5stars"></label>

                                <input aria-describedby="id_1_desc" type="radio" id="id_1-4" name="name_0" value="4" class="il-input-rating-scaleoption" />
                                <label class="glyphicon-star il-input-rating-star" for="id_1-4" aria-label="4stars"></label>

                                <input aria-describedby="id_1_desc" type="radio" id="id_1-3" name="name_0" value="3" class="il-input-rating-scaleoption" />
                                <label class="glyphicon-star il-input-rating-star" for="id_1-3" aria-label="3stars"></label>

                                <input aria-describedby="id_1_desc" type="radio" id="id_1-2" name="name_0" value="2" class="il-input-rating-scaleoption" />
                                <label class="glyphicon-star il-input-rating-star" for="id_1-2" aria-label="2stars"></label>

                                <input aria-describedby="id_1_desc" type="radio" id="id_1-1" name="name_0" value="1" class="il-input-rating-scaleoption" />
                                <label class="glyphicon-star il-input-rating-star" for="id_1-1" aria-label="1stars"></label>
                            </div>
                            <div class="il-input-rating__none">
                                <label for="id_1-0" aria-label="reset_stars">reset_stars</label>
                                <input aria-describedby="" type="radio" id="id_1-0" name="name_0" value="0" checked="checked"/>
                            </div>
                        </div>
                    </fieldset>

                    <div class="help-block">byline</div>
                </div>
            </div>'
        );
        $this->assertEquals($expected, $this->brutallyTrimHTML($r->render($rating)));
    }

    public function testRatingRenderFull(): void
    {
        $r = $this->getDefaultRenderer();
        $rating = $this->buildRating()
            ->withAdditionalText('question text')
            ->withDisabled(true)
            ->withValue(FiveStarRatingScale::GOOD)
            ->withCurrentAverage(3);

        $expected = $this->brutallyTrimHTML(
            '<div class="form-group row">
                <label class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
                <div class="col-sm-8 col-md-9 col-lg-10">

                    <fieldset class="input-group il-input-rating disabled" id="id_1">
                        <legend class="il-input-rating__text" id="id_1_desc">question text</legend>

                        <div class="il-input-rating__stars" role="radiogroup">

                            <div class="il-input-rating__options" title="rating_average">

                                <div class="il-input-rating__average">
                                    <div class="il-input-rating__average_value" style="width:60%;"></div>
                                </div>

                                <input aria-describedby="id_1_desc" type="radio" id="id_1-5" name="name_0" value="5" class="il-input-rating-scaleoption" disabled="disabled"/>
                                <label class="glyphicon-star il-input-rating-star" for="id_1-5" aria-label="5stars"></label>

                                <input aria-describedby="id_1_desc" type="radio" id="id_1-4" name="name_0" value="4" class="il-input-rating-scaleoption" disabled="disabled" checked="checked"/>
                                <label class="glyphicon-star il-input-rating-star" for="id_1-4" aria-label="4stars"></label>

                                <input aria-describedby="id_1_desc" type="radio" id="id_1-3" name="name_0" value="3" class="il-input-rating-scaleoption" disabled="disabled"/>
                                <label class="glyphicon-star il-input-rating-star" for="id_1-3" aria-label="3stars"></label>

                                <input aria-describedby="id_1_desc" type="radio" id="id_1-2" name="name_0" value="2" class="il-input-rating-scaleoption" disabled="disabled"/>
                                <label class="glyphicon-star il-input-rating-star" for="id_1-2" aria-label="2stars"></label>

                                <input aria-describedby="id_1_desc" type="radio" id="id_1-1" name="name_0" value="1" class="il-input-rating-scaleoption" disabled="disabled"/>
                                <label class="glyphicon-star il-input-rating-star" for="id_1-1" aria-label="1stars"></label>
                            </div>
                        
                            <div class="il-input-rating__none">
                                <label for="id_1-0" aria-label="reset_stars">reset_stars</label>
                                <input aria-describedby="" type="radio" id="id_1-0" name="name_0" value="0" />
                            </div>
                        
                        </div>
                    </fieldset>

                    <div class="help-block">byline</div>
                </div>
            </div>'
        );

        $this->assertEquals($expected, $this->brutallyTrimHTML($r->render($rating)));
    }

    public function testRatingAverage(): void
    {
        $rating = $this->buildRating();
        $this->assertNull($rating->getCurrentAverage());
        $this->assertEquals(2.1, $rating->withCurrentAverage(2.1)->getCurrentAverage());
    }

    public function testRatingAverageException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $rating = $this->buildRating()->withCurrentAverage(7);
    }


}
