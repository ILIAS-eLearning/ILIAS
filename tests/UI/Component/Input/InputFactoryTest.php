<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

class InputFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "container" => [
            "rules" => false,
        ],
    ];

    public string $factory_title = 'ILIAS\\UI\\Component\\Input\\Factory';
}
