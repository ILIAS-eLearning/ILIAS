<?php

require_once('tests/UI/Base.php');

use ILIAS\UI\Implementation\Component\Listing\CharacteristicValue\Factory as CharacteristicValueFactory;

class CharacteristicValueTest extends ILIAS_UI_TestBase
{
    public function test_interfaces()
    {
        $f = $this->getCharacteristicValueFactory();

        $this->assertInstanceOf(
            'ILIAS\\UI\\Component\\Listing\\CharacteristicValue\\Factory',
            $f
        );

        $this->assertInstanceOf(
            'ILIAS\\UI\\Component\\Listing\\CharacteristicValue\\Text',
            $f->text($this->getTextItemsMock())
        );
    }

    protected function getCharacteristicValueFactory() : CharacteristicValueFactory
    {
        return new CharacteristicValueFactory();
    }

    protected function getTextItemsMock() : array
    {
        return [
            'label1' => 'item1', 'label2' => 'item2', 'label3' => 'item3'
        ];
    }

    protected function getInvalidTextItemsMocks() : array
    {
        return [
            ['' => 'item'],
            ['label' => ''],
            []
        ];
    }
}
