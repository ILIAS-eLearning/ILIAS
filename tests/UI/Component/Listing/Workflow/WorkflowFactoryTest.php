<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

class WorkflowFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "step" => [
                "context" => false,
                "rules" => false
        ],
        "linear" => [
                "context" => false,
                "rules" => false
        ]
    ];


    public string $factory_title = 'ILIAS\\UI\\Component\\Listing\\Workflow\\Factory';
}
