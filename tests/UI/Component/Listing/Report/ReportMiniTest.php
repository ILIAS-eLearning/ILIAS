<?php

require_once(__DIR__ . '/ReportTest.php');

class ReportMiniTest extends ReportTest
{
    public function test_getItems()
    {
        $f = $this->getReportFactory();

        $items = $this->getStringItemsMock();
        $standard = $f->mini($items);
        $this->assertEquals($items, $standard->getItems());

        $items = $this->getComponentItemsMock();
        $standard = $f->mini($items);
        $this->assertEquals($items, $standard->getItems());
    }

    public function test_validation()
    {
        $f = $this->getReportFactory();

        foreach($this->getInvalidItemsMocks() as $invalidItemsMock)
        {
            try
            {
                $f->mini($invalidItemsMock);

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
        $f = $this->getReportFactory();
        $r = $this->getDefaultRenderer();

        $items = $this->getMixedItemsMock();
        $standard = $f->mini($items);
        $actualHtml = $r->render($standard);

        $expectedHtml = $this->getExpectedMixedItemsMockHtml();

        $this->assertHTMLEquals($expectedHtml, $actualHtml);
    }

    private function getExpectedMixedItemsMockHtml()
    {
        $html =  '<div class="row">';
        $html .= '		<div class="il-listing-report-row clearfix">';
        $html .= '				<div class="il-listing-report-label col-md-9 col-xs-9">label1</div>';
        $html .= '				<div class="il-listing-report-item col-md-3 col-xs-3 ilRight">item1</div>';
        $html .= '		</div>';
        $html .= '		<div class="il-listing-report-row clearfix">';
        $html .= '				<div class="il-listing-report-label col-md-9 col-xs-9">label2</div>';
        $html .= '				<div class="il-listing-report-item col-md-3 col-xs-3 ilRight">item2</div>';
        $html .= '		</div>';
        $html .= '		<div class="il-listing-report-row clearfix">';
        $html .= '				<div class="il-listing-report-label col-md-9 col-xs-9">label3</div>';
        $html .= '				<div class="il-listing-report-item col-md-3 col-xs-3 ilRight">item3</div>';
        $html .= '		</div>';
        $html .= '		<div class="il-listing-report-row clearfix">';
        $html .= '				<div class="il-listing-report-label col-md-9 col-xs-9">label4</div>';
        $html .= '				<div class="il-listing-report-item col-md-3 col-xs-3 ilRight">item4</div>';
        $html .= '		</div>';
        $html .= '</div>';

        return $html;
    }
}
