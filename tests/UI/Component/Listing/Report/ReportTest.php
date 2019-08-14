<?php

require_once('tests/UI/Base.php');

class ReportTest extends ILIAS_UI_TestBase
{
    public function test_interfaces()
    {
        $f = $this->getReportFactory();

        $this->assertInstanceOf(
            'ILIAS\\UI\\Component\\Listing\\Report\\Factory',
            $f
        );

        $this->assertInstanceOf(
            'ILIAS\\UI\\Component\\Listing\\Report\\Standard',
            $f->standard($this->getStringItemsMock())
        );

        $this->assertInstanceOf(
            'ILIAS\\UI\\Component\\Listing\\Report\\Mini',
            $f->mini($this->getStringItemsMock())
        );
    }

    protected function getReportFactory()
    {
        return new ILIAS\UI\Implementation\Component\Listing\Report\Factory();
    }

    protected function getLegacyComponent($content)
    {
        return new \ILIAS\UI\Implementation\Component\Legacy\Legacy($content);
    }

    protected function getComponentItemsMock()
    {
        return [
            'label1' => $this->getLegacyComponent('items1'),
            'label2' => $this->getLegacyComponent('item2')
        ];
    }

    protected function getStringItemsMock()
    {
        return [
            'label1' => 'item1', 'label2' => 'item2', 'label3' => 'item3'
        ];
    }

    protected function getMixedItemsMock()
    {
        return [
            'label1' => 'item1',
            'label2' => $this->getLegacyComponent('item2'),
            'label3' => 'item3',
            'label4' => $this->getLegacyComponent('item4')
        ];
    }

    protected function getInvalidItemsMocks()
    {
        return [
            ['' => 'item'],
            ['label' => ''],
            []
        ];
    }
}
