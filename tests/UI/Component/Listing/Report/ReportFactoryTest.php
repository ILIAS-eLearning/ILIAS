<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class ReportFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = [
        'standard' => array(
            'context' => false,
            'rules' => false
        ),
        'mini' => array(
            'context' => false,
            'rules' => false
        )
    ];

    public $factory_title = 'ILIAS\\UI\\Component\\Listing\\Report\\Factory';
}
