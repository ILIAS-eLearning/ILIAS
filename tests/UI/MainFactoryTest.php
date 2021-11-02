<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

/**
 * Class MainFactoryTest
 */
class MainFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "card" => ["context" => false],
        "deck" => ["context" => false],
        "image" => ["context" => false, "rules" => false],
        "legacy" => ["context" => false],
        "viewControl" => ["rules" => false],
        "input" => ["rules" => false],
        "table" => ["rules" => false],
        "layout" => ["rules" => false],
        "menu" => ["rules" => false],
        "symbol" => ["rules" => false]
    ];

    public string $factory_title = 'ILIAS\\UI\\Factory';

    /**
     * @doesNotPerformAssertions
     */
    public function test_proper_namespace() : void
    {
        // Nothing to test here.
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_proper_name() : void
    {
        // Nothing to test here.
    }

    protected function get_regex_factory_namespace() : string
    {
        return "\\\\ILIAS\\\\UI\\\\Component";
    }
}
