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

require_once(__DIR__ . '/CharacteristicValueTest.php');

class CharacteristicValueTextTest extends CharacteristicValueTest
{
    public function test_getItems(): void
    {
        $f = $this->getCharacteristicValueFactory();

        $items = $this->getTextItemsMock();
        $textListing = $f->text($items);
        $this->assertEquals($items, $textListing->getItems());
    }

    public function test_validation(): void
    {
        $f = $this->getCharacteristicValueFactory();

        foreach ($this->getInvalidTextItemsMocks() as $invalidItemsMock) {
            try {
                $f->text($invalidItemsMock);

                $this->throwException(new Exception(
                    'expected InvalidArgumentException, catched none'
                ));
            } catch (InvalidArgumentException $e) {
                $this->assertInstanceOf('InvalidArgumentException', $e);
            }
        }
    }

    public function test_rendered(): void
    {
        $f = $this->getCharacteristicValueFactory();
        $r = $this->getDefaultRenderer();

        $items = $this->getTextItemsMock();
        $textListing = $f->text($items);
        $actualHtml = $r->render($textListing);

        $expectedHtml = $this->getExpectedHtml();

        $this->assertHTMLEquals($expectedHtml, $actualHtml);
    }

    private function getExpectedHtml(): string
    {
        $html = '<div class="il-listing-characteristic-value clearfix">';
        $html .= '	<div class="il-listing-characteristic-value-row clearfix">';
        $html .= '		<div class="il-listing-characteristic-value-label">label1</div>';
        $html .= '		<div class="il-listing-characteristic-value-item">item1</div>';
        $html .= '	</div>';
        $html .= '	<div class="il-listing-characteristic-value-row clearfix">';
        $html .= '		<div class="il-listing-characteristic-value-label">label2</div>';
        $html .= '		<div class="il-listing-characteristic-value-item">item2</div>';
        $html .= '	</div>';
        $html .= '	<div class="il-listing-characteristic-value-row clearfix">';
        $html .= '		<div class="il-listing-characteristic-value-label">label3</div>';
        $html .= '		<div class="il-listing-characteristic-value-item">item3</div>';
        $html .= '	</div>';
        $html .= '</div>';

        return $html;
    }
}
