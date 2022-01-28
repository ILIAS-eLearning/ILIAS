<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

class ListingFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "ordered" => [
                "context" => false,
                "rules" => false
        ],
        "unordered" => [
                "context" => false,
                "rules" => false
        ],
        "descriptive" => [
                "context" => false,
                "rules" => false
        ],
        "workflow" => [
                "context" => false,
                "rules" => false
        ],
        "characteristicValue" => [
            "context" => false,
            "rules" => false
        ]
    ];


    public string $factory_title = 'ILIAS\\UI\\Component\\Listing\\Factory';
}
