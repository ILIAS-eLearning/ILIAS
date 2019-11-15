<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class CounterFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array( "status" => array("context" => false)
        );


    public $factory_title = 'ILIAS\\UI\\Component\\Counter\\Factory';
}
