<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class CharacteristicValueFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = [
        'text' => array(
            'context' => false,
            'rules' => false
        )
    ];

    public $factory_title = 'ILIAS\\UI\\Component\\Listing\\CharacteristicValue\\Factory';
}
