<?php declare(strict_types=1);
require_once __DIR__ .'/bootstrap.php';
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class iTinyMCETest
 * @author Jephte Abijuru <jephte.abijuru@minervis.com>
 */
class ilTinyMCETest extends ilRTEBaseTest
{
    protected $backupGlobals = false;
    protected function setUp() : void
    {

        parent::setUp();
    }

    public function testAddPlugin()
    {
        $tinymce = new ilTinyMCE();
        $tinymce->addPlugin('a_new_test_plugin');
        $plugins = $tinymce->getPlugins();
        $this->assertTrue(in_array('a_new_test_plugin', $plugins));

    }

    public function testTiny3xCodeHasbeenRemoved()
    {
        $this->assertDirectoryNotExists('./Services/RTE/tiny_mce_3_4_7');
        $this->assertDirectoryNotExists('./Services/RTE/tiny_mce_3_5_11');
    }
    public function testRemovePlugin()
    {
        $tinymce = new ilTinyMCE();
        $plugins_before_empty_removal=$tinymce->getPlugins();
        
        $tinymce->removePlugin('');//empty name for the plugin
        $this->assertEquals($plugins_before_empty_removal,$tinymce->getPlugins());
        $tinymce->removePlugin('link');
        $this->assertFalse(array_key_exists('link',$tinymce->getPlugins()));       
        
    }
    
}