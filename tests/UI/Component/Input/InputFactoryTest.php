<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class InputFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array(
        "container" => array(
            "rules" => false,
        ),
    );
    public $factory_title = 'ILIAS\\UI\\Component\\Input\\Factory';
}
