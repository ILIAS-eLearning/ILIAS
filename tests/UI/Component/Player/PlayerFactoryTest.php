<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

class PlayerFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "audio" => [
        ]
    ];


    public string $factory_title = 'ILIAS\\UI\\Component\\Player\\Factory';
}
