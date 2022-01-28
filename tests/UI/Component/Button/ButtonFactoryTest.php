<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

class ButtonFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "standard" => ["context" => false],
        "close" => ["context" => false],
        "minimize" => ["context" => false],
        "shy" => ["context" => false],
        "tag" => ["context" => false],
        "bulky" => ["context" => false],
        "toggle" => ["context" => false]
    ];

    public string $factory_title = 'ILIAS\\UI\\Component\\Button\\Factory';
}
