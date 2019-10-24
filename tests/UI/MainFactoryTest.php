<?php

require_once 'tests/UI/AbstractFactoryTest.php';

/**
 * Class MainFactoryTest
 */
class MainFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array(
        "card"		        => array("context" => false)
        , "deck"			=> array("context" => false)
        , "image"			=> array("context" => false, "rules" => false)
        , "legacy"			=> array("context" => false)
        , "viewControl"		=> array("rules" => false)
        , "input"		    => array("rules" => false)
        , "table"		    => array("rules" => false)

    );

    public $factory_title = 'ILIAS\\UI\\Factory';

    public function test_proper_namespace()
    {
        // Nothing to test here.
    }

    public function test_proper_name()
    {
        // Nothing to test here.
    }

    /**
     * @return string
     */
    protected function get_regex_factory_namespace()
    {
        return "\\\\ILIAS\\\\UI\\\\Component";
    }
}
