<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

class CharacteristicValueFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        'text' => [
            'context' => false,
            'rules' => false
        ]
    ];

    public string $factory_title = 'ILIAS\\UI\\Component\\Listing\\CharacteristicValue\\Factory';
}
