<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

class PanelFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "standard" => [
                "context" => false,
        ],
        "sub" => [
                "context" => false,
        ],
        "report" => [
                "context" => false,
                "rules" => false
        ],
        "secondary" => [
            "context" => false
        ]
    ];


    public string $factory_title = 'ILIAS\\UI\\Component\\Panel\\Factory';
}
