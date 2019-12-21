<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class TableFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array(
            "presentation" => array(
                    "context" => false,
                    "rules" => true
            )
    );

    public $factory_title = 'ILIAS\\UI\\Component\\Table\\Factory';
}
