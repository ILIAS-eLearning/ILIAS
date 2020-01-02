<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class PanelFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array(
            "standard" => array(
                    "context" => false,
            ),
            "sub" => array(
                    "context" => false,
            ),
            "report" => array(
                    "context" => false,
                    "rules" => false
            )
    );


    public $factory_title = 'ILIAS\\UI\\Component\\Panel\\Factory';
}
