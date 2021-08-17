<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

class ImageFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "standard" => [
            "context" => false,
            "rules" => false
        ],
        "responsive" => [
            "context" => false,
            "rules" => false
        ]
    ];


    public string $factory_title = 'ILIAS\\UI\\Component\\Image\\Factory';
}
