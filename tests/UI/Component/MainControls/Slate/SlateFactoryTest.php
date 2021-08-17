<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

/**
 * Tests on factory implementation for Slates
 *
 * @author Nils Haagen <nhaagen@concepts-and-training.de>
 */
class SlateFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "legacy" => ["context" => false],
        "combined" => ["rules" => false]
    ];

    public string $factory_title = 'ILIAS\\UI\\Component\\MainControls\\Slate\\Factory';
}
