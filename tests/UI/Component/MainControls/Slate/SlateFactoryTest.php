<?php

require_once 'tests/UI/AbstractFactoryTest.php';

/**
 * Tests on factory implementation for Slates
 *
 * @author Nils Haagen <nhaagen@concepts-and-training.de>
 */
class SlateFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = [
        "legacy" => ["context" => false],
        "combined" => ["rules" => false]
    ];
    public $factory_title = 'ILIAS\\UI\\Component\\MainControls\\Slate\\Factory';
}
