<?php

require_once(__DIR__ . '/ReportTest.php');

class ReportStandardTest extends ReportTest
{
    public function test_getItems()
    {
        $f = $this->getReportFactory();

        $items = $this->getStringItemsMock();
        $standard = $f->standard($items);
        $this->assertEquals($items, $standard->getItems());

        $items = $this->getComponentItemsMock();
        $standard = $f->standard($items);
        $this->assertEquals($items, $standard->getItems());
    }

    public function test_getDivider()
    {
        $f = $this->getReportFactory();
        $items = $this->getStringItemsMock();
        $divider = $this->getDividerComponent();

        $standard = $f->standard($items);
        $this->assertNull($standard->getDivider());

        $standard = $standard->withDivider($divider);
        $this->assertEquals($divider, $standard->getDivider());
    }

    public function test_hasDivider()
    {
        $f = $this->getReportFactory();
        $items = $this->getStringItemsMock();
        $divider = $this->getDividerComponent();

        $standard = $f->standard($items);
        $this->assertFalse($standard->hasDivider());

        $standard = $standard->withDivider($divider);
        $this->assertTrue($standard->hasDivider());
    }

    public function test_withDivider()
    {
        $f = $this->getReportFactory();
        $items = $this->getStringItemsMock();
        $divider = $this->getDividerComponent();

        $standard = $f->standard($items)->withDivider($divider);

        $this->assertEquals($divider, $standard->getDivider());
    }

    public function test_validation()
    {
        $f = $this->getReportFactory();

        foreach($this->getInvalidItemsMocks() as $invalidItemsMock)
        {
            try
            {
                $f->standard($invalidItemsMock);

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

    public function test_rendered_with_divider()
    {
        $f = $this->getReportFactory();
        $r = $this->getDefaultRenderer();

        $items = $this->getMixedItemsMock();
        $standard = $f->standard($items)->withDivider($this->getDividerComponent());
        $actualHtml = $r->render($standard);

        $expectedHtml = $this->getExpectedMixedItemsMockHtml(true);

        $this->assertHTMLEquals($expectedHtml, $actualHtml);
    }

    public function test_rendered_without_divider()
    {
        $f = $this->getReportFactory();
        $r = $this->getDefaultRenderer();

        $items = $this->getMixedItemsMock();
        $standard = $f->standard($items);
        $actualHtml = $r->render($standard);

        $expectedHtml = $this->getExpectedMixedItemsMockHtml(false);

        $this->assertHTMLEquals($expectedHtml, $actualHtml);
    }

    private function getExpectedMixedItemsMockHtml($withDivider)
    {
        $html =  '<div class="row">';
        $html .= '		<div class="il-listing-report-row clearfix">';
        $html .= '			<div class="il-listing-report-label col-md-6 col-xs-8">label1</div>';
        $html .= '			<div class="il-listing-report-item col-md-6 col-xs-4">item1</div>';
        $html .= '		</div>';
        if($withDivider)
        {
            $html .= '			<div class="col-md-12">';
            $html .= '<hr  />';
            $html .= '</div>';
        }
        $html .= '		<div class="il-listing-report-row clearfix">';
        $html .= '			<div class="il-listing-report-label col-md-6 col-xs-8">label2</div>';
        $html .= '			<div class="il-listing-report-item col-md-6 col-xs-4">item2</div>';
        $html .= '		</div>';
        if($withDivider)
        {
            $html .= '			<div class="col-md-12">';
            $html .= '<hr  />';
            $html .= '</div>';
        }
        $html .= '		<div class="il-listing-report-row clearfix">';
        $html .= '			<div class="il-listing-report-label col-md-6 col-xs-8">label3</div>';
        $html .= '			<div class="il-listing-report-item col-md-6 col-xs-4">item3</div>';
        $html .= '		</div>';
        if($withDivider)
        {
            $html .= '			<div class="col-md-12">';
            $html .= '<hr  />';
            $html .= '</div>';
        }
        $html .= '		<div class="il-listing-report-row clearfix">';
        $html .= '			<div class="il-listing-report-label col-md-6 col-xs-8">label4</div>';
        $html .= '			<div class="il-listing-report-item col-md-6 col-xs-4">item4</div>';
        $html .= '		</div>';
        $html .= '</div>';

        return $html;
    }

    private function getDividerComponent()
    {
        return new \ILIAS\UI\Implementation\Component\Divider\Horizontal();
    }
}
