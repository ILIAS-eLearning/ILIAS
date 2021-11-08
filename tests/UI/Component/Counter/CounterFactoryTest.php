<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

class CounterFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = array( "status" => array("context" => false));
    public string $factory_title = 'ILIAS\\UI\\Component\\Counter\\Factory';
}
