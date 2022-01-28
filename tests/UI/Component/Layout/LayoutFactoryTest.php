<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

/**
 * Tests on factory implementation for layout
 *
 * @author Nils Haagen <nhaagen@concepts-and-training.de>
 */
class LayoutFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "page" => [
                "context" => false,
                "rules" => false
        ]
    ];
    public string $factory_title = 'ILIAS\\UI\\Component\\Layout\\Factory';
}
