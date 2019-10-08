<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class ImageFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array(
            "standard" => array(
                    "context" => false,
                    "rules" => false
            ),
            "responsive" => array(
                    "context" => false,
                    "rules" => false
            )
    );


    public $factory_title = 'ILIAS\\UI\\Component\\Image\\Factory';
}
