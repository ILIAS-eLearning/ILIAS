<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class ListingFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array(
            "ordered" => array(
                    "context" => false,
                    "rules" => false
            ),
            "unordered" => array(
                    "context" => false,
                    "rules" => false
            ),
            "descriptive" => array(
                    "context" => false,
                    "rules" => false
            ),
            "workflow" => array(
                    "context" => false,
                    "rules" => false
            )
    );


    public $factory_title = 'ILIAS\\UI\\Component\\Listing\\Factory';
}
