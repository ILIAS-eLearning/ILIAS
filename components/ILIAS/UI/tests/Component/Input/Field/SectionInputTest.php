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
require_once(__DIR__ . "/CommonFieldRendering.php");

use ILIAS\UI\Implementation\Component\Input\Field;
use ILIAS\Data;

class SectionInputTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    protected DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    public function testSectionRendering(): void
    {
        $f = $this->getFieldFactory();
        $inputs = [
            $f->text("input1", "in 1"),
            $f->text("input2", "in 2")
        ];
        $label = 'section label';
        $byline = 'section byline';
        $section = $f->section($inputs, $label, $byline)->withNameFrom($this->name_source);
        $f1 = $this->getFormWrappedHtml(
            'text-field-input',
            'input1',
            '<input id="id_1" type="text"  name="name_0/name_1" class="c-field-text" />',
            'in 1',
            'id_1',
            null,
            'name_0/name_1'
        );
        $f2 = $this->getFormWrappedHtml(
            'text-field-input',
            'input2',
            '<input id="id_2" type="text"  name="name_0/name_2" class="c-field-text" />',
            'in 2',
            'id_2',
            null,
            'name_0/name_2'
        );
        $expected = $this->getFormWrappedHtml(
            'section-field-input',
            $label,
            $f1 . $f2,
            $byline,
            ''
        );
        $this->assertEquals($expected, $this->render($section));
    }


    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $inputs = [
            $f->text("input1")
        ];
        $label = 'section label';
        $section = $f->section($inputs, $label)->withNameFrom($this->name_source);

        $this->testWithError($section);
        $this->testWithNoByline($section);
        $this->testWithRequired($section);
        $this->testWithDisabled($section);
        $this->testWithAdditionalOnloadCodeRendersId($section);
    }

    public function testNestedSectionRendering(): void
    {
        $f = $this->getFieldFactory();
        $inputs_level4 = [$f->text("input3", "input3 byline")];
        $section_level4 = $f->section($inputs_level4, "Inner Section");

        $inputs_level3 = [$f->text("input2", "input2 byline"), $section_level4];
        $section_level3 = $f->section($inputs_level3, "Middle Section");

        $inputs_level2 = [$f->text("input1", "input1 byline"), $section_level3];
        $section_level2 = $f->section($inputs_level2, "Outermost Section");

        $nested_sections_html = $this->render($section_level2);

        $this->assertStringContainsString('<h2>Outermost Section</h2>', $nested_sections_html);
        $this->assertStringContainsString('<h3>Middle Section</h3>', $nested_sections_html);
        $this->assertStringContainsString('<h4>Inner Section</h4>', $nested_sections_html);
    }
}
