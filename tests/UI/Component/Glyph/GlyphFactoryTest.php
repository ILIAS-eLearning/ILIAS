<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class GlyphFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array( "collapse"		=> array("context" => false)
        , "expand"			=> array("context" => false)
        , "next"			=> array("context" => false)
        , "user"			=> array("context" => false)
        , "mail"			=> array("context" => false)
        , "notification"	=> array("context" => false)
        , "tag"				=> array("context" => false)
        , "note"			=> array("context" => false)
        , "comment"			=> array("context" => false)
        , "sortAscending"	=> array("context" => false)
        , "sortDescending"	=> array("context" => false)
        , "briefcase"	    => array("context" => false)
        , "attachment"      => array("context" => false)
        , "reset"			=> array("context" => false)
        , "apply"			=> array("context" => false)
        );

    public $factory_title = 'ILIAS\\UI\\Component\\Glyph\\Factory';
}
