<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Unit tests for template class
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @version $Id$
 * @group needsInstalledILIAS
 * @ingroup ServicesUICore
 */
class ilTemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup
     */
    protected function setUp()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            require_once './Services/PHPUnit/classes/class.ilUnitUtil.php';
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }

        // setup stub for global ilPluginAdmin
        include_once("./Services/Component/classes/class.ilPluginAdmin.php");
        global $ilPluginAdmin;
        $ilPluginAdmin = $this->getMockBuilder('ilPluginAdmin')
            ->getMock();
        $ilPluginAdmin->method('getActivePluginsForSlot')
            ->willReturn(array());
    }

    /**
     * @backupGlobals enabled
     */
    public function testilTemplateGet()
    {
        include_once("./Services/UICore/classes/class.ilTemplate.php");
        $tpl = new ilTemplate("tpl.test_template_1.html", true, true, "Services/UICore/test");
        $tpl->setVariable("CONTENT", "Hello World");

        $actual = $tpl->get();

        // Assert
        $expected = "<b>Hello World</b>";
        $this->assertEquals(
            $actual,
            $expected,
            "ilTemplate get() not rendered properly."
        );
    }
}
