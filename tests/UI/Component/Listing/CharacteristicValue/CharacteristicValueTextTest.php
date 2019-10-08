<?php

require_once(__DIR__ . '/CharacteristicValueTest.php');

class CharacteristicValueTextTest extends CharacteristicValueTest
{
    public function test_getItems()
    {
        $f = $this->getCharacteristicValueFactory();

        $items = $this->getTextItemsMock();
        $textListing = $f->text($items);
        $this->assertEquals($items, $textListing->getItems());
    }

    public function test_validation()
    {
        $f = $this->getCharacteristicValueFactory();

        foreach($this->getInvalidTextItemsMocks() as $invalidItemsMock)
        {
            try
            {
                $f->text($invalidItemsMock);

                $this->throwException(new Exception(
                    'expected InvalidArgumentException, catched none'
                ));
            }
            catch(InvalidArgumentException $e)
            {
                $this->assertInstanceOf('InvalidArgumentException', $e);
            }
        }
    }

    public function test_rendered()
    {
        $f = $this->getCharacteristicValueFactory();
        $r = $this->getDefaultRenderer();

        $items = $this->getTextItemsMock();
        $textListing = $f->text($items);
        $actualHtml = $r->render($textListing);

        $expectedHtml = $this->getExpectedHtml();

        $this->assertHTMLEquals($expectedHtml, $actualHtml);
    }

    private function getExpectedHtml() : string
    {
        $html  = '<div class="il-listing-characteristic-value clearfix">';
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
