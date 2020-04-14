<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class TableFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array(
        "presentation" => [
            "context" => false,
            "rules" => true
        ],
        "data" => [
            "context" => false
        ]
    );

    public $factory_title = 'ILIAS\\UI\\Component\\Table\\Factory';
}
