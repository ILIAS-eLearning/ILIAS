<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class WorkflowFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array(
            "step" => array(
                    "context" => false,
                    "rules" => false
            ),
            "linear" => array(
                    "context" => false,
                    "rules" => false
            )
    );


    public $factory_title = 'ILIAS\\UI\\Component\\Listing\\Workflow\\Factory';
}
