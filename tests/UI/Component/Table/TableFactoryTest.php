<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

class TableFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "presentation" => [
            "context" => false,
            "rules" => true
        ],
        "data" => [
            "context" => false
        ]
    ];

    public string $factory_title = 'ILIAS\\UI\\Component\\Table\\Factory';
}
